<?php

namespace EliteHub\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentProvider extends Model
{
    use HasFactory;


    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function methods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
