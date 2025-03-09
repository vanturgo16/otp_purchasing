<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstRules extends Model
{
    use HasFactory;
    protected $table = 'mst_rules';
    protected $guarded=[
        'id'
    ];
}
