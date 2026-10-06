<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MahasiswaLog extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'mahasiswa_logs';

    protected $fillable = [
        'action',
        'data',
        'created_at'
    ];
}
