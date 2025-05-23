<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActionMember extends Model
{
    protected $fillable = ['reg_state', 'action_id', 'web_id','group', 'reg_email'];

    // Update einer Anmeldung auf der Basis von action_id und web_id
    public static function updateRecord(int $actionId, int $webId, array $updateData)
    {
        Log::debug("--- ActionMember.updateRecord $actionId $webId ".print_r($updateData, true));
        // einzigen Datensatz holen
        $record = self::where('action_id', $actionId)
            ->where('web_id', $webId)
            ->firstOrFail();

        //Log::debug('ActionMember.record '. print_r($record, true));

        return $record->update($updateData);
    }

    public static function deleteRecord(int $actionId, int $webId): void
    {
        Log::debug("--- ActionMember.deleteRecord $actionId $webId");

        self::where('action_id', $actionId)
            ->where('web_id', $webId)
            ->delete();

    }

    /*public static function existsRecord(int $webId, int $actionId): bool
    {
        Log::debug("--- ActionMember.existsRecord $actionId $webId ---");
        return self::where('web_id', $webId)
            ->where('action_id', $actionId)
            ->exists();
    }*/
}


