<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    use HasFactory;
    protected $table = 't_employee';
    protected $primaryKey = 'emp_number';

    public $incrementing = false;

    const CREATED_AT = 'emp_join';
    const UPDATED_AT = 'emp_updated';

    protected $hidden = [
        'emp_pwd2',
    ];

    public function getAuthPassword()
    {
        return $this->emp_pwd_bcrypt;
    }
}
