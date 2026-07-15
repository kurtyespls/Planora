<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

$hotels = App\Models\Hotel::all();
foreach ($hotels as $h) {
    echo "{$h->name} | lat={$h->lat} | lon={$h->lon} | addr={$h->address}\n";
}