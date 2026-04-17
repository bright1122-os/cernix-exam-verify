<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $database = 'connected';
        } catch (\Throwable) {
            $database = 'error';
        }

        $sessionActive = false;
        try {
            $sessionActive = DB::table('exam_sessions')->where('is_active', true)->exists();
        } catch (\Throwable) {
            // table may not exist in a fresh install
        }

        return response()->json([
            'status'         => $database === 'connected' ? 'ok' : 'degraded',
            'database'       => $database,
            'session_active' => $sessionActive,
            'timestamp'      => now()->toIso8601String(),
        ]);
    }
}
