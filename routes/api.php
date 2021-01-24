<?php

use App\Http\Controllers\BookStoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/listBookStore/opentime', [BookStoreController::class, 'listBookStoreOpenTime']);
Route::get('/listBookStore/opentime/dayOfWeek', [BookStoreController::class, 'listBookStoreDayOfWeek']);
Route::get('/listBookStore/opentime/filterByTotalTime', [BookStoreController::class, 'ListBookStoreFilterByTotalTime']);
Route::get('/listBooks', [BookStoreController::class, 'listBooks']);
// Route::get('/listBookStore/numberOfBook', [BookStoreController::class, 'listBookStoreNumberOfBook']);
Route::get('/listBookStore/numberOfBook/price', [BookStoreController::class, 'listBookStoreFilterBooksAndPrice']);
Route::get('/searchBookAndBookStore', [BookStoreController::class, 'searchBookAndBookStore']);
Route::get('/userList/ByHighestTransactionAmount', [UserController::class, 'userListByHighestTransactionAmount']);
Route::get('/totalTransactionAmount', [UserController::class, 'totalTransactionAmount']);
Route::get('/popularStore', [UserController::class, 'popularStore']);
Route::get('/numberOfUserByTransaction', [UserController::class, 'numberOfUserByTransaction']);
Route::post('/buyBook', [UserController::class, 'buyBook']);
Route::put('/updateUserBookData', [UserController::class, 'updateUserBookData']);
