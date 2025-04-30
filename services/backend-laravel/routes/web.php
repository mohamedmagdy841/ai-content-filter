<?php

use App\Jobs\TestRabbitJob;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-rabbit', function () {
    dispatch(new TestRabbitJob());
    return 'Job dispatched!';
});
