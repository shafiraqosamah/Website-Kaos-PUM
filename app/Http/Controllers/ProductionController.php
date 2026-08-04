<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductionStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Mail\SettlementRequiredMail;

class ProductionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeOrders = Order::with(['user', 'workOrder', 'productionSteps'])
            ->whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement', 'production_done_waiting_admin', 'ready_for_pickup'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('product_name', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%")
                      ->orWhereHas('user', function ($uq) use ($request) {
                          $uq->where('name', 'like', "%{$request->search}%");
                      })
                      ->orWhereHas('workOrder', function ($wq) use ($request) {
                          $wq->where('spk_number', 'like', "%{$request->search}%");
                      });
                });
            })
            ->latest()
            ->paginate(3, ['*'], 'active_page')
            ->withQueryString();

        $completedOrders = Order::with(['user', 'workOrder', 'productionSteps'])
            ->where('order_status', 'completed')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('product_name', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%")
                      ->orWhereHas('user', function ($uq) use ($request) {
                          $uq->where('name', 'like', "%{$request->search}%");
                      })
                      ->orWhereHas('workOrder', function ($wq) use ($request) {
                          $wq->where('spk_number', 'like', "%{$request->search}%");
                      });
                });
            })
            ->latest('updated_at')
            ->paginate(3, ['*'], 'completed_page')
            ->withQueryString();

        return view('production.index', compact('activeOrders', 'completedOrders', 'user'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'workOrder', 'productionSteps', 'sizes']);

        $user = request()->user();

        return view('production.show', compact('order', 'user'));
    }

    public function spk(Order $order): View
    {
        $order->load(['user', 'workOrder.issuer', 'sizes']);

        abort_unless((bool) $order->workOrder, 404);

        return view('production.spk', compact('order'));
    }

    public function updateStep(Request $request, Order $order, ProductionStep $step): RedirectResponse
    {
        abort_unless($step->order_id === $order->id, 404);

        if ($request->user()->hasRole('admin')) {
            return back()->withErrors([
                'production' => 'Admin hanya dapat melihat tahapan produksi. Perubahan tahap hanya dapat dilakukan tim produksi.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,done'],
        ]);

        $status = $validated['status'];
        $isFinishing = strtolower($step->step_name) === 'finishing';

        if ($step->status === 'done' && in_array($status, ['pending', 'in_progress'], true)) {
            return back()->withErrors([
                'production' => 'Tahap yang sudah selesai tidak dapat diubah kembali ke status sebelumnya.',
            ]);
        }

        if ($step->status === 'pending' && $status === 'done') {
            return back()->withErrors([
                'production' => 'Tahap harus dimulai terlebih dahulu sebelum bisa diselesaikan.',
            ]);
        }

        $hasPreviousIncompleteStep = $order->productionSteps()
            ->where('step_order', '<', $step->step_order)
            ->where('status', '!=', 'done')
            ->exists();

        if ($hasPreviousIncompleteStep && in_array($status, ['in_progress', 'done'], true)) {
            return back()->withErrors([
                'production' => 'Tahap sebelumnya belum selesai. Selesaikan proses secara berurutan.',
            ]);
        }

        $isSteam = str_contains(strtolower(trim((string) $step->step_name)), 'steam');

        if ($isFinishing && in_array($status, ['in_progress', 'done'], true) && $order->remaining_amount > 0) {
            return back()->withErrors(['production' => 'Pelunasan belum diverifikasi. Tahap finishing tidak dapat dimulai atau diselesaikan.']);
        }

        DB::transaction(function () use ($request, $order, $step, $status, $isFinishing): void {
            $step->status = $status;
            $step->updated_by = $request->user()->id;
            $step->started_at = $status === 'in_progress' ? now() : $step->started_at;
            $step->completed_at = $status === 'done' ? now() : null;
            $step->save();

            $visibleProductionSteps = $order->productionSteps()
                ->get()
                ->filter(static function (ProductionStep $productionStep): bool {
                    $normalized = strtolower(trim((string) $productionStep->step_name));
                    return ! str_contains($normalized, 'persiapan bahan');
                });

            $allDone = $visibleProductionSteps->isEmpty() || $visibleProductionSteps->every(static function (ProductionStep $productionStep): bool {
                return $productionStep->status === 'done';
            });

            if ($allDone) {
                $order->update([
                    'order_status' => 'production_done_waiting_admin',
                    'payment_status' => 'fully_paid',
                ]);
            } else {
                $steamStep = $visibleProductionSteps->first(function ($s) {
                    return str_contains(strtolower(trim((string) $s->step_name)), 'steam');
                });
                
                $isSteamStarted = $steamStep && in_array($steamStep->status, ['in_progress', 'done'], true);
                
                if ($isSteamStarted && $order->remaining_amount > 0) {
                    $updates = ['order_status' => 'finishing_waiting_settlement'];
                    $newlyFinished = false;
                    
                    if ($steamStep->status === 'done' && is_null($order->payment_deadline_at)) {
                        $updates['payment_deadline_at'] = now()->addHours(48);
                        $newlyFinished = true;
                    }

                    $order->update($updates);
                    
                    if ($newlyFinished && $order->user && $order->user->email) {
                        Mail::to($order->user->email)->send(new SettlementRequiredMail($order));
                    }
                } else {
                    $order->update([
                        'order_status' => 'in_production',
                    ]);
                }
            }
        });

        return back()->with('success', 'Status produksi berhasil diperbarui.');
    }

    public function verifyFinalResult(Request $request, Order $order): RedirectResponse
    {
        if (! $request->user()->hasRole('admin')) {
            abort(403);
        }

        $order->loadMissing(['productionSteps', 'workOrder']);

        $visibleProductionSteps = $order->productionSteps->filter(static function (ProductionStep $productionStep): bool {
            $normalized = strtolower(trim((string) $productionStep->step_name));

            return ! str_contains($normalized, 'persiapan bahan');
        });

        $allDone = $visibleProductionSteps->isEmpty() || $visibleProductionSteps->every(static function (ProductionStep $productionStep): bool {
            return $productionStep->status === 'done';
        });

        if (! $allDone) {
            return back()->withErrors([
                'production' => 'Semua tahap produksi harus selesai sebelum verifikasi final admin.',
            ]);
        }

        if ((float) $order->remaining_amount > 0) {
            return back()->withErrors([
                'production' => 'Pesanan belum lunas. Admin tidak dapat menverifikasi hasil produksi.',
            ]);
        }

        DB::transaction(function () use ($order): void {
            $order->update([
                'order_status' => 'ready_for_pickup',
                'payment_status' => 'fully_paid',
            ]);

            if ($order->workOrder) {
                $order->workOrder->update(['status' => 'closed']);
            }
        });

        return back()->with('success', 'Hasil produksi sudah diverifikasi admin. Status pesanan kini siap diambil pelanggan.');
    }

    public function updatePickupStatus(Request $request, Order $order): RedirectResponse
    {
        if (! $request->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'order_status' => ['required', 'in:ready_for_pickup,completed'],
        ]);

        if (! in_array($order->order_status, ['ready_for_pickup', 'completed'], true)) {
            return back()->withErrors([
                'production' => 'Status pengambilan hanya dapat diubah setelah verifikasi final admin.',
            ]);
        }

        if ($order->order_status === 'completed' && $validated['order_status'] !== 'completed') {
            return back()->withErrors([
                'production' => 'Pesanan yang sudah selesai tidak dapat dikembalikan ke status siap ambil.',
            ]);
        }

        $order->update([
            'order_status' => $validated['order_status'],
        ]);

        $message = $validated['order_status'] === 'completed'
            ? 'Status pesanan diperbarui menjadi Pesanan Selesai.'
            : 'Status pesanan diperbarui menjadi Pesanan Siap Ambil.';

        return back()->with('success', $message);
    }
}
