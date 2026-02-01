<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseCategoryRequest;
use App\Http\Requests\ToggleActiveRequest;
use App\Http\Requests\UpdateCourseCategoryRequest;
use App\Http\Resources\categoryResource;
use App\Models\CourseCategory;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $limit   = (int) $request->get('limit');
        $search  = $request->get('search');
        $sortBy  = $request->get('sort_by', 'id');
        $order   = $request->get('order', 'asc');
        $is_active  = $request->get('is_active');

        $allowedSort = ['id', 'name', 'created_at'];

        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'id';
        }

        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        $query = CourseCategory::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($is_active) {
            $query->where('is_active', $is_active);
        }

        $query->orderBy($sortBy, $order);

        if ($limit && $limit > 0) {
            $query->limit($limit);
            $perPage = min($perPage, $limit);
        }

        $categories = $query->paginate($perPage);

        if ($categories->isEmpty()) {
            return $this->notFound('Categories not found.');
        }

        return categoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseCategoryRequest $request)
    {
        $validated = $request->validated();

        $category = CourseCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
        ]);

        return $this->created(['category' => new categoryResource($category)], 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = CourseCategory::find($id);

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        return $this->success(['category' => new categoryResource($category)], 'Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseCategoryRequest $request, string $id)
    {
        $category = CourseCategory::find($id);

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        $category->update($request->validated());

        return $this->success(
            ['category' => new categoryResource($category)],
            'Category updated successfully.'
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = CourseCategory::find($id);

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        $category->delete();

        return $this->success([], 'Category deleted successfully.');
    }

    public function toggleActive(ToggleActiveRequest $request, string $id)
    {
        $category = CourseCategory::find($id);

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        $category->update($request->validated());

        return $this->success(
            ['category' => new categoryResource($category)],
            'Category updated successfully.'
        );
    }
}
