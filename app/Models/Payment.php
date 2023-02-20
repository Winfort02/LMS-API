<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public $table  = 'payments';

    protected $fillable = [
        'order_id',
        'customer_id',
        'user_id',
        'payment_date',
        'due_amount',
        'payment_type',
        'amount',
        'remarks',
        'status'
    ];

    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment_history() {
        return $this->hasMany(PaymentHistory::class, 'payment_id', 'id');
    }
}
