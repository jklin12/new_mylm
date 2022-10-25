<?php

use App\Http\Controllers\OltController;
use App\Http\Controllers\OnuController;
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
Route::get('finance/cekRequest', 'App\Http\Controllers\FinanceController@cekRequest')->name('cek-request-doku')->middleware('cek_login');
Route::get('finance/cekRequestList', 'App\Http\Controllers\FinanceController@cekRequestList')->name('cek-request-list')->middleware('cek_login');

Route::get('login', 'App\Http\Controllers\AuthController@index')->name('login');
// Route::get('register', 'App\Http\Controllers\AuthController@register')->name('register');
Route::post('do_login', 'App\Http\Controllers\AuthController@do_login')->name('do_login');
Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::middleware(['cek_login'])->group(function () {
    Route::get('/cust', 'App\Http\Controllers\CustomerController@index')->name('customer-index');
    Route::get('/cust_list', 'App\Http\Controllers\CustomerController@list')->name('customer-list');
    Route::get('/cust_detail/{cust_number}', 'App\Http\Controllers\CustomerController@detail')->name('customer-detail');
    Route::get('/cust_cupkg/{cust_number}', 'App\Http\Controllers\CustomerController@cupkg')->name('customer-cupkg');

    Route::get('/cust_porfoma/{cust_number}', 'App\Http\Controllers\PorfomaController@index')->name('customer-porfoma');
    Route::get('/porfoma_list/{cust_number}', 'App\Http\Controllers\PorfomaController@list')->name('porfoma-list');
    Route::get('/porfoma_detail/{inv_number}', 'App\Http\Controllers\PorfomaController@detail')->name('porfoma-detail');

    Route::get('/qris/generate/{inv_number}', 'App\Http\Controllers\QrisController@generate')->name('qris-generate');
    Route::get('/qris', 'App\Http\Controllers\QrisController@index')->name('qris-index');
    Route::get('/qris/auth', 'App\Http\Controllers\QrisController@auth')->name('qris-auth');
    Route::get('/qris_list', 'App\Http\Controllers\QrisController@list')->name('qris-list');
    Route::get('/qris_cek/{id}', 'App\Http\Controllers\QrisController@cekStatus')->name('qris-cek');


    Route::get('/customer_audit', 'App\Http\Controllers\CustomerController@audit')->name('customer-audit');
    Route::get('/customer_message/{message_id}/{cust_number}', 'App\Http\Controllers\CustomerController@messageForm')->name('customer-message-form');
    Route::post('/customer_message/{message_id}/{cust_number}', 'App\Http\Controllers\CustomerController@sendMessage')->name('customer-message-store');
    
    Route::get('/report_doku', 'App\Http\Controllers\ReportDokuController@index')->name('report-doku');
    Route::get('/report_pengguna', 'App\Http\Controllers\ReportController@penggunaBaru')->name('report-pengguna');
    Route::get('/report_invoice', 'App\Http\Controllers\ReportController@invoice')->name('report-invoice');
    Route::get('/report_porfoma', 'App\Http\Controllers\ReportController@porfoma')->name('report-porfoma');
    Route::get('/report_porfoma_detail', 'App\Http\Controllers\ReportController@porfomaDetail')->name('report-porfoma-detail');
    Route::get('/report_porfoma_list/{date}', 'App\Http\Controllers\ReportController@porfomaList')->name('report-porfoma-list');
    Route::get('/report_spk', 'App\Http\Controllers\ReportController@spk')->name('report-spk');
    Route::get('/report_olt', 'App\Http\Controllers\ReportController@Olt')->name('report-olt');
    
    Route::get('/payment_request', 'App\Http\Controllers\DokuController@paymentRequest')->name('pay-request');
    Route::get('/payment_request_list', 'App\Http\Controllers\DokuController@paymentRequestList')->name('pay-request-list');
    Route::get('/payment_request_detail', 'App\Http\Controllers\DokuController@paymentRequestDetail')->name('pay-request-detail');
    Route::get('/payment/void_request', 'App\Http\Controllers\DokuController@voidRequest')->name('void-request');
    Route::get('/payment/cek_request', 'App\Http\Controllers\DokuController@cekRequest')->name('cek-request');
    Route::get('/payment/update', 'App\Http\Controllers\DokuController@updateRequest')->name('update-request');
    Route::get('/send_invoice', 'App\Http\Controllers\DokuController@sendInvForm')->name('send-invoice-form');
    Route::post('/send_invoice', 'App\Http\Controllers\DokuController@sendInv')->name('send-invoice-store');
 
    Route::get('/mikrotik_cek_status', 'App\Http\Controllers\MikrotikController@cekStatus')->name('cek-status-pppoe');;
    Route::get('/olt_cek_status', 'App\Http\Controllers\MikrotikController@cekOlt')->name('cek-status-olt');;
    Route::get('/mikrotik_test', 'App\Http\Controllers\MikrotikController@test');
   
    Route::resource('bukti_tf', 'App\Http\Controllers\BuktiTfController');
    Route::get('bukti_tf_list', 'App\Http\Controllers\BuktiTfController@list')->name('bukti-tf-list');

    Route::get('/onu/index', [OnuController::class,'index'])->name('onu-index');
    Route::get('/onu_unconfig', [OnuController::class,'unconfig'])->name('onu-uncfg');

    Route::get('/olt', [OltController::class,'index'])->name('olt-index');
    Route::get('/olt_uncfg', [OltController::class,'uncfg'])->name('olt-uncfg');
    Route::get('/olt_register/{step}', [OltController::class,'register'])->name('olt-register');
    Route::post('/ppp_register', [OltController::class,'pppRegister'])->name('ppp-register');
    Route::post('/onu_register', [OltController::class,'onuRegister'])->name('onu-register');
    Route::post('/cek_api', [OltController::class,'api'])->name('olt-api');
});