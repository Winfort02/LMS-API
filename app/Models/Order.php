<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $table  = 'orders';

    protected $fillable = [
        'customer_id',
        'user_id',
        'transaction_number',
        'sales_order_number',
        'sales_date',
        'payment_type',
        'payment',
        'total_amount',
        'order_status',
        'sales_type',
        'remarks',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
