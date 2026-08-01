<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnDetail extends Model
{
    protected $guarded = [];

    public function returnTransaction()
    {
        return $this->belongsTo(ReturnTransaction::class, 'return_transaction_id');
    }

    public function barangDireturn()
    {
        return $this->belongsTo(Barang::class, 'barang_direturn_id');
    }

    public function barangPengganti()
    {
        return $this->belongsTo(Barang::class, 'barang_pengganti_id');
    }
}
