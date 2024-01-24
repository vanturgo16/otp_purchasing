<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPurchaseTransactions extends Model
{
    use HasFactory;
    protected $table = 'data_purchase_transactions';
    protected $guarded=[
        'id'
    ];
}
