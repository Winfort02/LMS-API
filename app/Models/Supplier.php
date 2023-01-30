<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    public $table  = 'suppliers';

    protected $fillable = [
        'supplier_name',
        'contact_number',
        'email',
        'address',
        'is_active',
        'created_by'
    ];
}
