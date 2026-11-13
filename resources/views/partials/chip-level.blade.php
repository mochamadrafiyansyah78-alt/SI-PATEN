@php
    $w = match ($level ?? 'Hijau') {
        'Merah' => 'bg-rose-50 text-rose-700',
        'Kuning' => 'bg-amber-50 text-amber-700',
        default => 'bg-emerald-50 text-emerald-700',
    };
@endphp
<span class="chip {{ $w }}"><span class="mr-1.5 inline-block h-2 w-2 rounded-full {{ ($level ?? 'Hijau') === 'Merah' ? 'bg-rose-500' : (($level ?? 'Hijau') === 'Kuning' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>{{ $level ?? 'Hijau' }}</span>
