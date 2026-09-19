<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnSupplier extends Model
{
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $latest = static::orderBy('id', 'desc')->first();
                if (!$latest) {
                    $model->id = 'RSP-001';
                } else {
                    $number = intval(substr($latest->id, 4)) + 1;
                    $model->id = 'RSP-' . str_pad($number, 3, '0', STR_PAD_LEFT);
                }
            }
        });
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
