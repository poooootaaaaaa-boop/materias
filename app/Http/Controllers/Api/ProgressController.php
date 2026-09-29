<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizResult;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string'],
            'score' => ['required', 'integer', 'min:0', 'max:10'],
            'total_questions' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        // Si no hay usuario autenticado (modo niño/invitado), solo devolver OK
        if (! $request->user()) {
            return response()->json(['message' => 'Progreso registrado en modo invitado.'], 200);
        }

        // Verificar que el subject existe en la base de datos antes de guardar
        $subjectExists = \App\Models\Subject::where('slug', $validated['subject'])->where('active', true)->exists();
        if (! $subjectExists) {
            // Aceptar de todas formas para no romper el juego
            return response()->json(['message' => 'Materia no registrada, progreso ignorado.'], 200);
        }

        $result = $request->user()->quizResults()->create($validated);

        return response()->json([
            'result' => $result,
            'progress' => $this->progressFor($request->user()),
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json($this->progressFor($request->user()));
    }

    public function ranking(): JsonResponse
    {
        $ranking = User::query()
            ->where('is_admin', false)
            ->withCount('quizResults')
            ->withSum('quizResults', 'score')
            ->orderByDesc('quiz_results_sum_score')
            ->get(['id', 'name', 'school', 'country', 'state', 'gender'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'total_points' => (int) ($user->quiz_results_sum_score ?? 0),
                'games_completed' => (int) $user->quiz_results_count,
            ]);

        return response()->json($ranking);
    }

    private function progressFor(User $user): array
    {
        $results = $user->quizResults()->latest()->get();
        $bySubject = Subject::where('active', true)->pluck('slug')->mapWithKeys(function (string $subject) use ($results) {
            $subjectResults = $results->where('subject', $subject);

            return [$subject => [
                'points' => (int) $subjectResults->sum('score'),
                'best_score' => (int) ($subjectResults->max('score') ?? 0),
                'games_completed' => $subjectResults->count(),
            ]];
        })->all();

        $totalPoints = (int) $results->sum('score');
        $gamesCompleted = $results->count();
        $achievements = [
            ['id' => 'first_game', 'title' => 'Primera aventura', 'description' => 'Completaste tu primer juego.', 'icon' => '🌱', 'unlocked' => $gamesCompleted >= 1],
            ['id' => 'ten_points', 'title' => 'Diez estrellas', 'description' => 'Conseguiste 10 puntos.', 'icon' => '⭐', 'unlocked' => $totalPoints >= 10],
            ['id' => 'all_subjects', 'title' => 'Explorador', 'description' => 'Jugaste las tres materias.', 'icon' => '🧭', 'unlocked' => collect($bySubject)->every(fn (array $subject) => $subject['games_completed'] > 0)],
            ['id' => 'perfect_game', 'title' => 'Puntuación perfecta', 'description' => 'Lograste 10 respuestas correctas.', 'icon' => '🏆', 'unlocked' => $results->contains('score', 10)],
        ];

        return [
            'total_points' => $totalPoints,
            'games_completed' => $gamesCompleted,
            'subjects' => $bySubject,
            'achievements' => $achievements,
            'history' => $results->take(10)->map(fn (QuizResult $result) => [
                'id' => $result->id,
                'subject' => $result->subject,
                'score' => $result->score,
                'total_questions' => $result->total_questions,
                'completed_at' => $result->created_at->toISOString(),
            ])->values(),
        ];
    }

    public function childProgress(Request $request): JsonResponse
{
    $parent = $request->user();

    $child = $parent->children()
        ->where('age', '<', 18)
        ->first();

    if (! $child) {
        return response()->json([
            'message' => 'No hay un niño vinculado a esta cuenta.',
            'child' => null,
        ]);
    }

    $results = $child->quizResults()
        ->latest()
        ->get();

    $subjects = Subject::where('active', true)
        ->get();

    $subjectProgress = $subjects->map(function ($subject) use ($results) {

        $subjectResults = $results->where('subject', $subject->slug);

        $completed = $subjectResults->count();

        $totalScore = $subjectResults->sum('score');

        $totalQuestions = $subjectResults->sum('total_questions');

        $progress = $totalQuestions > 0
            ? round(($totalScore / $totalQuestions) * 100)
            : 0;

        $score = $completed > 0
            ? round($subjectResults->avg('score'), 1)
            : 0;

        return [
            'name' => $subject->name,
            'slug' => $subject->slug,
            'progress' => $progress,
            'score' => $score,
            'completed' => $completed,
            'activities' => 10,
        ];
    })->values();

    return response()->json([
        'child' => [
            'id' => $child->id,
            'name' => $child->name,
            'age' => $child->age,
            'school' => $child->school,
        ],
        'subjects' => $subjectProgress,
        'total_points' => $results->sum('score'),
        'games_completed' => $results->count(),
    ]);
}
}