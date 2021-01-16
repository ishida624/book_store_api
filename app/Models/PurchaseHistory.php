<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    use HasFactory;
    protected $table = 'purchaseHistory';
    public $primaryKey = 'id';
    protected $fillable = [
        'bookName', 'storeName', 'transactionAmount', 'transactionDate', 'user_id',
    ];
}
