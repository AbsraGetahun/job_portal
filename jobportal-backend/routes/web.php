<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return redirect('/career/register');
})->name('register');

// ============================================
// DEBUG ROUTES - Remove after fixing!
// ============================================

Route::get('/debug-env', function() {
    return response()->json([
        'APP_ENV' => env('APP_ENV', 'NOT SET'),
        'APP_DEBUG' => env('APP_DEBUG', 'NOT SET'),
        'APP_KEY' => env('APP_KEY') ? 'SET (length: ' . strlen(env('APP_KEY')) . ')' : 'NOT SET',
        'DB_CONNECTION' => env('DB_CONNECTION', 'NOT SET'),
        'DB_HOST' => env('DB_HOST', 'NOT SET'),
        'DB_PORT' => env('DB_PORT', 'NOT SET'),
        'DB_DATABASE' => env('DB_DATABASE', 'NOT SET'),
        'DB_USERNAME' => env('DB_USERNAME', 'NOT SET'),
        'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET',
        'DB_SSL_CA' => env('DB_SSL_CA', 'NOT SET'),
    ]);
});

Route::get('/debug-log', function() {
    try {
        Log::info('Debug log test - ' . now());
        return response()->json(['message' => 'Log written successfully! Check storage/logs/laravel.log']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/debug-db', function() {
    try {
        $pdo = DB::connection()->getPdo();
        return response()->json([
            'message' => 'Database connected successfully!',
            'db_name' => $pdo->query('SELECT DATABASE()')->fetchColumn(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
});

Route::get('/debug-phpinfo', function() {
    phpinfo();
});