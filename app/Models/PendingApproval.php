<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingApproval extends Model
{
    protected $fillable = ['type', 'approvable_type', 'approvable_id', 'requested_by', 'approved_by', 'status', 'requester_note', 'reviewer_note', 'decided_at'];

    protected $casts = ['decided_at' => 'datetime'];

    public function approvable()  { return $this->morphTo(); }
    public function requester()   { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer()    { return $this->belongsTo(User::class, 'approved_by'); }
}
