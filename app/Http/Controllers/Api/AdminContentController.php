<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DifficultyLevel;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'subjects' => Subject::withCount('activities')->orderBy('id')->get(),
            'levels' => DifficultyLevel::withCount('activities')->orderBy('min_age')->get(),
            'activities' => Activity::with(['subject:id,name,slug,emoji', 'difficultyLevel:id,name'])->latest('id')->get(),
        ]);
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'accent' => ['nullable', 'string', 'max:20'],
            'accent_dark' => ['nullable', 'string', 'max:20'],
            'bg' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        return response()->json(Subject::create($data), 201);
    }

    public function updateSubject(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'accent' => ['nullable', 'string', 'max:20'],
            'accent_dark' => ['nullable', 'string', 'max:20'],
            'bg' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
        ]);
        $subject->update($data);
        return response()->json($subject);
    }

    public function destroySubject(Subject $subject): JsonResponse
    {
        $subject->delete();
        return response()->json(['message' => 'Materia eliminada.']);
    }

    public function storeLevel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:80'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:18'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:18'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        return response()->json(DifficultyLevel::create($data), 201);
    }

    public function updateLevel(Request $request, DifficultyLevel $level): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'max:80'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:18'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:18'],
            'active' => ['boolean'],
        ]);
        $level->update($data);
        return response()->json($level);
    }

    public function destroyLevel(DifficultyLevel $level): JsonResponse
    {
        $level->delete();
        return response()->json(['message' => 'Nivel eliminado.']);
    }

    public function storeActivity(Request $request): JsonResponse
    {
        $data = $this->validatedActivity($request);
        $activity = Activity::create($data);
        return response()->json($activity->load(['subject:id,name,slug,emoji', 'difficultyLevel:id,name']), 201);
    }

    public function updateActivity(Request $request, Activity $activity): JsonResponse
    {
        $activity->update($this->validatedActivity($request));
        return response()->json($activity->load(['subject:id,name,slug,emoji', 'difficultyLevel:id,name']));
    }

    public function destroyActivity(Activity $activity): JsonResponse
    {
        $activity->delete();
        return response()->json(['message' => 'Actividad eliminada.']);
    }

    private function validatedActivity(Request $request): array
    {
        // Support subject passed by slug or label if subject_id is not directly given
        if (!$request->filled('subject_id') && $request->filled('subject')) {
            $sub = Subject::where('slug', $request->input('subject'))
                ->orWhere('name', $request->input('subject'))
                ->first();
            if ($sub) {
                $request->merge(['subject_id' => $sub->id]);
            }
        }

        if (!$request->filled('question') && $request->filled('q')) {
            $request->merge(['question' => $request->input('q')]);
        }

        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'difficulty_level_id' => ['nullable', 'exists:difficulty_levels,id'],
            'type' => ['required', 'in:choice,order,match,fillnum,crossword,meaning,relate,audio,spell,input,matching,memory'],
            'kind' => ['nullable', 'string', 'max:60'],
            'question' => ['required', 'string', 'max:1000'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable'],
            'answer' => ['nullable'],
            'data' => ['nullable', 'array'],
            'speak' => ['nullable', 'string', 'max:255'],
            'speak_lang' => ['nullable', 'string', 'max:10'],
            'order_num' => ['nullable', 'integer'],
            'active' => ['boolean'],
        ]);
    }
}