<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppEvent extends Model
{
    protected $table = 'app_event';

    protected $fillable = [
        'text',
        'web_id',
    ];

    public $timestamps = true;

    /**
     * Einfacher Log-Aufruf:
     * AppEvent::log('Text', $optionalWebId);
     */
    public static function log(string $text, ?string $webId = null): void
    {
        self::create([
            'text'   => $text,
            'web_id' => $webId,
        ]);
    }
}
