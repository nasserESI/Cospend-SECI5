<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

//only for testing purposes
Route::get('/group/{groupId}/members', [Controller::class, 'members']);

//only for testing purposes
Route::get('/group/{groupId}/owner', [Controller::class, 'owner']);

//used for testing purposes only
Route::get('/groups/{group}/balances', [BalanceController::class, 'showBalance'])->name('groups.balances');

Route::group(['middleware' => ['XssSanitization', 'throttle:10|60,1']], function () {

Route::resource('groups', GroupController::class)->except([
    'index', 'create', 'edit', 'update'
]);

Route::resource('balances', BalanceController::class)->except([
    'index', 'create', 'edit', 'update','store','show','destroy'
]);


Route::resource('expenses', ExpenseController::class)->except([
    'index', 'create', 'edit','show'
]);


Route::post('/groups/{group}/add-user', [GroupController::class, 'addUser'])->name('groups.addUser');


Route::delete('/groups/{group}/remove-user/{user}', [GroupController::class, 'removeUser'])->name('groups.removeUser');

Route::get('/groups/{group}/expenses', [ExpenseController::class, 'showGroupExpenses'])->name('groups.expenses');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Auth::routes();

Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});





