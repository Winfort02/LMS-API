<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    public $table  = 'logs';

    protected $fillable = [
        'user_id',
        'logs',
        'remarks',
        'date'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
