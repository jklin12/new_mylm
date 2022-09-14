<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 't_invoice';
    protected $primaryKey = 'inv_number';

    public $incrementing = false;
    
    protected $fillable  = ['inv_number', 'cust_number', 'sp_code', 'inv_type', 'inv_due', 'inv_post', 'inv_paid', 'inv_status', 'inv_start', 'inv_end', 'inv_info', 'inv_pay_method', 'pi_number', 'sp_nom'];

    public $timestamps = false;
}
