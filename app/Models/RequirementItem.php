<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementItem extends Model
{
    protected $fillable = ['text'];

    public function vacancies() {
        return $this->belongsToMany(Vacancy::class, 'vacancy_requirement');
    }

    protected $casts = ['text' => 'string'];
}