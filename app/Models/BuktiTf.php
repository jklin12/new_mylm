<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiTf extends Model
{
    use HasFactory;

    protected $table = 't_bukti_tf';
    protected $primaryKey = 'bukti_tf_id';
    


    protected $fillable = ['bukti_tf_cust', 'bukti_tf_inv', 'bukti_tf_status','bukti_tf_file','bukti_tf_desc','bukti_tf_date'];
}
