@php $ukuran = $ukuran ?? 'h-10 w-10'; @endphp
@if (file_exists(public_path('images/logo-lebak.png')))
    <span class="inline-flex {{ $ukuran }} shrink-0 items-center justify-center rounded-lg bg-white p-1 ring-1 ring-black/5">
        <img src="{{ asset('images/logo-lebak.png') }}" alt="Logo Kabupaten Lebak" class="h-full w-full object-contain">
    </span>
@else
    <span class="inline-flex {{ $ukuran }} shrink-0 items-center justify-center rounded-lg bg-white text-sm font-extrabold text-lebak-blue ring-1 ring-black/5">SP</span>
@endif