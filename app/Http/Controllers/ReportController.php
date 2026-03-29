<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
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
        $period = $this->resolveMonthPeriod($request);
        $orderData = $this->buildOrderBalanceData($period['start'], $period['end']);

        return view('reports.orders-balance', array_merge($period, $orderData));
    }

    public function finance(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);
        $financeData = $this->buildFinanceLedgerData($period['start'], $period['end']);

        return view('reports.finance-ledger', array_merge($period, $financeData));
    }

    public function production(Request $request): View
    {
        $period = $this->resolveMonthPeriod($request);
        $productionData = $this->buildProductionData($period['start'], $period['end']);

        return view('reports.production-monthly', array_merge($period, $productionData));
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
            $bank = $payment->destinationBankDetails();

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
                'destination' => $bank
                    ? ($bank['label'] . ' - ' . $bank['account_number'] . ' (' . $bank['account_name'] . ')')
                    : ((string) ($payment->destination_bank ?? '-')),
                'sender' => trim((string) ($payment->sender_bank_name ?? '-') . ' / ' . (string) ($payment->sender_account_name ?? '-')),
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
                'updated_at' => $order->updated_at,
            ];
        });

        $productionByType = $orders
            ->groupBy(fn (Order $order): string => (string) ($order->production_type ?: '-'))
            ->map(function ($group): array {
                return [
                    'production_type' => (string) $group->first()->production_type ?: '-',
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
