<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePorfoma extends Model
{
    use HasFactory;
    protected $table = 't_invoice_porfoma';
    protected $primaryKey = 'inv_number';

    public $incrementing = false;

    const CREATED_AT = 'inv_post';
    const UPDATED_AT = 'inv_updated';

    public function invNumber()
    {
        return $this->hasOne(Invoice::class);
    }
}
