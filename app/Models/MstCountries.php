<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstCountries extends Model
{
    use HasFactory;
    protected $table = 'master_countries';
    protected $guarded=[
        'id'
    ];
}
