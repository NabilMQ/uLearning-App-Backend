<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\ModelTree;

class CourseType extends Model
{
    use HasFactory;
    use ModelTree;

    public function courses()
    {
        return $this->hasMany(Course::class, 'type_id', 'id');
    }
}
