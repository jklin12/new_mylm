<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 't_customer';
    protected $primaryKey = 'cust_number';

    public $incrementing = false;
    public $timestamps = false;

    /*public function custPackage()
    {
        return $this->hasMany(CustPackage::class,'cust_number');
    }*/

}
