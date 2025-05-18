<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'mv_id',
        'name',
        'email',
        'firstname',
        'mem_id',
        'groups',
        'nickname'
    ];

}
