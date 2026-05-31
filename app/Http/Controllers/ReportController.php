<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const MATERIALS = [
        'Drill',
        'Taipan',
        'Tropical',
        'Oxford',
        'Twill',
        'Ribstop',
        'Lacoste Pique',
        'Cotton Combed 30s',
        'Cotton Combed 20s',
        'Drifit',
        'Lainnya',
    ];

    private const TYPES = ['Sablon Manual', 'DTF (Direct to Film)', 'Bordiran', 'Printing'];
    private const MODELS = ['Polo Shirt', 'Kaos Oblong', 'Kaos Panjang', 'Raglan', 'T-Shirt'];
    private const SLEEVES = ['Lengan Pendek', 'Lengan Panjang'];

    public function monthly(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(User::ROLE_MANAGER, User::ROLE_OWNER)) {
            return redirect()->route('reports.executive', $request->query());
        }

        if ($user->hasRole(User::ROLE_FINANCE)) {
            return redirect()->route('reports.finance', $request->query());
        }

        if ($user->hasRole(User::ROLE_PRODUCTION)) {
            return redirect()->route('reports.production', $request->query());
        }

        return redirect()->route('reports.orders', $request->query());
    }

    public function orders(Request $request): View
    {
        $ordersQuery = Order::query()
            ->with(['user', 'sizes', 'payments'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('product_name', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%");
                });
            })
            ->latest();

        $orderCount = (clone $ordersQuery)->count();
        $verifiedCount = (clone $ordersQuery)
            ->where('admin_verification_status', 'verified')
            ->count();
        $revisionRequestedCount = (clone $ordersQuery)
            ->where('admin_verification_status', 'revision_requested')
            ->count();

        $pendingOrders = (clone $ordersQuery)
            ->where(function($query) {
                $query->whereNull('admin_verification_status')
                      ->orWhere('admin_verification_status', '!=', 'verified');
            })
            ->where('order_status', '!=', 'rejected')
            ->paginate(10, ['*'], 'pending_page')->withQueryString();

        $verifiedOrders = (clone $ordersQuery)
            ->where('admin_verification_status', 'verified')
            ->where('order_status', '!=', 'rejected')
            ->paginate(10, ['*'], 'verified_page')->withQueryString();

        $cancelledOrders = (clone $ordersQuery)
            ->where('order_status', 'rejected')
            ->paginate(10, ['*'], 'cancelled_page')->withQueryString();

        return view('reports.orders-balance', [
            'pendingOrders' => $pendingOrders,
            'verifiedOrders' => $verifiedOrders,
            'cancelledOrders' => $cancelledOrders,
            'orderCount' => $orderCount,
            'verifiedCount' => $verifiedCount,
            'revisionRequestedCount' => $revisionRequestedCount,
        ]);
    }

    public function ordersReport(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);

        $ordersQuery = Order::query()
            ->with(['user', 'sizes', 'payments'])
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('product_name', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%");
                });
            })
            ->latest();

        $orderCount = (clone $ordersQuery)->count();
        $verifiedCount = (clone $ordersQuery)
            ->where('admin_verification_status', 'verified')
            ->count();
        $revisionRequestedCount = (clone $ordersQuery)
            ->where('admin_verification_status', 'revision_requested')
            ->count();

        $orders = $ordersQuery->get();

        return view('reports.orders-report', array_merge($period, [
            'orders' => $orders,
            'orderCount' => $orderCount,
            'verifiedCount' => $verifiedCount,
            'revisionRequestedCount' => $revisionRequestedCount,
        ]));
    }

    public function exportOrders(Request $request)
    {
        $period = $this->resolveMonthPeriod($request);

        $ordersQuery = Order::query()
            ->with(['user', 'sizes', 'payments'])
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->latest();

        $orders = $ordersQuery->get();

        return response(view('reports.exports.orders', array_merge($period, ['orders' => $orders])))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Laporan_Pemesanan_' . $period['monthInput'] . '.xls"');
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN, User::ROLE_FINANCE), 403);

        $order->load(['user', 'sizes', 'payments']);

        return view('reports.orders-detail', [
            'order' => $order,
            'materials' => self::MATERIALS,
            'types' => self::TYPES,
            'models' => self::MODELS,
            'sleeves' => self::SLEEVES,
        ]);
    }

    public function verifyOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN, User::ROLE_FINANCE), 403);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'admin_verification_status' => 'verified',
            'admin_verification_note' => $validated['admin_note'] ?? null,
            'admin_verified_by' => $request->user()->id,
            'admin_verified_at' => now(),
        ]);

        $month = (string) $request->input('month', '');

        return redirect()
            ->route('reports.orders', array_filter(['month' => $month]))
            ->with('success', 'Pesanan berhasil diverifikasi. Customer dapat melanjutkan pembayaran.');
    }

    public function requestRevision(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN, User::ROLE_FINANCE), 403);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'fabric' => ['required', 'string', 'max:120'],
            'production_type' => ['required', 'string', 'max:120'],
            'product_model' => ['required', 'string', 'max:120'],
            'sleeve_type' => ['required', 'string', 'max:80'],
            'dominant_color' => ['required', 'string', 'max:80'],
            'estimated_finish_date' => ['required', 'date', 'after_or_equal:today'],
            'design_notes' => ['nullable', 'string', 'max:1000'],
            'admin_revision_note' => ['required', 'string', 'max:1000'],
        ]);

        $order->update([
            'customer_name' => $validated['customer_name'],
            'fabric' => $validated['fabric'],
            'production_type' => $validated['production_type'],
            'product_model' => $validated['product_model'],
            'sleeve_type' => $validated['sleeve_type'],
            'dominant_color' => $validated['dominant_color'],
            'estimated_finish_date' => $validated['estimated_finish_date'],
            'design_notes' => $validated['design_notes'] ?? $order->design_notes,
            'admin_verification_status' => 'revision_requested',
            'admin_verification_note' => $validated['admin_revision_note'],
            'admin_verified_by' => $request->user()->id,
            'admin_verified_at' => now(),
        ]);

        $month = (string) $request->input('month', '');

        return redirect()
            ->route('reports.orders', array_filter(['month' => $month]))
            ->with('success', 'Pesanan diajukan kembali ke customer beserta catatan revisi.');
    }

    public function finance(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);
        $financeData = $this->buildFinanceLedgerData($period['start'], $period['end']);

        return view('reports.finance-ledger', array_merge($period, $financeData));
    }

    public function exportFinance(Request $request)
    {
        $period = $this->resolveMonthPeriod($request);
        $financeData = $this->buildFinanceLedgerData($period['start'], $period['end']);

        return response(view('reports.exports.finance', array_merge($period, $financeData)))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Laporan_Keuangan_' . $period['monthInput'] . '.xls"');
    }

    public function production(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);
        $productionData = $this->buildProductionData($period['start'], $period['end']);

        return view('reports.production-monthly', array_merge($period, $productionData));
    }

    public function exportProduction(Request $request)
    {
        $period = $this->resolveMonthPeriod($request);
        $productionData = $this->buildProductionData($period['start'], $period['end']);

        return response(view('reports.exports.production', array_merge($period, $productionData)))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Laporan_Produksi_' . $period['monthInput'] . '.xls"');
    }

    public function executive(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);

        return view('reports.executive', array_merge(
            $period,
            $this->buildOrderBalanceData($period['start'], $period['end']),
            $this->buildFinanceLedgerData($period['start'], $period['end']),
            $this->buildProductionData($period['start'], $period['end'])
        ));
    }

    private function resolveMonthPeriod(Request $request): array
    {
        $monthInput = (string) $request->query('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        return [
            'monthInput' => $start->format('Y-m'),
            'monthLabel' => $start->translatedFormat('F Y'),
            'start' => $start,
            'end' => $end,
        ];
    }

    private function buildOrderBalanceData(Carbon $start, Carbon $end): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->with(['user', 'payments'])
            ->latest()
            ->get();

        $orderSubtotal = (float) $orders->sum('subtotal');
        $verifiedPaymentsTotal = (float) Payment::query()
            ->where('status', 'verified')
            ->whereBetween('verified_at', [$start, $end])
            ->sum('amount');

        $financePerOrder = $orders->map(function (Order $order): array {
            $dpVerifiedAt = optional(
                $order->payments
                    ->where('method', 'dp')
                    ->where('status', 'verified')
                    ->sortBy('verified_at')
                    ->first()
            )->verified_at;

            $settlementVerifiedAt = optional(
                $order->payments
                    ->where('method', 'settlement')
                    ->where('status', 'verified')
                    ->sortBy('verified_at')
                    ->first()
            )->verified_at;

            $fullVerifiedAt = optional(
                $order->payments
                    ->where('method', 'full')
                    ->where('status', 'verified')
                    ->sortBy('verified_at')
                    ->first()
            )->verified_at;

            $dpVerified = (float) $order->payments->where('method', 'dp')->where('status', 'verified')->sum('amount');
            $settlementVerified = (float) $order->payments->where('method', 'settlement')->where('status', 'verified')->sum('amount');
            $fullVerified = (float) $order->payments->where('method', 'full')->where('status', 'verified')->sum('amount');
            $verified = (float) $order->payments->where('status', 'verified')->sum('amount');

            $paymentLines = $order->payments
                ->sortBy('created_at')
                ->map(function (Payment $payment): array {
                    return [
                        'method_label' => $this->paymentMethodLabel($payment->method),
                        'status' => $payment->status,
                        'amount' => (float) $payment->amount,
                        'date' => $payment->created_at,
                    ];
                })
                ->values()
                ->all();

            return [
                'order_code' => $order->order_code,
                'customer_name' => $order->customer_name,
                'product_name' => $order->product_name,
                'total_pcs' => (int) $order->total_pcs,
                'subtotal' => (float) $order->subtotal,
                'verified' => $verified,
                'dp_verified' => $dpVerified,
                'settlement_verified' => $settlementVerified,
                'full_verified' => $fullVerified,
                'dp_verified_at' => $dpVerifiedAt,
                'settlement_verified_at' => $settlementVerifiedAt,
                'full_verified_at' => $fullVerifiedAt,
                'remaining_amount' => (float) $order->remaining_amount,
                'balance_delta' => $verified - (float) $order->subtotal,
                'is_balanced' => abs(((float) $order->subtotal) - $verified) < 0.01,
                'payment_lines' => $paymentLines,
            ];
        });

        return [
            'orders' => $orders,
            'orderCount' => $orders->count(),
            'orderSubtotal' => $orderSubtotal,
            'totalPcs' => (int) $orders->sum('total_pcs'),
            'verifiedPaymentsTotal' => $verifiedPaymentsTotal,
            'balanceGap' => $verifiedPaymentsTotal - $orderSubtotal,
            'financePerOrder' => $financePerOrder,
        ];
    }

    private function buildFinanceLedgerData(Carbon $start, Carbon $end): array
    {
        $payments = Payment::query()
            ->with(['order'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $ledgerRows = $payments->map(function (Payment $payment): array {
            $order = $payment->order;

            return [
                'date' => $payment->created_at,
                'order_id' => $order?->id,
                'order_code' => $order?->order_code ?? '-',
                'customer_name' => $order?->customer_name ?? '-',
                'product' => trim((string) ($order?->product_name ?? '-') . ' / ' . (string) ($order?->production_type ?? '-')),
                'qty' => (int) ($order?->total_pcs ?? 0),
                'unit_price' => (float) ($order?->unit_price ?? 0),
                'order_subtotal' => (float) ($order?->subtotal ?? 0),
                'method' => $this->paymentMethodLabel($payment->method),
                'method_raw' => $payment->method,
                'destination' => $payment->midtrans_payment_type ? 'Midtrans (' . strtoupper($payment->midtrans_payment_type) . ')' : 'Midtrans',
                'sender' => '-',
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'invoice' => $payment->invoice_number ?? '-',
                'verified_at' => $payment->verified_at,
            ];
        });

        $ledgerByOrder = $ledgerRows
            ->groupBy(function (array $row): string {
                if (! empty($row['order_id'])) {
                    return 'order-' . $row['order_id'];
                }

                return 'payment-' . (string) ($row['invoice'] ?: uniqid('p-', true));
            })
            ->map(function ($rows): array {
                $first = $rows->first();
                $verified = (float) $rows->where('status', 'verified')->sum('amount');
                $pending = (float) $rows->where('status', 'pending')->sum('amount');
                $rejected = (float) $rows->where('status', 'rejected')->sum('amount');
                $orderSubtotal = (float) ($first['order_subtotal'] ?? 0);

                $firstVerifiedByMethod = function (string $method) use ($rows) {
                    $row = $rows
                        ->where('method_raw', $method)
                        ->where('status', 'verified')
                        ->sortBy('verified_at')
                        ->first();

                    return $row['verified_at'] ?? null;
                };

                return [
                    'order_code' => (string) ($first['order_code'] ?? '-'),
                    'customer_name' => (string) ($first['customer_name'] ?? '-'),
                    'product' => (string) ($first['product'] ?? '-'),
                    'qty' => (int) ($first['qty'] ?? 0),
                    'unit_price' => (float) ($first['unit_price'] ?? 0),
                    'order_subtotal' => $orderSubtotal,
                    'lines' => $rows->values()->all(),
                    'row_count' => $rows->count(),
                    'verified_total' => $verified,
                    'pending_total' => $pending,
                    'rejected_total' => $rejected,
                    'dp_verified' => (float) $rows->where('method_raw', 'dp')->where('status', 'verified')->sum('amount'),
                    'settlement_verified' => (float) $rows->where('method_raw', 'settlement')->where('status', 'verified')->sum('amount'),
                    'full_verified' => (float) $rows->where('method_raw', 'full')->where('status', 'verified')->sum('amount'),
                    'remaining' => max(0, $orderSubtotal - $verified),
                    'dp_verified_at' => $firstVerifiedByMethod('dp'),
                    'settlement_verified_at' => $firstVerifiedByMethod('settlement'),
                    'full_verified_at' => $firstVerifiedByMethod('full'),
                ];
            })
            ->sortBy('order_code')
            ->values();

        $orderSubtotal = (float) Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('subtotal');

        $verifiedTotal = (float) $payments->where('status', 'verified')->sum('amount');
        $pendingTotal = (float) $payments->where('status', 'pending')->sum('amount');
        $rejectedTotal = (float) $payments->where('status', 'rejected')->sum('amount');
        $receivable = $orderSubtotal - $verifiedTotal;

        return [
            'ledgerRows' => $ledgerRows,
            'ledgerByOrder' => $ledgerByOrder,
            'ledgerSummary' => [
                'order_subtotal' => $orderSubtotal,
                'verified_total' => $verifiedTotal,
                'pending_total' => $pendingTotal,
                'rejected_total' => $rejectedTotal,
                'receivable' => $receivable,
                'surplus_deficit' => $verifiedTotal - $orderSubtotal,
            ],
        ];
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'dp' => 'DP 50%',
            'settlement' => 'Pelunasan',
            'full' => 'Lunas Awal',
            default => Str::upper($method),
        };
    }

    private function buildProductionData(Carbon $start, Carbon $end): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->with(['productionSteps'])
            ->latest()
            ->get();

        $productionRows = $orders->map(function (Order $order): array {
            $doneSteps = $order->productionSteps->where('status', 'done')->count();
            $stepCount = $order->productionSteps->count();

            return [
                'order_code' => $order->order_code,
                'customer_name' => $order->customer_name,
                'product_name' => $order->product_name,
                'production_type' => $order->production_type,
                'total_pcs' => (int) $order->total_pcs,
                'order_status' => $order->order_status,
                'step_progress' => $stepCount > 0 ? ($doneSteps . '/' . $stepCount) : '-',
                'created_at' => $order->created_at,
                'estimated_finish_date' => $order->estimated_finish_date,
                'updated_at' => $order->updated_at,
            ];
        });

        $productionByType = $orders
            ->groupBy(static function (Order $order): string {
                $productionType = (string) ($order->production_type ?: '-');
                $productName = (string) ($order->product_model ?: $order->product_name ?: '-');

                return $productionType . '||' . $productName;
            })
            ->map(function ($group): array {
                $firstOrder = $group->first();

                return [
                    'production_type' => (string) ($firstOrder->production_type ?: '-'),
                    'product_model' => (string) ($firstOrder->product_model ?: $firstOrder->product_name ?: '-'),
                    'total_orders' => $group->count(),
                    'total_pcs' => (int) $group->sum('total_pcs'),
                ];
            })
            ->sortByDesc('total_orders')
            ->values();

        return [
            'productionRows' => $productionRows,
            'completedInMonth' => $orders->where('order_status', 'completed')->count(),
            'producedPcsInMonth' => (int) $orders->where('order_status', 'completed')->sum('total_pcs'),
            'productionByType' => $productionByType,
        ];
    }
}
