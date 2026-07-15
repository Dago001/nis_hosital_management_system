<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/services', function () {
    return view('services');
});

Route::get('/support-chats', function () {
    return view('support-chats');
});

Route::get('/patients', function () {
    return view('patients');
});

Route::get('/appointments', function () {
    return view('appointments');
});

Route::get('/vitals', function () {
    return view('vitals');
});

Route::get('/consultations', function () {
    return view('consultations');
});

Route::get('/laboratory', function () {
    return view('laboratory');
});

Route::get('/pharmacy', function () {
    return view('pharmacy');
});

Route::get('/billing', function () {
    return view('billing');
});

Route::get('/audit-trail', function () {
    return view('audit-trail');
});

Route::get('/admin/users', function () {
    return view('users');
});

Route::get('/settings', function () {
    return view('settings');
});

// New Feature Web Routes
Route::get('/queue', function () { return view('queue'); });
Route::get('/ipd', function () { return view('ipd'); });
Route::get('/reports', function () { return view('reports'); });
Route::get('/referrals', function () { return view('referrals'); });
Route::get('/emergencies', function () { return view('emergency'); });

