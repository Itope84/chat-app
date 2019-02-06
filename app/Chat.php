<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mpociot\Firebase\SyncsWithFirebase;


class Chat extends Model
{
    use SyncsWithFirebase;

    protected $fillable = ['initiator_id', 'partner_id'];

    public function initiator()
    {
        return $this->belongsTo('App\User', 'initiator_id');
    }

    public function partner()
    {
        return $this->belongsTo('App\User', 'partner_id');
    }

    public static function exists($user1, $user2) {
        return Chat::where(function($q) use ($user1, $user2) {
            $q->where('initiator_id', $user1->id)->where('partner_id', $user2->id);
        })->orWhere(function($q) use ($user1, $user2) {
            $q->where('initiator_id', $user2->id)->where('partner_id', $user1->id);
        })->first();
    }
}
