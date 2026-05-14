<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentUnit extends Model
{
    protected $table = 'assignment_unit';

    protected $fillable = [
        'audit_assignment_id',
        'unit_diperiksa_id',
    ];

    public function auditAssignment()
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function unitDiperiksa()
    {
        return $this->belongsTo(UnitDiperiksa::class);
    }
}