<?php

use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseController;
use App\Models\MstProcessProductions;

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

//Route Login NON SSO
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('auth/login', [AuthController::class, 'postlogin'])->name('postlogin')->middleware("throttle:5,2");
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::group(['middleware' => ['clear.permission.cache', 'permission:Purchasing|Purchasing_Requisition|Purchasing_Item|Purchasing_Order']], function () {

        //Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        //Purchase
        Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase');
        Route::get('/purchase-requisition-cari/{request_number}', [PurchaseController::class, 'purchase_requisition_cari'])->name('purchase_requisition_cari');
        Route::get('/purchase-order', [PurchaseController::class, 'purchase_order'])->name('purchase_order');
        Route::get('/purchase-requisition-items', [PurchaseController::class, 'purchase_requisition'])->name('purchase_requisition');
        Route::get('/hapus_request_number', [PurchaseController::class, 'hapus_request_number'])->name('hapus_request_number');
        Route::get('/hapus_request_number_wip', [PurchaseController::class, 'hapus_request_number_wip'])->name('hapus_request_number_wip');
        Route::get('/hapus_request_number_fg', [PurchaseController::class, 'hapus_request_number_fg'])->name('hapus_request_number_fg');
        Route::get('/hapus_request_number_ta', [PurchaseController::class, 'hapus_request_number_ta'])->name('hapus_request_number_ta');
        Route::get('/hapus_request_number_other', [PurchaseController::class, 'hapus_request_number_other'])->name('hapus_request_number_other');
        Route::get('/add-pr-rm', [PurchaseController::class, 'tambah_pr_rm'])->name('tambah_pr_rm');
        Route::get('/detail-pr/{request_number}', [PurchaseController::class, 'detail_pr'])->name('detail_pr');
        Route::get('/add-pr-wip', [PurchaseController::class, 'tambah_pr_wip'])->name('tambah_pr_wip');
        Route::get('/detail-pr-wip/{request_number}', [PurchaseController::class, 'detail_pr_wip'])->name('detail_pr_wip');
        Route::get('/add-pr-fg', [PurchaseController::class, 'tambah_pr_fg'])->name('tambah_pr_fg');
        Route::get('/detail-pr-fg/{request_number}', [PurchaseController::class, 'detail_pr_fg'])->name('detail_pr_fg');
        Route::get('/add-pr-sparepart', [PurchaseController::class, 'tambah_pr_sparepart'])->name('tambah_pr_sparepart');
        Route::get('/detail-pr-sparepart/{request_number}', [PurchaseController::class, 'detail_pr_sparepart'])->name('detail_pr_sparepart');
        Route::get('/add-pr-other', [PurchaseController::class, 'tambah_pr_other'])->name('tambah_pr_other');
        Route::get('/detail-pr-other/{request_number}', [PurchaseController::class, 'detail_pr_other'])->name('detail_pr_other');
        Route::get('/get-supplier', [PurchaseController::class, 'get_supplier'])->name('get_supplier');
        Route::get('/get-unit', [PurchaseController::class, 'get_unit'])->name('get_unit');
        Route::post('/simpan_po', [PurchaseController::class, 'simpan_po'])->name('simpan_po');
        Route::get('/detail-po/{reference_number}/{id}', [PurchaseController::class, 'detail_po'])->name('detail_po');
        Route::post('/simpan_pr_rm', [PurchaseController::class, 'simpan_pr_rm'])->name('simpan_pr_rm');
        Route::post('/simpan_detail_rm/{request_number}', [PurchaseController::class, 'simpan_detail_rm'])->name('simpan_detail_rm');
        Route::post('/update_detail_rm/{request_number}/{id}', [PurchaseController::class, 'update_detail_rm'])->name('update_detail_rm');
        Route::post('/simpan_pr_wip', [PurchaseController::class, 'simpan_pr_wip'])->name('simpan_pr_wip');
        Route::post('/simpan_detail_wip/{request_number}', [PurchaseController::class, 'simpan_detail_wip'])->name('simpan_detail_rm');
        Route::post('/simpan_pr_fg', [PurchaseController::class, 'simpan_pr_fg'])->name('simpan_pr_fg');
        Route::post('/simpan_detail_fg/{request_number}', [PurchaseController::class, 'simpan_detail_fg'])->name('simpan_detail_fg');
        Route::post('/simpan_pr_ta', [PurchaseController::class, 'simpan_pr_ta'])->name('simpan_pr_ta');
        Route::post('/simpan_detail_ta/{request_number}', [PurchaseController::class, 'simpan_detail_ta'])->name('simpan_detail_ta');
        Route::post('/simpan_pr_other', [PurchaseController::class, 'simpan_pr_other'])->name('simpan_pr_other');
        Route::post('/simpan_detail_other/{request_number}', [PurchaseController::class, 'simpan_detail_other'])->name('simpan_detail_other');
        Route::get('/generate-code', [PurchaseController::class, 'generateCode'])->name('generateCode');
        Route::delete('/hapus_po/{id}', [PurchaseController::class, 'hapus_po'])->name('hapus_po');
        Route::delete('/hapus_po_detail/{id}/{idx}', [PurchaseController::class, 'hapus_po_detail'])->name('hapus_po_detail');
        Route::get('/get-edit-po/{id}', [PurchaseController::class, 'get_edit_po'])->name('get_edit_po');
        Route::get('/get-edit-po-smt/{id}', [PurchaseController::class, 'get_edit_po_smt'])->name('get_edit_po_smt');
        Route::delete('/hapus_pr/{request_number}', [PurchaseController::class, 'hapus_pr'])->name('hapus_pr');
        Route::delete('/hapus_pr_detail/{id}/{request_number}', [PurchaseController::class, 'hapus_pr_detail'])->name('hapus_pr_detail');
        Route::get('/edit-pr/{request_number}', [PurchaseController::class, 'edit_pr'])->name('edit_pr');
        Route::put('/update_pr/{request_number}', [PurchaseController::class, 'update_pr'])->name('update_pr');
        Route::get('/get-edit-pr/{id}', [PurchaseController::class, 'get_edit_pr'])->name('get_edit_pr');
        Route::put('/posted_pr/{request_number}', [PurchaseController::class, 'posted_pr'])->name('posted_pr');
        Route::put('/unposted_pr/{request_number}', [PurchaseController::class, 'unposted_pr'])->name('unposted_pr');
        Route::get('/edit-po/{id}', [PurchaseController::class, 'edit_po'])->name('edit_po');


        Route::controller(PurchaseController::class)->group(function () {
            Route::prefix('purchase_requisition')->group(function () {
                //DATA PR
                Route::get('/', 'indexPR')->name('pr.index');
                Route::get('/add/{type}', 'addPR')->name('pr.add');
                Route::get('/edit/{id}', 'editPR')->name('pr.edit');
                Route::post('/store', 'storePR')->name('pr.store');
                Route::post('/update/{id}', 'updatePR')->name('pr.update');
                Route::post('/delete/{id}', 'deletePR')->name('pr.delete');
                Route::post('/posted/{id}', 'postedPR')->name('pr.posted');
                Route::post('/unposted/{id}', 'unpostedPR')->name('pr.unposted');
                //ITEM PR
                Route::get('/item/edit/{id}', 'editItemPR')->name('pr.editItem');
                Route::post('/item/store/{id}', 'storeItemPR')->name('pr.storeItem');
                Route::post('/item/update/{id}', 'updateItemPR')->name('pr.updateItem');
                Route::post('/item/delete/{id}', 'deleteItemPR')->name('pr.deleteItem');
            });
        });

        Route::controller(PurchaseController::class)->group(function () {
            Route::prefix('purchase_orders')->group(function () {
                Route::get('/', 'indexPO')->name('po.index');
                Route::post('/update/{id}', 'updatePO')->name('updatePO');
                Route::post('item/add/{id}', 'addItemPO')->name('addItemPO');
                Route::post('item/update/{id}', 'updateItemPO')->name('updateItemPO');
                Route::post('item/delete/{id}', 'deleteItemPO')->name('deleteItemPO');
            });
        });

        // Route::post('/update-po/{id}', [PurchaseController::class, 'updatePO'])->name('updatePO');
        // Route::post('/add-item-po/{id}', [PurchaseController::class, 'addItemPO'])->name('addItemPO');
        // Route::post('/update-item-po/{id}', [PurchaseController::class, 'updateItemPO'])->name('updateItemPO');
        // Route::post('/delete-item-po/{id}', [PurchaseController::class, 'deleteItemPO'])->name('deleteItemPO');

        Route::get('/edit-po-item/{id}', [PurchaseController::class, 'edit_po_item'])->name('edit_po_item');
        Route::get('/edit-po-item-smt/{id}', [PurchaseController::class, 'edit_po_item_smt'])->name('edit_po_item_smt');
        Route::get('/tambah_detail_po/{reference_number}/{id}', [PurchaseController::class, 'tambah_detail_po'])->name('tambah_detail_po');
        Route::post('/simpan_detail_po/{reference_number}/{id}', [PurchaseController::class, 'simpan_detail_po'])->name('simpan_detail_po');
        Route::post('/simpan_detail_po_fix/{id}/{reference_number}', [PurchaseController::class, 'simpan_detail_po_fix'])->name('simpan_detail_po_fix');
        Route::put('/posted_po/{id}', [PurchaseController::class, 'posted_po'])->name('posted_po');
        Route::put('/unposted_po/{id}', [PurchaseController::class, 'unposted_po'])->name('unposted_po');
        Route::put('/update_po/{id}', [PurchaseController::class, 'update_po'])->name('update_po');
        Route::post('/update_detail_po/{id}', [PurchaseController::class, 'update_detail_po'])->name('update_detail_po');
        Route::post('/update_detail_po_item/{id}', [PurchaseController::class, 'update_detail_po_item'])->name('update_detail_po_item');
        Route::put('/update_po_detail/{id}', [PurchaseController::class, 'update_po_detail'])->name('update_po_detail');
        Route::put('/update_po_detail_smt/{id}', [PurchaseController::class, 'update_po_detail_smt'])->name('update_po_detail_smt');
        Route::put('/update_pr_detailx/{id}', [PurchaseController::class, 'update_pr_detailx'])->name('update_pr_detailx');
        Route::put('/update_pr_detail_editx/{id}', [PurchaseController::class, 'update_pr_detail_editx'])->name('update_pr_detail_editx');
        Route::get('/print-po/{id}', [PurchaseController::class, 'print_po'])->name('print_po');
        Route::get('/print-po-ind/{id}', [PurchaseController::class, 'print_po_ind'])->name('print_po_ind');
        Route::get('/print-pr/{request_number}', [PurchaseController::class, 'print_pr'])->name('print_pr');
        Route::get('/print-pr-ind/{request_number}', [PurchaseController::class, 'print_pr_ind'])->name('print_pr_ind');
    });
});
