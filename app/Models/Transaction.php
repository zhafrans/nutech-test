<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $casts = [
        'transaction_type' => TransactionType::class,
    ];

    public function serviceProduct()
    {
        return $this->belongsTo(ServiceProduct::class);
    }
}
