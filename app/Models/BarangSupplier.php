<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangSupplier extends Model
{
    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
