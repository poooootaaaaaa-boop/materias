<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassroomController extends Controller
{
    public function teacherIndex(Request $request): JsonResponse
    {
        return response()->json(Classroom::where('teacher_id', $request->user()->id)->with(['students:id,name,email,school', 'activities:id,question,type,subject_id,difficulty_level_id'])->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        do { $code = Str::upper(Str::random(6)); } while (Classroom::where('join_code', $code)->exists());
        return response()->json($request->user()->classrooms()->create([...$data, 'join_code' => $code]), 201);
    }

    public function updateActivities(Request $request, Classroom $classroom): JsonResponse
    {
        abort_unless($classroom->teacher_id === $request->user()->id, 403);
        $data = $request->validate(['activity_ids' => ['array'], 'activity_ids.*' => ['integer', 'exists:activities,id']]);
        $classroom->activities()->sync($data['activity_ids'] ?? []);
        return response()->json($classroom->load('activities'));
    }

    public function destroy(Request $request, Classroom $classroom): JsonResponse
    {
        abort_unless($classroom->teacher_id === $request->user()->id, 403);
        $classroom->delete();
        return response()->json(['message' => 'Clase eliminada.']);
    }

    public function join(Request $request): JsonResponse
    {
        $data = $request->validate(['join_code' => ['required', 'string', 'size:6']]);
        $classroom = Classroom::where('join_code', Str::upper($data['join_code']))->where('active', true)->firstOrFail();
        $classroom->students()->syncWithoutDetaching([$request->user()->id]);
        return response()->json($classroom->load(['teacher:id,name', 'activities:id,question,type,options,answer,data,subject_id,difficulty_level_id']));
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json($request->user()->enrolledClassrooms()->with(['teacher:id,name', 'activities:id,question,type,options,answer,data,subject_id,difficulty_level_id'])->get());
    }

    public function activities(): JsonResponse
    {
        return response()->json(Activity::with(['subject:id,name,slug,emoji', 'difficultyLevel:id,name,slug'])->where('active', true)->latest()->get());
    }
}