<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courseId = $this->route('course');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_free' => ['required', 'boolean'],
            'status' => ['required', 'in:draft,published,unpublished'],
            'category_id' => ['required', 'exists:course_categories,id'],
            'course_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],

            'tags' => ['nullable', 'array'],
        'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a string.',
            'title.max' => 'Title must not exceed 255 characters.',
            'description.string' => 'Description must be a string.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'is_free.required' => 'Is free is required.',
            'is_free.boolean' => 'Is free must be a boolean.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of the following: draft, published, unpublished.',
            'category_id.required' => 'Category ID is required.',
            'category_id.exists' => 'Category ID does not exist.',
            'course_image.required' => 'Please select an image to upload.',
            'course_image.image' => 'The file must be an image.',
            'course_image.mimes' => 'Allowed image types: jpeg, png, jpg, gif, webp.',
            'course_image.max' => 'Maximum image size is 10MB.',
        ];
    }
}

