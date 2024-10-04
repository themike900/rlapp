<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionType extends Model
{
    protected function casts(): array
    {
        return [
            'prams' => 'array',
        ];
    }
}
