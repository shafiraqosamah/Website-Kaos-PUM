<?php

namespace App\Console\Commands;

use App\Mail\OrderCancelledMail;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckOrderDeadlines extends Command
{
    protected $signature = 'orders:check-deadlines';
    protected $description = 'Check and enforce 2x24h deadlines for orders';

    public function handle()
    {
        $this->info('Memulai pengecekan batas waktu pesanan...');
        $now = now();
        $deadlineHours = 48;

        // 1. Verifikasi Admin (2x24h sejak created_at)
        $unverifiedOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'pending')
            ->where('created_at', '<=', $now->copy()->subHours($deadlineHours))
            ->get();

        foreach ($unverifiedOrders as $order) {
            $order->update([
                'order_status' => 'cancelled',
                'admin_verification_note' => 'Dibatalkan otomatis oleh sistem karena melewati batas waktu verifikasi admin (2x24 Jam).',
            ]);
            $this->info("Pesanan {$order->order_code} dibatalkan (Admin verification timeout).");
            $this->sendCancelEmail($order);
        }

        // 2. Pembayaran DP (2x24h sejak admin_verified_at)
        $unpaidDpOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'verified')
            ->where('admin_verified_at', '<=', $now->copy()->subHours($deadlineHours))
            ->get();

        foreach ($unpaidDpOrders as $order) {
            $order->update([
                'order_status' => 'cancelled',
                'admin_verification_note' => 'Dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran awal (2x24 Jam).',
            ]);
            $this->info("Pesanan {$order->order_code} dibatalkan (DP payment timeout).");
            $this->sendCancelEmail($order);
        }

        // 3. Revisi Pesanan (2x24h sejak admin_verified_at)
        $revisionOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'revision_requested')
            ->where('admin_verified_at', '<=', $now->copy()->subHours($deadlineHours))
            ->get();

        foreach ($revisionOrders as $order) {
            $order->update([
                'admin_verification_status' => 'verified',
                'admin_verification_note' => 'Disetujui otomatis oleh sistem (Batas waktu revisi 2x24 Jam habis). ' . $order->admin_verification_note,
                'admin_verified_at' => now(), // Reset timer for DP payment
            ]);
            $this->info("Revisi pesanan {$order->order_code} disetujui otomatis.");
        }
        
        $this->info('Pengecekan batas waktu selesai.');
    }

    private function sendCancelEmail(Order $order)
    {
        if ($order->user && $order->user->email) {
            try {
                Mail::to($order->user->email)->send(new OrderCancelledMail($order));
            } catch (\Exception $e) {
                Log::error('Failed to send cancel email for order ' . $order->order_code . ': ' . $e->getMessage());
            }
        }
    }
}
