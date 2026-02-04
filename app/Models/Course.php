<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'description',
        'price',
        'is_free',
        'course_image',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tag');
    }


    public function instructor()
    {
        return $this->belongsTo(User::class);
    }
}
