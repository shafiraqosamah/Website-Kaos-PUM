<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;

class OrderStatusPresenter
{
    public static function resolveForCustomer(Order $order, ?Payment $latestPayment): string
    {
        if (($latestPayment?->status ?? null) === 'rejected') {
            return 'rejected';
        }

        $adminStatus = (string) ($order->admin_verification_status ?? 'pending');

        if ($order->order_status === 'submitted') {
            if ($adminStatus === 'verified') {
                return 'admin_verified_waiting_payment';
            }

            if ($adminStatus === 'revision_requested') {
                return 'revision_requested';
            }
        }

        return (string) $order->order_status;
    }

    public static function customerClass(string $status): string
    {
        return match ($status) {
            'submitted' => 'status-warning',
            'admin_verified_waiting_payment' => 'status-teal',
            'revision_requested', 'rejected' => 'status-danger',
            'pending_verification' => 'status-warning',
            'verified_payment', 'verified_dp' => 'status-accent',
            'in_production' => 'status-primary',
            'production_done_waiting_admin' => 'status-teal',
            'finishing_waiting_settlement' => 'status-danger',
            'ready_for_pickup', 'completed', 'done' => 'status-success',
            default => 'status-neutral',
        };
    }

    public static function customerLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Menunggu Verifikasi Admin (Max 2x24 Jam)',
            'admin_verified_waiting_payment' => 'Pesanan Terverifikasi',
            'revision_requested' => 'Pengajuan Kembali (Max 2x24 Jam)',
            'pending_verification' => 'Menunggu Pembayaran DP (Max 2x24 Jam)',
            'verified_payment', 'verified_dp' => 'Menunggu Produksi',
            'production_done_waiting_admin' => 'Selesai Produksi',
            'in_production' => 'Sedang Proses',
            'finishing_waiting_settlement' => 'Menunggu Pelunasan (Max 2x24 Jam)',
            'ready_for_pickup' => 'Pesanan Siap Ambil',
            'completed', 'done' => 'Selesai',
            'rejected' => 'Ditolak',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
