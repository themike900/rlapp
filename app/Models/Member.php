<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',
        'email',
        'firstname',
        'mem_id',
        'groups',
        'nickname'
    ];

}
