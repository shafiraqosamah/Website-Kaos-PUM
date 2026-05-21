<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminSettingController extends Controller
{
    public function index(): View
    {
        $setting = Setting::where('key', 'auto_cancel_minutes')->first();
        $autoCancelMinutes = $setting ? $setting->value : 2880;

        return view('admin.settings.index', compact('autoCancelMinutes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'auto_cancel_minutes' => 'required|integer|min:1',
        ]);

        Setting::updateOrCreate(
            ['key' => 'auto_cancel_minutes'],
            ['value' => $request->auto_cancel_minutes]
        );

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }

    public function runCheck(): RedirectResponse
    {
        Artisan::call('orders:check-deadlines');
        
        return redirect()->route('admin.settings.index')->with('success', 'Simulasi pengecekan batas waktu berhasil dijalankan. Silakan cek status pesanan atau email terkait.');
    }
}
