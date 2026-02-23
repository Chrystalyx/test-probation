<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = [
    'SALES' => \App\Models\Sales::all()->toArray(),
    'PURCHASES' => \App\Models\Purchases::all()->toArray(),
    'USERS' => \App\Models\Users::all()->toArray()
];

file_put_contents('output.json', json_encode($data, JSON_PRETTY_PRINT));
