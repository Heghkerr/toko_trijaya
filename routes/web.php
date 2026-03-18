<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesChartController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductColorController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\WhatsappOrderController;
use App\Http\Controllers\PushSubscriptionController;



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

// PWA Routes
Route::get('/offline', function () {
    return view('offline');
});

// Manifest route - handle baik dari root maupun subfolder
// Manifest
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'), [
        'Content-Type' => 'application/manifest+json'
    ]);
});

// Service Worker
Route::get('/service-worker.js', function () {
    // Backward compatible: serve actual file name in /public
    return response()->file(public_path('serviceworker.js'), [
        'Content-Type' => 'application/javascript'
    ]);
});

// Service Worker (actual filename used by layout)
Route::get('/serviceworker.js', function () {
    return response()->file(public_path('serviceworker.js'), [
        'Content-Type' => 'application/javascript'
    ]);
});



Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:web')->name('dashboard');
Route::get('/home', function () { return view('home'); })->name('home');
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
route::post('/logout', [AuthController::class, 'logout'])->name('logout');


#reports
Route::get('/reports/x', [ReportController::class, 'xReport'])->name('reports.x');
    Route::get('/reports/z', [ReportController::class, 'zReport'])->name('reports.z');
    Route::get('/reports/download', [ReportController::class, 'showDownloadForm'])->name('reports.download.form');
    Route::post('/reports/download', [ReportController::class, 'downloadReport'])->name('reports.download');
    Route::get('/reports/daily', [ReportController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/daily/transaction/{id}', [ReportController::class, 'showDailyTransaction'])->name('reports.daily.transaction');


#Transaksi
Route::resource('transactions', TransactionController::class);
Route::post('/transactions/add-fund', [TransactionController::class, 'addFund'])->name('transactions.addFund');
Route::put('/transactions/{id}/mark-paid', [TransactionController::class, 'markPaid'])->name('transactions.markPaid');
Route::put('/transactions/{id}/mark-sent', [TransactionController::class, 'markSent'])->name('transactions.markSent');
Route::put('/transactions/{id}/mark-finished', [TransactionController::class, 'markFinished'])->name('transactions.markFinished');
Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
     ->name('transactions.receipt');

#refund
Route::get('/transactions/{id}/refund', [TransactionController::class, 'refund'])->name('transactions.refund');
Route::post('/transactions/{id}/refund', [TransactionController::class, 'refundStore'])->name('transactions.refund.store');
Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
Route::get('refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');

#Product Type
Route::post('/product-types', [ProductTypeController::class, 'store'])->name('product-types.store');


#Cashflow

Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cashflow.index');
Route::post('/cash-flow/transfer', [CashFlowController::class, 'transfer'])->name('cashflow.transfer');

// Whatsapp Orders
Route::get('/whatsapp/orders', [WhatsappOrderController::class, 'index'])->name('whatsapp.orders.index');
Route::get('/whatsapp/orders/{id}/process', [WhatsappOrderController::class, 'process'])->name('whatsapp.orders.process');
Route::post('/whatsapp/orders/{id}/cancel', [WhatsappOrderController::class, 'cancel'])->name('whatsapp.orders.cancel');

#Owner
Route::middleware('check.owner')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('users', UserController::class);
    });
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
    });

// Sales Chart
Route::get('/charts', [SalesChartController::class, 'index'])->name('charts.index');

//Inventory

Route::resource('inventories', InventoryController::class)->except(['show']);
Route::get('/inventories/opname', [InventoryController::class, 'opname'])->name('inventories.opname');
Route::post('/inventories/opname', [InventoryController::class, 'storeOpname'])->name('inventories.opname.store');



//Purchase
Route::resource('purchase-returns', PurchaseReturnController::class)->middleware('auth');

Route::resource('purchases', PurchaseController::class);
Route::get('purchases/{id}/refund', [PurchaseController::class, 'refund'])->name('purchases.refund');
Route::post('purchases/{id}/refund', [PurchaseController::class, 'refundStore'])->name('purchases.refund.store');
Route::resource('suppliers', SupplierController::class);
Route::resource('customers', CustomerController::class);

// WhatsApp Webhook (untuk menerima pesan masuk dari Fonnte)
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

// PWA Push Subscription (login required)
Route::middleware('auth:web')->group(function () {
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});


Route::resource('product_types', ProductTypeController::class);

Route::resource('product_colors', ProductColorController::class);

Route::get('/inventories/convert', [InventoryController::class, 'convert'])->name('inventories.convert');
Route::post('/inventories/convert', [InventoryController::class, 'storeConvert'])->name('inventories.convert.store');

    // [ROUTE BARU UNTUK API DROPDOWN]
Route::get('/api/inventories/get-units', [InventoryController::class, 'getUnitsForProduct'])->name('inventories.getUnits');
#PRODUCT UNIT DETAIL VIEW
Route::get('inventories/{productUnit}', [InventoryController::class, 'show'])
     ->name('inventories.show');

// Route untuk serve gambar katalog (public access)
Route::get('/catalog/{filename}', function ($filename) {
    $path = storage_path('app/public/catalog/' . $filename);
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }
    abort(404);
})->name('catalog.image');

// Public route untuk detail produk (tanpa login, untuk QR code)
Route::get('/product/{productUnit}', [InventoryController::class, 'publicShow'])
    ->name('product.public.show');

// API route untuk mendapatkan product ID dari product unit ID (untuk QR scanner)
Route::get('/api/product-unit/{productUnit}', function ($productUnitId) {
    $productUnit = \App\Models\ProductUnit::with(['product.color', 'product.type'])->find($productUnitId);

    if (!$productUnit) {
        return response()->json(['error' => 'Product unit not found'], 404);
    }

    return response()->json([
        'id' => $productUnit->id,
        'product_id' => $productUnit->product_id,
        'name' => $productUnit->product->name . ' (' . $productUnit->name . ')',
        'product_name' => $productUnit->product->name,
        'unit_name' => $productUnit->name,
        'color' => $productUnit->product->color ? $productUnit->product->color->name : '-',
        'price' => $productUnit->price,
        'stock' => $productUnit->stock,
        'conversion_value' => $productUnit->conversion_value
    ]);
})->name('api.product-unit');
