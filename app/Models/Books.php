<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;
    protected $table = 'books';
    public $primaryKey = 'id';
    protected $fillable = [
        'bookName', 'price', 'bookStore_id',
    ];
    public function showStore()
    {
        return $this->belongsTo(BookStore::class, 'bookStore_id');
    }
}
