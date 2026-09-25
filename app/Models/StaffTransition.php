<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffTransition extends Model
{
    protected $fillable = [
        'type',
        'outgoing_user_id',
        'incoming_user_ids',
        'performed_by_id',
        'outgoing_name',
        'incoming_names',
        'counts',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'incoming_user_ids' => 'array',
            'counts' => 'array',
        ];
    }

    public function outgoing()
    {
        return $this->belongsTo(User::class, 'outgoing_user_id')->withTrashed();
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_id')->withTrashed();
    }
}
