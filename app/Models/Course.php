<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    public function type()
    {
        return $this->hasOne(CourseType::class, 'id', 'type_id')->select(['id', 'title']);
    }
}
