<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductionStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['user', 'workOrder', 'productionSteps'])
            ->whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement'])
            ->latest()
            ->get();

        return view('production.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'workOrder', 'productionSteps']);

        return view('production.show', compact('order'));
    }

    public function updateStep(Request $request, Order $order, ProductionStep $step): RedirectResponse
    {
        abort_unless($step->order_id === $order->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,done'],
        ]);

        $status = $validated['status'];
        $isFinishing = strtolower($step->step_name) === 'finishing';

        if ($isFinishing && $status === 'done' && $order->remaining_amount > 0) {
            return back()->withErrors(['production' => 'Pelunasan belum diverifikasi. Finishing tidak bisa diselesaikan.']);
        }

        DB::transaction(function () use ($request, $order, $step, $status, $isFinishing): void {
            $step->status = $status;
            $step->updated_by = $request->user()->id;
            $step->started_at = $status === 'in_progress' ? now() : $step->started_at;
            $step->completed_at = $status === 'done' ? now() : null;
            $step->save();

            if ($isFinishing && in_array($status, ['in_progress', 'done'], true) && $order->remaining_amount > 0) {
                $order->update([
                    'order_status' => 'finishing_waiting_settlement',
                ]);
                return;
            }

            $allDone = $order->productionSteps()->where('status', '!=', 'done')->doesntExist();

            if ($allDone && $order->remaining_amount <= 0) {
                $order->update([
                    'order_status' => 'completed',
                    'payment_status' => 'fully_paid',
                ]);

                if ($order->workOrder) {
                    $order->workOrder->update(['status' => 'closed']);
                }
            } else {
                $order->update([
                    'order_status' => 'in_production',
                ]);
            }
        });

        return back()->with('success', 'Status produksi berhasil diperbarui.');
    }
}
