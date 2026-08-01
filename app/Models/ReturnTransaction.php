<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnTransaction extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(ReturnDetail::class, 'return_transaction_id');
    }
}
