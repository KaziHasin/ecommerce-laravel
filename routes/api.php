<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

 

// user group routes
Route::controller(AuthController::class)->group(function(){
Route::post("/register", "register");
Route::post("/login", "login");


});

// public Route
Route::get("/products/{id?}", [ProductController::class, "index"]);
Route::post('/resetpasswordmail', [AuthController::class, "send_reset_password_email"]);
Route::post("/resetpassword/{token}", [AuthController::class, "reset_password"]);

  

  // private route
 Route::middleware('auth:sanctum')->group( function () {
// Route::group(['middleware' => ['auth:sanctum']], function(){
Route::post("/products", [ProductController::class, "store"]);
Route::post("/products/{id}", [ProductController::class, "update"]);
Route::delete("/products/{id}", [ProductController::class, "destroy"]);
  
  Route::post("/changepassword", [AuthController::class, "changePassword"]);

Route::post("/logout", [AuthController::class, "logout"]);

});



