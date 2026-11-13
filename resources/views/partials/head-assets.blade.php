<link rel="icon" type="image/png" href="{{ asset('images/logo-lebak.png') }}">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: { extend: {
            fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: {
                lebak: { blue: '#00508F', dark: '#00395F', green: '#62B38B', gold: '#77612A', cream: '#F7F3E8', mist: '#F2F6F5' },
                teal:   { 50:'#EAF3FA',100:'#D3E6F4',200:'#A9CDE9',300:'#6FA9D4',400:'#3A83BC',500:'#1A68A5',600:'#00508F',700:'#00508F',800:'#00416F',900:'#00395F' },
                indigo: { 50:'#EAF3FA',100:'#D3E6F4',200:'#A9CDE9',300:'#6FA9D4',400:'#3A83BC',500:'#1A68A5',600:'#00508F',700:'#00508F',800:'#00416F',900:'#00395F' },
            },
        } },
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: #cbd5d1; border-radius: 3px; }

    /* Chip/badge oval: satu baris rapi, teks panjang tetap simetris */
    .chip { display: inline-flex; align-items: center; justify-content: center; max-width: 100%; white-space: nowrap;
            border-radius: 9999px; padding: .2rem .7rem; font-size: .75rem; font-weight: 600; line-height: 1.25; }
    .chip-wrap { white-space: normal; text-align: center; border-radius: 1rem; }

    .btn-edit, .btn-hapus, .btn-warn, .btn-ok, .btn-muted {
        display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
        border-radius: .5rem; padding: .375rem .8rem; font-size: .75rem; font-weight: 600;
        transition: all .15s ease; cursor: pointer; border: 1px solid transparent;
    }
    .btn-edit  { background: rgba(0,80,143,.10);  color: #00508F; }
    .btn-edit:hover  { background: #00508F; color: #fff; }
    .btn-hapus { background: #fff1f2; color: #e11d48; }
    .btn-hapus:hover { background: #e11d48; color: #fff; }
    .btn-warn  { background: #F7F3E8; color: #77612A; border-color: #e8dfc4; }
    .btn-warn:hover  { background: #77612A; color: #fff; }
    .btn-ok    { background: rgba(98,179,139,.18); color: #1f6b48; }
    .btn-ok:hover    { background: #62B38B; color: #0b3d2a; }
    .btn-muted { background: #f1f5f9; color: #475569; }
    .btn-muted:hover { background: #475569; color: #fff; }

    @keyframes pop { from { opacity: 0; transform: scale(.92) translateY(10px); } to { opacity: 1; transform: none; } }
    .modal-pop { animation: pop .18s ease-out; }
</style>