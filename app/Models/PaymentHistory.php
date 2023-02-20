<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    public $table  = 'payment_history';

    protected $fillable = [
        'payment_id',
        'user_id',
        'previous_payment',
        'current_payment',
        'date'
    ];

    
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
