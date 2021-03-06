<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookStore extends Model
{
    use HasFactory;
    protected $table = 'bookStore';
    public $primaryKey = 'id';
    protected $fillable = [
        'storeName', 'cashBalance', 'openingHours',
    ];
    public function showBooks()
    {
        return $this->hasMany(Books::class, 'bookStore_id');
    }
}
