<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\pelanggan;
use App\Models\produk;
use App\Models\transaksi;
use App\Models\transaksi_detail;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CreateTransaksi extends CreateRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        DB::transaction(function () use (&$data) {
            $data = json_decode(request()->getContent(), true);
            if (isset($data['components'][0]['snapshot'])) {
                $snapshot = json_decode($data['components'][0]['snapshot'], true);

                if (isset($snapshot['data']['data'][0])) {
                    $dataTransaksi = $snapshot['data']['data'][0];
                    $dataPelanggan = $dataTransaksi['pelanggan'][0];

                    // Simpan data pelanggan
                    $pelanggan = Pelanggan::create([
                        'nama' => $dataPelanggan['nama'],
                        'email' => $dataPelanggan['email'],
                        'no_hp' => $dataPelanggan['no_hp'],
                        'alamat' => $dataPelanggan['alamat'],
                    ]);

                    $tanggalTransaksi = now();
                    // Simpan data transaksi
                    $transaksi = Transaksi::create([
                        'pelanggan_id' => $pelanggan->id,
                        'tanggal' => $tanggalTransaksi,
                        'total_pembayaran' => $dataTransaksi['total_pembayaran'],
                        'metode_pembayaran' => $dataTransaksi['metode_pembayaran'],
                    ]);

                    $transaksiDetails = $dataTransaksi['transaksi_details'][0];
                    foreach ($transaksiDetails as $key => $detail) {
                        // Simpan data transaksi_detail
                        Transaksi_detail::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $detail[0]['produk_id'],
                            'jumlah' => $detail[0]['jumlah'],
                            'harga' => $detail[0]['harga'],
                            'total_harga' => $detail[0]['total_harga'],
                        ]);

                        // Update stok produk
                        $produk = Produk::find($detail[0]['produk_id']);
                        if ($produk) {
                            if ($produk->stok >= $detail[0]['jumlah']) {
                                $produk->stok -= $detail[0]['jumlah'];
                                $produk->save();
                            } else {
                                throw new \Exception("Stok produk tidak mencukupi.");
                            }
                        }
                    }

                    // Update data transaksi dengan ID yang benar
                    $data['pelanggan_id'] = $pelanggan->id;
                } else {
                    throw new \Exception("Data transaksi tidak ditemukan.");
                }
            } else {
                throw new \Exception("Snapshot data tidak ditemukan.");
            }
        });

        return $data;
    }
}
