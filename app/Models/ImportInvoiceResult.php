<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportInvoiceResult extends Model
{
    use HasFactory;
    protected $table = 'import_inv_results';
    protected $fillable = [
        'import_date', 'file_import','note','file_report'
    ];
}
