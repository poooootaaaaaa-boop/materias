<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $children = User::where('is_admin', false);
        $total = (clone $children)->count();
        $active = (clone $children)->whereHas('quizResults')->count();

        return response()->json([
            'summary' => ['registered_children' => $total, 'active_children' => $active, 'schools' => (clone $children)->whereNotNull('school')->distinct('school')->count('school')],
            'gender' => $this->group($children, 'gender'),
            'countries' => $this->group($children, 'country'),
            'states' => $this->group($children, 'state'),
            'schools' => $this->schoolUsage(10),
        ]);
    }

    private function group($query, string $column, int $limit = 20): array
    {
        $query = clone $query;
        return $query->whereNotNull($column)->select($column, DB::raw('count(*) as total'))
            ->groupBy($column)->orderByDesc('total')->limit($limit)->get()
            ->map(fn ($item) => ['label' => $item->{$column}, 'total' => (int) $item->total])->values()->all();
    }

    private function schoolUsage(int $limit): array
    {
        return DB::table('users')
            ->leftJoin('quiz_results', 'users.id', '=', 'quiz_results.user_id')
            ->where('users.is_admin', false)
            ->whereNotNull('users.school')
            ->select('users.school', DB::raw('count(quiz_results.id) as total'))
            ->groupBy('users.school')->orderByDesc('total')->limit($limit)->get()
            ->map(fn ($item) => ['label' => $item->school, 'total' => (int) $item->total])->values()->all();
    }
}