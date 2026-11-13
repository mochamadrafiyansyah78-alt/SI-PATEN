{{-- Muat Chart.js sekali + helper bersama. Pakai: @include('partials.chart-dasar') sebelum skrip diagram. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(() => {
    window.diagramTersedia = typeof Chart !== 'undefined';

    // Kembalikan elemen kanvas, atau tampilkan pesan bila Chart.js gagal dimuat (mis. tanpa internet).
    window.ambilKanvas = (idKanvas) => {
        const kanvas = document.getElementById(idKanvas);
        if (!kanvas) return null;
        if (!window.diagramTersedia) {
            kanvas.parentElement.innerHTML = '<p class="flex h-full items-center justify-center text-sm text-slate-400">Diagram tidak dapat dimuat. Periksa koneksi internet.</p>';
            return null;
        }
        return kanvas;
    };

    if (!window.diagramTersedia) return;

    Chart.defaults.font.family = '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;

    window.gambarDonat = (idKanvas, daftarLabel, daftarNilai, daftarWarna) => {
        const kanvas = window.ambilKanvas(idKanvas);
        if (!kanvas) return;

        const total = daftarNilai.reduce((jumlah, nilai) => jumlah + nilai, 0);
        if (total === 0) {
            kanvas.parentElement.innerHTML = '<p class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data.</p>';
            return;
        }

        new Chart(kanvas, {
            type: 'doughnut',
            data: {
                labels: daftarLabel,
                datasets: [{ data: daftarNilai, backgroundColor: daftarWarna, borderColor: '#ffffff', borderWidth: 2 }],
            },
            options: {
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (item) => ` ${item.label}: ${item.parsed.toLocaleString('id-ID')} (${Math.round(item.parsed / total * 100)}%)`,
                        },
                    },
                },
            },
        });
    };
})();
</script>
