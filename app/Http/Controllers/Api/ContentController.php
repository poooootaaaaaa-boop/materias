<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DifficultyLevel;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = Subject::where('active', true)->orderBy('id')->get();
        $levels = DifficultyLevel::where('active', true)->orderBy('min_age')->get();
        $activities = Activity::with(['subject:id,name,slug,emoji', 'difficultyLevel:id,name,slug'])
            ->where('active', true)
            ->orderBy('order_num')
            ->orderBy('id')
            ->get()
            ->map(function ($act) {
                $data = $act->data ?? [];
                $options = $act->options ?? [];
                $correct = null;
                if (isset($data['correct'])) {
                    $correct = (int) $data['correct'];
                } elseif (is_array($options) && $act->answer !== null) {
                    $idx = array_search((string) $act->answer, array_map('strval', $options));
                    $correct = $idx !== false ? $idx : (is_numeric($act->answer) ? (int) $act->answer : null);
                }

                return [
                    'id' => $act->id,
                    'subject_id' => $act->subject_id,
                    'subject_slug' => $act->subject?->slug,
                    'subject' => $act->subject?->slug,
                    'difficulty_level_id' => $act->difficulty_level_id,
                    'type' => $act->type,
                    'kind' => $act->kind,
                    'q' => $act->question,
                    'question' => $act->question,
                    'options' => $options,
                    'correct' => $correct,
                    'answer' => $act->answer,
                    'expr' => $data['expr'] ?? null,
                    'tokens' => $data['tokens'] ?? null,
                    'pairs' => $data['pairs'] ?? null,
                    'clue' => $data['clue'] ?? null,
                    'speak' => $act->speak,
                    'speakLang' => $act->speak_lang,
                    'speak_lang' => $act->speak_lang,
                    'data' => $data,
                    'active' => $act->active,
                ];
            });

        return response()->json([
            'subjects' => $subjects,
            'levels' => $levels,
            'activities' => $activities,
        ]);
    }
}