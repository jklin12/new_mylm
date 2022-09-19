<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\DashboardController@index')->name('/')->middleware('cek_login');
Route::get('finance/generateInv', 'App\Http\Controllers\FinanceController@index')->name('generateInv')->middleware('cek_login');
Route::post('finance/importStatement', 'App\Http\Controllers\FinanceController@importStatement')->name('import-statement')->middleware('cek_login');

Route::get('login', 'App\Http\Controllers\AuthController@index')->name('login');
// Route::get('register', 'App\Http\Controllers\AuthController@register')->name('register');
Route::post('do_login', 'App\Http\Controllers\AuthController@do_login')->name('do_login');
Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::middleware(['cek_login'])->group(function () {
    Route::get('/cust', 'App\Http\Controllers\CustomerController@index')->name('customer-index');
    Route::get('/cust_list', 'App\Http\Controllers\CustomerController@list')->name('customer-list');

    Route::get('/cust_detail', 'App\Http\Controllers\CustomerController@detail')->name('customer-detail');
    Route::get('/cust_cupkg', 'App\Http\Controllers\CustomerController@cupkg')->name('customer-cupkg');
    Route::get('/cust_porfoma', 'App\Http\Controllers\CustomerController@porfoma')->name('customer-porfoma');
    Route::get('/customer_audit', 'App\Http\Controllers\CustomerController@audit')->name('customer-audit');
    
    Route::get('/report_doku', 'App\Http\Controllers\ReportDokuController@index')->name('report-doku');
    Route::get('/report_pengguna', 'App\Http\Controllers\ReportController@penggunaBaru')->name('report-pengguna');
    Route::get('/report_porfoma', 'App\Http\Controllers\ReportController@porfoma')->name('report-porfoma');
    Route::get('/report_spk', 'App\Http\Controllers\ReportController@spk')->name('report-spk');
    
    Route::get('/payment_request', 'App\Http\Controllers\DokuController@paymentRequest')->name('pay-request');
    Route::get('/payment_request_list', 'App\Http\Controllers\DokuController@paymentRequestList')->name('pay-request-list');
    Route::get('/payment_request_detail', 'App\Http\Controllers\DokuController@paymentRequestDetail')->name('pay-request-detail');
    Route::get('/payment/void_request', 'App\Http\Controllers\DokuController@voidRequest')->name('void-request');
    Route::get('/payment/cek_request', 'App\Http\Controllers\DokuController@cekRequest')->name('cek-request');
    Route::get('/payment/update', 'App\Http\Controllers\DokuController@updateRequest')->name('update-request');
 
    Route::get('/mikrotik_cek_status', 'App\Http\Controllers\MikrotikController@cekStatus')->name('cek-status-pppoe');;
   
});