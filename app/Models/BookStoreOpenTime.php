<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookStoreOpenTime extends Model
{
    use HasFactory;
    protected $table = 'bookStoreOpenTime';
    public $primaryKey = 'id';
    protected $dates = [
        'openTimeMon', 'closeTimeMon',
        'openTimeTues', 'closeTimeTues',
        'openTimeWed', 'closeTimeWed',
        'openTimeThurs', 'closeTimeThurs',
        'openTimeFri', 'closeTimeFri',
        'openTimeSat', 'closeTimeSat',
        'openTimeSun', 'closeTimeSun',
    ];
    protected $fillable = [
        'storeName', 'openTimeMon', 'closeTimeMon',
        'openTimeTues', 'closeTimeTues',
        'openTimeWed', 'closeTimeWed',
        'openTimeThurs', 'closeTimeThurs',
        'openTimeFri', 'closeTimeFri',
        'openTimeSat', 'closeTimeSat',
        'openTimeSun', 'closeTimeSun',
    ];
}
