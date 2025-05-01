<?php

use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test-email', function () {
    Mail::to('kyalograce2024@gmail.com')->send(new WelcomeEmail());
    return response()->json(['message' => 'Email sent!']);
});
