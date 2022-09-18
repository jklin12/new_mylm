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

    const CREATED_AT = 'created';
    protected $fillable = ['cust_number,cust_name,cust_address,cust_phone'];
    /*public function custPackage()
    {
        return $this->hasMany(CustPackage::class,'cust_number');
    }*/

}
