<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    use HasFactory;

    protected $table = 'outputs';

    protected $primaryKey = 'output_id';

    public $timestamps = false;

    protected $fillable = [
        'output_date',
        'user_id',
        'reason'
    ];
}
