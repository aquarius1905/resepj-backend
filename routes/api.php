<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopRepresentativeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ShopAuthController;
use App\Http\Controllers\Auth\UserAuthController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::prefix('users')->group(function () {
  //ユーザー登録
  Route::post('/', [UserController::class, 'store']);
  // ユーザーログイン
  Route::post('/login', [UserAuthController::class, 'store'])->name('user.login');
  Route::middleware('auth:web')->group(function () {
    //ユーザー情報取得
    Route::get('/me', [UserController::class, 'me']);
    // ユーザーログアウト
    Route::post('/logout', [UserAuthController::class, 'destroy'])->name('user.logout');
  });
});

// store: お気に入り登録
// delete: お気に入り削除
Route::middleware('auth:web')->apiResource('/likes', LikeController::class)->only([
  'store', 'destroy'
]);
// store: 予約登録
// update: 予約更新
// update: 予約削除
Route::apiResource('/reservations', ReservationController::class)->only([
  'store', 'update', 'delete'
]);
// show: 評価取得
// store: 評価登録
Route::apiResource('/reservations/{reservation}/ratings', RatingController::class)->only([
  'show', 'store'
]);

Route::prefix('shop-represetatives')->group(function () {
  // store: 店舗代表者登録
  Route::apiResource('/', ShopRepresentativeController::class)->only([
    'store'
  ]);
});


// カード決済
Route::apiResource('/payments', PaymentController::class)->only([
  'store'
]);
  // index：飲食店一覧取得
  // show: 飲食店詳細取得
  // store：飲食店情報登録
  // update: 飲食店情報更新
  Route::apiResource('/shops', ShopController::class)->only([
    'index', 'show', 'store', 'update'
  ]);
Route::prefix('/shops')->group(function () {
  // search: 飲食店検索
  Route::get('search', [ShopController::class, 'search'])->name('shops.search');
  // 店舗代表者ログイン
  Route::post('/login', [ShopAuthController::class, 'store'])->name('shop.login');
  Route::middleware('auth:shop')->group(function () {
    // 店舗代表者ログアウト
    Route::post('/logout', [ShopAuthController::class, 'destroy'])->name('shop.logout');
  });
});
Route::prefix('admins')->group(function () {
  // 管理者ログイン
  Route::post('/login', [AdminAuthController::class, 'store'])->name('admin.login');
  Route::middleware('auth:admin')->group(function () {
    // 管理者ログアウト
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
  });
});