<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GeocodeKecamatan extends Command
{
    protected $signature = 'geo:kecamatan {--force}';

    protected $description = 'Isi latitude/longitude tiap kecamatan Kab. Lebak via geocoding Nominatim (OpenStreetMap)';

    public function handle(): int
    {
        $query = Kecamatan::query();

        if (! $this->option('force')) {
            $query->whereNull('latitude');
        }

        $kecamatanList = $query->get();

        if ($kecamatanList->isEmpty()) {
            $this->info('Semua kecamatan sudah memiliki koordinat. Gunakan --force untuk timpa ulang.');
            return self::SUCCESS;
        }

        $this->info("Geocoding {$kecamatanList->count()} kecamatan...");
        $bar = $this->output->createProgressBar($kecamatanList->count());

        foreach ($kecamatanList as $kec) {
            $namaBersih = trim(str_ireplace(['kec.', 'kecamatan'], '', $kec->nama_kecamatan));

            // Coba beberapa variasi query, berhenti di percobaan pertama yang berhasil
            $percobaan = [
                // 1) Parameter terstruktur (paling akurat untuk level kecamatan/kota)
                ['city' => $namaBersih, 'county' => 'Lebak', 'state' => 'Banten', 'country' => 'Indonesia'],
                // 2) Free text tanpa kata "Kecamatan"
                ['q' => "{$namaBersih}, Kabupaten Lebak, Banten, Indonesia"],
                // 3) Free text dengan kata "Kecamatan" (percobaan awal)
                ['q' => "Kecamatan {$namaBersih}, Kabupaten Lebak, Banten, Indonesia"],
            ];

            $ditemukan = false;

            foreach ($percobaan as $params) {
                $response = Http::withHeaders([
                    'User-Agent' => 'SI-PATEN-DinasPendidikanLebak/1.0 ([email protected])',
                ])->get('https://nominatim.openstreetmap.org/search', array_merge($params, [
                    'format'       => 'json',
                    'limit'        => 1,
                    'countrycodes' => 'id',
                ]));

                $result = $response->json();

                if (! empty($result)) {
                    $kec->update([
                        'latitude'    => $result[0]['lat'],
                        'longitude'   => $result[0]['lon'],
                        'geocoded_at' => now(),
                    ]);
                    $ditemukan = true;
                    break;
                }

                sleep(1); // wajib jeda antar request Nominatim
            }

            if (! $ditemukan) {
                $this->warn("\nTidak ditemukan koordinat untuk: {$kec->nama_kecamatan} (isi manual).");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Selesai. Cek kolom latitude/longitude pada tabel kecamatans.');

        return self::SUCCESS;
    }
}