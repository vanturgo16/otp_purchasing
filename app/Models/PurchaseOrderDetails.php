<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetails extends Model
{
    use HasFactory;
    protected $table = 'purchase_order_details';
    protected $guarded=[
        'id'
    ];
     // Definisikan relasi many-to-one ke tabel master_salesman
     public function masterUnit()
     {
         return $this->belongsTo(\App\Models\MstUnits::class, 'master_units_id', 'id');
     }
}
