<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'total_amount',
        'order_status',
        'sales_type',
        'remarks',
        'status'
    ];

    protected static function boot() {
        parent::boot();
        
            static::creating(function($model){
                $date = Carbon::now()->format('Ymd');
                $max = self::where('transaction_number', 'like', '%'.$date . '%')->max('transaction_number') ?? 0;
                $no = substr($max,11,14);
                $no++;
                $no = str_pad($no, 4, '0', STR_PAD_LEFT);
                $model->transaction_number = 'SO-'.$date.$no;
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

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'id', 'order_id');
    }
}
