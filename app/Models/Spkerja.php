<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spkerja extends Model
{
    use HasFactory;

    protected $table = 't_field_task';
    protected $primaryKey = 'ft_number';

    public $incrementing = false;
    
    protected $fillable  = ['ft_number', 'ft_received', 'ft_updated', 'ft_updated_by', 'ft_solved', 'ft_type', 'ft_svc_type', 'ft_status', 'cust_number', 'sp_code', 'cmp_number', 'ft_desc', 'ft_desc', 'ft_equipment','ft_plan','ft_coordinator','ft_executor1','ft_executor2'];

    public $timestamps = false;
}
