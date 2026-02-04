<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'instructor_id' => $this->instructor_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'price'         => $this->price,
            'status'        => $this->status,
            'is_free'       => $this->is_free,
            'course_image'  => $this->courseImageUrl(),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

            'instructor' => new userResource(
                $this->whenLoaded('instructor')
            ),

            'category' => new categoryResource(
                $this->whenLoaded('category')
            ),

            
            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),
        ];
    }

    protected function courseImageUrl(): string
    {
        if (!$this->course_image) {
            return "https://foundr.com/wp-content/uploads/2021/09/Best-online-course-platforms.png";
        }

        return asset('storage/' . $this->course_image);
    }
}
