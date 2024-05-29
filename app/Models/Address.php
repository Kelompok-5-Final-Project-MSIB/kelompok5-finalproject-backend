<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_address';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_address',
        'id_user',
        'province',
        'city',
        'zip_code',
        'details',
        'shipping_cost'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
