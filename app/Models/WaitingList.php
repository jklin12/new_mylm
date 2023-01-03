<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    use HasFactory;

    protected $table = 't_waiting_list_new';
    protected $primaryKey = 'wi_number';
    
    protected $fillable  = ["wi_member_card","wi_svc_begin","wi_type","wi_business","wi_business_type","wi_name","wi_pop","wi_acct_manager","wi_address","wi_prov","wi_city","wi_kecamatan","wi_kelurahan","wi_rw","wi_rt","wi_phone","wi_phone_name","wi_telp","wi_email","wi_birth_date","wi_sex","wi_job","wi_ident_type","wi_ident_number","wi_npwp","wi_home_pass","sp_code","wi_cont_begin","wi_cont_end","wi_trial","wi_tech_coord",'wi_bill_contact','wi_bill_address','wi_bill_zip_code','wi_bill_prov','wi_bill_city','wi_bill_phone','wi_bill_telp','wi_bill_email','wi_bill_desc'];
    public $timestamps = false;
}
