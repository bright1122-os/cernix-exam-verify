<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\ExaminerAuthController;
use App\Http\Controllers\Web\ExaminerWebController;
use App\Http\Controllers\Web\StudentAuthController;
use App\Http\Controllers\Web\StudentDashboardController;
use App\Http\Controllers\Web\StudentWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));
Route::get('/health', [HealthController::class, 'check']);

// Student portal
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login']);
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');
    Route::get('/register', [StudentWebController::class, 'index'])->name('register');
    Route::post('/register', [StudentWebController::class, 'register']);
    Route::middleware('student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/exam-pass/print', [StudentDashboardController::class, 'printPass'])->name('pass.print');
    });
});

// Examiner portal
Route::prefix('examiner')->name('examiner.')->group(function () {
    Route::get('/login', [ExaminerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ExaminerAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [ExaminerAuthController::class, 'logout'])->name('logout');
    Route::middleware('examiner')->group(function () {
        Route::get('/dashboard', [ExaminerWebController::class, 'index'])->name('dashboard');
        Route::post('/verify', [ExaminerWebController::class, 'verify'])->name('verify');
    });
});

// Admin portal
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminWebController::class, 'index'])->name('dashboard');
        Route::get('/sessions', [AdminWebController::class, 'sessions'])->name('sessions.index');
        Route::get('/sessions/{session}', [AdminWebController::class, 'showSession'])->name('sessions.show');
        Route::post('/sessions', [AdminWebController::class, 'storeSession'])->name('sessions.store');
        Route::post('/sessions/{session}/close', [AdminWebController::class, 'closeSession'])->name('sessions.close');
        Route::delete('/sessions/{session}', [AdminWebController::class, 'deleteSession'])->name('sessions.delete');
        Route::get('/examiners', [AdminWebController::class, 'examiners'])->name('examiners.index');
        Route::get('/examiners/{examiner}', [AdminWebController::class, 'showExaminer'])->name('examiners.show');
        Route::post('/examiners', [AdminWebController::class, 'storeExaminer'])->name('examiners.store');
        Route::post('/examiners/{examiner}/toggle', [AdminWebController::class, 'toggleExaminer'])->name('examiners.toggle');
        Route::delete('/examiners/{examiner}', [AdminWebController::class, 'deleteExaminer'])->name('examiners.delete');
        Route::get('/students', [AdminWebController::class, 'students'])->name('students.index');
        Route::get('/students/{student}', [AdminWebController::class, 'showStudent'])->where('student', '.*')->name('students.show');
        Route::delete('/students/{student}', [AdminWebController::class, 'deleteStudent'])->where('student', '.*')->name('students.delete');
        Route::get('/timetables', [AdminWebController::class, 'timetables'])->name('timetables.index');
        Route::post('/timetables', [AdminWebController::class, 'storeTimetable'])->name('timetables.store');
        Route::get('/timetables/{timetable}/edit', [AdminWebController::class, 'editTimetable'])->name('timetables.edit');
        Route::put('/timetables/{timetable}', [AdminWebController::class, 'updateTimetable'])->name('timetables.update');
        Route::delete('/timetables/{timetable}', [AdminWebController::class, 'deleteTimetable'])->name('timetables.delete');
        Route::get('/payments', [AdminWebController::class, 'payments'])->name('payments.index');
        Route::get('/scan-logs', [AdminWebController::class, 'scanLogs'])->name('scan-logs.index');
        Route::get('/scan-logs/export', [AdminWebController::class, 'exportScanLogs'])->name('scan-logs.export');
        Route::get('/scan-logs/{log}', [AdminWebController::class, 'showScanLog'])->whereNumber('log')->name('scan-logs.show');
        Route::get('/activity', [AdminWebController::class, 'activity'])->name('activity.index');
        Route::get('/settings', [AdminWebController::class, 'settings'])->name('settings.index');
        Route::post('/settings', [AdminWebController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/clear-scan-logs', [AdminWebController::class, 'clearScanLogs'])->name('settings.clear-scan-logs');
        Route::post('/settings/reset-system', [AdminWebController::class, 'resetSystem'])->name('settings.reset-system');
    });
});

// Passport photo thumbnails — resize + disk cache (GD)
Route::get('/photo-thumb/{name}', function (string $name) {
    $name = basename($name);
    if (! preg_match('/^[\w\-]+\.jpe?g$/i', $name)) {
        abort(404);
    }

    $srcPath = public_path('photos/' . $name);
    if (! file_exists($srcPath)) {
        abort(404);
    }

    $thumbDir  = storage_path('app/thumbs');
    $thumbPath = $thumbDir . '/' . $name;

    if (! is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Serve disk-cached thumb if source is unchanged
    if (file_exists($thumbPath) && filemtime($thumbPath) >= filemtime($srcPath)) {
        return response()->file($thumbPath, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    // Resize to 224×280 max (2× retina at 112×140 display size)
    $src = @imagecreatefromjpeg($srcPath);
    if (! $src) {
        abort(500);
    }
    [$ow, $oh] = [imagesx($src), imagesy($src)];
    $ratio = min(224 / $ow, 280 / $oh);
    $nw    = (int) round($ow * $ratio);
    $nh    = (int) round($oh * $ratio);

    $dst = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
    imagedestroy($src);

    ob_start();
    imagejpeg($dst, null, 82);
    imagedestroy($dst);
    $jpeg = ob_get_clean();

    file_put_contents($thumbPath, $jpeg);

    return response($jpeg, 200, [
        'Content-Type'  => 'image/jpeg',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('name', '[^/]+');

// Documentation (replaces presentation)
Route::get('/documentation', fn () => view('documentation'));
Route::get('/presentation',  fn () => view('landing'));
