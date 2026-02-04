<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class userResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'profile_image' => $this->profileImageUrl(),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }

    protected function profileImageUrl(): string
    {
        if (!$this->profile_image) {
            return "https://cdn.pixabay.com/photo/2021/11/24/05/19/user-6820232_1280.png";
        }

        return asset('storage/' . $this->profile_image);
    }
}
