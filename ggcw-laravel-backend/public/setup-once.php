<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Ye file 'public' folder mein daal kar browser se ek baar chalani hai,
 * phir TURANT DELETE kar deni hai. Security ke liye pehle neeche wala
 * $secret badal lo (kuch bhi random likh do).
 *
 * Usage:
 *  https://api.ggcwcomplaints.com/setup-once.php?key=YOUR_SECRET&cmd=key:generate
 *  https://api.ggcwcomplaints.com/setup-once.php?key=YOUR_SECRET&cmd=storage:link
 */

$secret = 'CHANGE_ME_9x7Klm2'; // 🔒 Isay pehle badal lo, phir URL mein wahi likhna

if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    exit('Forbidden.');
}

$allowedCommands = [
    'key:generate'  => ['key:generate', ['--force' => true]],
    'storage:link'  => ['storage:link', []],
    'config:clear'  => ['config:clear', []],
    'cache:clear'   => ['cache:clear', []],
    'route:clear'   => ['route:clear', []],
    'view:clear'    => ['view:clear', []],
];

$cmd = $_GET['cmd'] ?? '';

if (!isset($allowedCommands[$cmd])) {
    echo 'Invalid command. Allowed: ' . implode(', ', array_keys($allowedCommands));
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

[$artisanCommand, $params] = $allowedCommands[$cmd];

$exitCode = Illuminate\Support\Facades\Artisan::call($artisanCommand, $params);

echo '<pre style="font-family:monospace; background:#111; color:#0f0; padding:20px;">';
echo "Command: {$artisanCommand}\n";
echo "Exit code: {$exitCode}\n\n";
echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
echo '</pre>';
echo '<p style="font-family:sans-serif; color:red; font-weight:bold;">⚠️ Ab is file (setup-once.php) ko File Manager se TURANT DELETE kar dein.</p>';