<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConditionItem extends Model
{
    protected $fillable = ['text'];

    public function vacancies() {
        return $this->belongsToMany(Vacancy::class, 'vacancy_condition');
    }

    protected $casts = ['text' => 'string'];
}