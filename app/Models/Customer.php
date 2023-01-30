<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public $table  = 'customers';

    protected $fillable = [
        'customer_name',
        'phone_number',
        'address',
        'gender',
        'email',
        'is_active',
        'created_by'
    ];
}
