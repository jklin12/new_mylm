<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustPackage extends Model
{
    use HasFactory;
    protected $table = 'trel_cust_pkg';
    protected $primaryKey = '_nomor';

    public $incrementing = false;
    public $timestamps = false;
}
