<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $limit   = (int) $request->get('limit');
        $search  = $request->get('search');

        $sortable = [
            'id'         => 'id',
            'title'      => 'title',
            'price'      => 'price',
            'created_at' => 'created_at',
        ];

        $sortBy = $sortable[$request->get('sort_by')] ?? 'id';
        $order  = in_array($request->get('order'), ['asc', 'desc'])
            ? $request->get('order')
            : 'desc';

        $categoryId   = $request->get('category_id');
        $instructorId = $request->get('instructor_id');
        $status       = $request->get('status');
        $priceMin     = $request->get('price_min');
        $priceMax     = $request->get('price_max');
        $createdFrom  = $request->get('created_from');
        $createdTo    = $request->get('created_to');

        $isFree = $request->has('is_free')
            ? filter_var($request->get('is_free'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $query = Course::query()
            ->with(['category', 'instructor', 'tags'])
            ->whereHas('category', fn($c) => $c->where('is_active', 1))

            // ✅ FIXED instructor filter
            ->when(
                $instructorId,
                fn($q) =>
                $q->where('instructor_id', $instructorId)
            )

            ->when(
                $categoryId,
                fn($q) =>
                $q->where('category_id', $categoryId)
            )

            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhereHas('instructor', function ($i) use ($search) {
                            $i->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })

            ->when(
                !is_null($isFree),
                fn($q) =>
                $q->where('is_free', $isFree)
            )

            ->when(
                $status,
                fn($q) =>
                $q->where('status', $status)
            )

            ->when(
                !is_null($priceMin),
                fn($q) =>
                $q->where('price', '>=', $priceMin)
            )

            ->when(
                !is_null($priceMax),
                fn($q) =>
                $q->where('price', '<=', $priceMax)
            )

            ->when(
                $createdFrom,
                fn($q) =>
                $q->whereDate('created_at', '>=', $createdFrom)
            )

            ->when(
                $createdTo,
                fn($q) =>
                $q->whereDate('created_at', '<=', $createdTo)
            )

            ->orderBy($sortBy, $order);

        $courses = $limit > 0
            ? $query->limit($limit)->get()
            : $query->paginate($perPage);

        if ($courses->isEmpty()) {
            return $this->notFound('No courses found');
        }

        return CourseResource::collection($courses);
    }


    public function getCourseByInstructorId(Request $request, int $instructorId)
    {
        $user = User::find($instructorId);

        if (!$user) {
            return $this->notFound('Instructor not found');
        }

        $request->merge(['instructor_id' => $instructorId]);

        return $this->index($request);
    }


    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $authUser = $request->user();
            $data = $request->validated();

            if ($request->hasFile('course_image')) {
                $image = $request->file('course_image');
                $imageName = Str::slug($data['title']) . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                $data['course_image'] = $image->storeAs(
                    'course_images',
                    $imageName,
                    'public'
                );
            }


            $isFree = filter_var($data['is_free'], FILTER_VALIDATE_BOOLEAN);
            $data['price'] = $isFree ? 0 : $data['price'];
            $data['is_free'] = $isFree;
            $data['instructor_id'] = $authUser->id;

            $course = Course::create($data);


            if (!empty($data['tags'])) {

                $tagIds = collect($data['tags'])->map(function ($tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => ucfirst($tagName)]
                    );

                    return $tag->id;
                });

                $course->tags()->sync($tagIds);
            }

            $course->load(['category', 'tags']);

            return $this->success(
                ['course' => new CourseResource($course)],
                'Course created successfully.'
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $course = Course::with(['category', 'instructor','tags'])->find($id);

            if (!$course) {
                return $this->notFound('Course not found.');
            }

            return $this->success(
                ['course' => new CourseResource($course)],
                'Course retrieved successfully.'
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(UpdateCourseRequest $request, string $id)
    {
        try {
            $authUser = $request->user();

            $course = Course::find($id);
            if (!$course) {
                return $this->notFound('Course not found.');
            }

            $data = $request->validated();


            if (array_key_exists('is_free', $data)) {
                $isFree = filter_var($data['is_free'], FILTER_VALIDATE_BOOLEAN);
                $data['is_free'] = $isFree;

                if ($isFree) {
                    $data['price'] = 0;
                }
            }


            if ($request->hasFile('course_image')) {
                $image = $request->file('course_image');

                if ($course->course_image) {
                    Storage::disk('public')->delete($course->course_image);
                }

                $imageName = Str::slug($data['title'] ?? $course->title)
                    . '-' . uniqid()
                    . '.' . $image->getClientOriginalExtension();

                $data['course_image'] = $image->storeAs(
                    'course_images',
                    $imageName,
                    'public'
                );
            }


            $data['instructor_id'] = $authUser->id;
            $course->update($data);


            if (array_key_exists('tags', $data)) {

                $tagIds = collect($data['tags'])->map(function ($tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => ucfirst($tagName)]
                    );

                    return $tag->id;
                });

                $course->tags()->sync($tagIds);
            }

            $course->load(['category', 'tags']);

            return $this->success(
                ['course' => new CourseResource($course)],
                'Course updated successfully.'
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return $this->notFound('Course not found.');
            }

            $course->delete();

            return $this->success(
                ['course' => new CourseResource($course)],
                'Course deleted successfully.'
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
