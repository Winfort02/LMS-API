<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    public $table  = 'stock_in';

    protected $fillable = [
        'supplier_id',
        'product_id',
        'user_id',
        'transaction_number',
        'van_number',
        'date',
        'quantity',
        'status'
    ];

    protected static function boot() {
        parent::boot();
    
        static::creating(function($model){
            $max = self::max('transaction_number') ?? 0;
            $no = intval($max) + 1;

            $model->transaction_number = str_pad($no, 10, '0', STR_PAD_LEFT);
        }); 
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
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
