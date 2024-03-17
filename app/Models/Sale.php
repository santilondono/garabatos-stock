<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $primaryKey = 'sale_id';

    public $timestamps = false;

    protected $fillable = [
        'sale_date',
        'user_id',
        'client_id',
        'is_cancelled'
    ];
}
