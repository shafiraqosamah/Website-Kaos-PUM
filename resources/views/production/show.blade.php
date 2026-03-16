@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Progress Produksi {{ $order->order_code }}</h1>
    <p class="muted">SPK: {{ $order->workOrder?->spk_number ?? '-' }} | Pelanggan: {{ $order->user->name }}</p>
</div>

<div class="card" style="margin-top:1rem;">
    <table>
        <thead><tr><th>Tahap</th><th>Status Saat Ini</th><th>Update</th></tr></thead>
        <tbody>
        @foreach($order->productionSteps as $step)
            <tr>
                <td>{{ $step->step_order }}. {{ $step->step_name }}</td>
                <td><span class="status-pill">{{ $step->status }}</span></td>
                <td>
                    <form method="POST" action="{{ route('production.step.update', [$order, $step]) }}" style="display:flex; gap:0.5rem; max-width:320px;">
                        @csrf
                        <select name="status">
                            <option value="pending" @selected($step->status==='pending')>pending</option>
                            <option value="in_progress" @selected($step->status==='in_progress')>in progress</option>
                            <option value="done" @selected($step->status==='done')>done</option>
                        </select>
                        <button class="btn btn-brand" type="submit">Simpan</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($order->order_status === 'finishing_waiting_settlement')
        <p class="alert alert-err" style="margin-top:0.9rem;">Menunggu pelunasan pelanggan. Finishing tidak dapat diselesaikan sebelum lunas terverifikasi.</p>
    @endif
</div>
@endsection
