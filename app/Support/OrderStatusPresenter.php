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
            'submitted', 'pending_verification', 'verified_payment', 'verified_dp' => 'status-warning',
            'admin_verified_waiting_payment' => 'status-success',
            'revision_requested' => 'status-danger',
            'production_done_waiting_admin', 'in_production' => 'status-info',
            'finishing_waiting_settlement' => 'status-accent',
            'ready_for_pickup' => 'status-warning',
            'completed' => 'status-success',
            'rejected' => 'status-danger',
            default => 'status-neutral',
        };
    }

    public static function customerLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Menunggu Verifikasi Admin',
            'admin_verified_waiting_payment' => 'Terverifikasi',
            'revision_requested' => 'Menunggu Persetujuan Perubahan',
            'pending_verification' => 'Menunggu Pembayaran',
            'verified_payment', 'verified_dp' => 'Menunggu Produksi',
            'production_done_waiting_admin' => 'Selesai Produksi',
            'in_production' => 'Sedang Proses',
            'finishing_waiting_settlement' => 'Menunggu Bayar',
            'ready_for_pickup' => 'Pesanan Siap Ambil',
            'completed', 'done' => 'Pesanan Sudah Diambil',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
