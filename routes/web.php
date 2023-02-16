<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/assign_user/{id}',[BlogController::class, 'assign_user']
)->middleware(['auth'])->name('assign_user');

Route::get('/blog_list',[BlogController::class,'blog_list'])->middleware(['auth'])->name('blog_list');
Route::post('assign_user_post/{id}',[BlogController::class,'assign_user_post'])->middleware(['auth'])->name('assign_user_post');

Route::resource('blog',BlogController::class)->middleware(['auth']);
require __DIR__.'/auth.php';
