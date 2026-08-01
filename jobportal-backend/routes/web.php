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

// ============================================
// NEW DEBUG ROUTES - Check .env and logs
// ============================================

Route::get('/check-env', function() {
    $envPath = base_path('.env');
    if (file_exists($envPath)) {
        return '✅ .env file exists at: ' . $envPath . '<br><br>Content:<br><pre>' . htmlspecialchars(file_get_contents($envPath)) . '</pre>';
    } else {
        return '❌ .env file NOT found at: ' . $envPath;
    }
});

Route::get('/check-log', function() {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -50); // Get last 50 lines
        return '<h3>Last 50 lines of laravel.log:</h3><pre>' . htmlspecialchars(implode("\n", $lastLines)) . '</pre>';
    } else {
        return '❌ No log file found at: ' . $logPath;
    }
});

Route::get('/check-storage', function() {
    $storagePath = storage_path();
    $files = scandir($storagePath);
    return '<h3>Storage directory contents:</h3><pre>' . print_r($files, true) . '</pre>';
});

Route::get('/ping', function() {
    return 'pong';
});