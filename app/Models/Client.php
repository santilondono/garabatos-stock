<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $primaryKey = 'client_id';

    public $timestamps = false;


    protected $fillable = [
        'client_name',
        'shipping_mark',
        'country'
    ];
    
    protected $guarded = [ //Columnas que no quiero incluir en el modelo

    ];
}
