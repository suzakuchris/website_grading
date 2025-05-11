<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;

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

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('login');
});

Route::get('/init', [AuthController::class, 'init']);

Route::group(['middleware' => 'guest'], function(){
    Route::group(['prefix' => 'login'], function(){
        Route::get('/', [AuthController::class, 'login'])->name('login');
        Route::post('/', [AuthController::class, 'login_process'])->name('login.post');
    });
});

Route::group(['middleware' => 'auth'], function(){
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::group(['prefix' => 'dashboard'], function(){
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

    Route::group(['prefix' => 'config'], function(){
        Route::get('/', [ConfigController::class, 'index'])->name('config.main');
        Route::post('/save', [ConfigController::class, 'save'])->name('config.main.save');
    });

    Route::group(['prefix' => 'master'], function(){
        Route::group(['prefix' => 'items'], function(){
            Route::get('/', [ItemController::class, 'index'])->name('master.item');
            Route::post('/search', [ItemController::class, 'search'])->name('master.item.search');
            Route::post('/view', [ItemController::class, 'view'])->name('master.item.view');
            Route::post('/upsert', [ItemController::class, 'upsert'])->name('master.item.upsert');
            Route::post('/delete', [ItemController::class, 'delete'])->name('master.item.delete');
        });

        Route::group(['prefix' => 'rekening'], function(){
            Route::get('/', [RekeningController::class, 'index'])->name('master.rekening');
            Route::post('/search', [RekeningController::class, 'search'])->name('master.rekening.search');
            Route::post('/view', [RekeningController::class, 'view'])->name('master.rekening.view');
            Route::post('/upsert', [RekeningController::class, 'upsert'])->name('master.rekening.upsert');
            Route::post('/delete', [RekeningController::class, 'delete'])->name('master.rekening.delete');
        });

        Route::group(['prefix' => 'customer'], function(){
            Route::get('/', [CustomerController::class, 'index'])->name('master.customer');
            Route::post('/search', [CustomerController::class, 'search'])->name('master.customer.search');

            Route::get('/view/{customer_id?}', [CustomerController::class, 'view'])->name('master.customer.view');
            Route::get('/edit/{customer_id?}', [CustomerController::class, 'edit'])->name('master.customer.edit');
            Route::get('/add', [CustomerController::class, 'add'])->name('master.customer.add');

            Route::post('/upsert', [CustomerController::class, 'upsert'])->name('master.customer.upsert');
            Route::post('/delete', [CustomerController::class, 'delete'])->name('master.customer.delete');
        });

        Route::group(['prefix' => 'users'], function(){
            Route::get('/', [UsersController::class, 'index'])->name('master.users');
            Route::post('/search', [UsersController::class, 'search'])->name('master.users.search');
            Route::post('/view', [UsersController::class, 'view'])->name('master.users.view');
            Route::post('/upsert', [UsersController::class, 'upsert'])->name('master.users.upsert');
            Route::post('/delete', [UsersController::class, 'delete'])->name('master.users.delete');
        });

        Route::group(['prefix' => 'company'], function(){
            Route::get('/', [CompanyController::class, 'index'])->name('master.company');
            Route::post('/search', [CompanyController::class, 'search'])->name('master.company.search');
            Route::post('/delete', [CompanyController::class, 'delete'])->name('master.company.delete');

            Route::get('/add', [CompanyController::class, 'add'])->name('master.company.add');
            Route::get('/view/{id?}', [CompanyController::class, 'view'])->name('master.company.view');
            Route::post('/save', [CompanyController::class, 'upsert'])->name('master.company.upsert');
            
            Route::get('/detail/add/{id}', [CompanyController::class, 'detail_add'])->name('master.company.detail.add');
            Route::get('/detail/{detail_id?}', [CompanyController::class, 'detail_view'])->name('master.company.detail.view');
            Route::post('/detail_save', [CompanyController::class, 'detail_upsert'])->name('master.company.details.upsert');
        });
    });

    Route::group(['prefix' => 'transaction'], function(){
        Route::get('/', [TransactionController::class, 'index'])->name('transaction');
        Route::post('/search', [TransactionController::class, 'search'])->name('transaction.search');

        Route::post('/delete', [TransactionController::class, 'delete'])->name('transaction.delete');
        Route::post('/get-tier', [TransactionController::class, 'company_details'])->name('transaction.tier');
        
        Route::get('/add', [TransactionController::class, 'add'])->name('transaction.add');
        Route::get('/view/{header_id?}', [TransactionController::class, 'view'])->name('transaction.view');
        Route::post('/upsert', [TransactionController::class, 'save'])->name('transaction.save');
        Route::post('/payment_attachment', [TransactionController::class, 'search_payment_attachment'])->name('transaction.payment.attachment');

        Route::group(['prefix' => '{header_id}/payment'], function(){
            Route::get('/add', [TransactionController::class, 'add_payment'])->name('transaction.payment.add');
            Route::get('/view/{payment_id}', [TransactionController::class, 'view_payment'])->name('transaction.payment.view');
            Route::post('/upsert', [TransactionController::class, 'save_payment'])->name('transaction.payment.save');
            Route::post('/delete', [TransactionController::class, 'delete_payment'])->name('transaction.payment.delete');
        });
    });
});
