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

    const CREATED_AT = 'inv_post';
    const UPDATED_AT = 'inv_updated';
}
