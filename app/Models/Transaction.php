<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'id_transaction';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id_transaction',
        'order_id',
        'id_user',
        'gross_amount',
        'transaction_status',
        'fraud_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
