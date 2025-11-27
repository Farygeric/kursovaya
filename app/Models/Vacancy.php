<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = ['name', 'department_id', 'status'];

    public function department() { return $this->belongsTo(Department::class); }
    public function applications() { return $this->hasMany(Application::class); }

    public function responsibilities() {
        return $this->belongsToMany(ResponsibilityItem::class, 'vacancy_responsibility')
                    ->withPivot('sort_order')
                    ->orderBy('vacancy_responsibility.sort_order');
    }

    public function requirements() {
        return $this->belongsToMany(RequirementItem::class, 'vacancy_requirement')
                    ->withPivot('sort_order')
                    ->orderBy('vacancy_requirement.sort_order');
    }

    public function conditions() {
        return $this->belongsToMany(ConditionItem::class, 'vacancy_condition')
                    ->withPivot('sort_order')
                    ->orderBy('vacancy_condition.sort_order');
    }
}