<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class transaksi extends Model
{
    use HasFactory;

    protected $fillable = ['pelanggan_id', 'tanggal', 'total_pembayaran', 'metode_pembayaran'];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    // Relasi ke TransactionDetails
    public function transaksi_details()
    {
        return $this->hasMany(Transaksi_detail::class);
    }
}
