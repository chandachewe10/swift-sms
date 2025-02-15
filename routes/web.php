<?php

use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\feedbackController;
use App\Http\Controllers\newsLetterSubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn () => view('welcome'));

Route::redirect('/login', '/app/login')->name('login');

Route::redirect('/register', '/app/register')->name('register');

Route::redirect('/dashboard', '/app')->name('dashboard');

Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
    ->middleware(['signed', 'verified', 'auth', AuthenticateSession::class])
    ->name('team-invitations.accept');



## API Documentations

Route::get('/api_docs', function () {
    return view('docs.api');
})
->name('api_docs');



## Terms and Conditions

Route::get('/terms_and_conditions', function () {
    return view('terms');
})
->name('terms_and_conditions');


## Privacy Policy

Route::get('/privacy_and_policy', function () {
    return view('policy');
})
->name('privacy_and_policy');


## Contact Us Controller
 
Route::resource('contact-us', ContactUsController::class);

## Feedback Controller
 
Route::resource('feedback', feedbackController::class);

## NewsLetter Controller
 
Route::resource('news-letter', newsLetterSubscriptionController::class);


