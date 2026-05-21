<?php

namespace App\Console\Commands;

use App\Mail\OrderCancelledMail;
use App\Mail\AdminOrderCancelledMail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
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
        
        $setting = Setting::where('key', 'auto_cancel_minutes')->first();
        $deadlineMinutes = $setting ? (int) $setting->value : 2880;

        // 1. Verifikasi Admin (Berdasarkan menit sejak created_at)
        $unverifiedOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'pending')
            ->where('created_at', '<=', $now->copy()->subMinutes($deadlineMinutes))
            ->get();

        foreach ($unverifiedOrders as $order) {
            $order->update([
                'order_status' => 'rejected',
                'admin_verification_note' => 'Dibatalkan otomatis oleh sistem karena melewati batas waktu verifikasi admin.',
            ]);
            $this->info("Pesanan {$order->order_code} dibatalkan (Admin verification timeout).");
            $this->sendCancelEmail($order);
        }

        // 2. Pembayaran DP (Berdasarkan menit sejak admin_verified_at)
        $unpaidDpOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'verified')
            ->where('admin_verified_at', '<=', $now->copy()->subMinutes($deadlineMinutes))
            ->get();

        foreach ($unpaidDpOrders as $order) {
            $order->update([
                'order_status' => 'rejected',
                'admin_verification_note' => 'Dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran awal.',
            ]);
            $this->info("Pesanan {$order->order_code} dibatalkan (DP payment timeout).");
            $this->sendCancelEmail($order);
        }

        // 3. Revisi Pesanan (Berdasarkan menit sejak admin_verified_at)
        $revisionOrders = Order::where('order_status', 'submitted')
            ->where('admin_verification_status', 'revision_requested')
            ->where('admin_verified_at', '<=', $now->copy()->subMinutes($deadlineMinutes))
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
        // 1. Kirim email ke pelanggan
        if ($order->user && $order->user->email) {
            try {
                Mail::to($order->user->email)->send(new OrderCancelledMail($order));
            } catch (\Exception $e) {
                Log::error('Failed to send cancel email to customer for order ' . $order->order_code . ': ' . $e->getMessage());
            }
        }

        // 2. Kirim email ke semua admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    Mail::to($admin->email)->send(new AdminOrderCancelledMail($order, $admin));
                } catch (\Exception $e) {
                    Log::error('Failed to send cancel email to admin ' . $admin->email . ' for order ' . $order->order_code . ': ' . $e->getMessage());
                }
            }
        }
    }
}
