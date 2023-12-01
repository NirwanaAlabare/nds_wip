<?php

use App\Http\Controllers\CutPlanController;
use App\Http\Controllers\CutPlanNewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\SpreadingController;
use App\Http\Controllers\FormCutInputController;
use App\Http\Controllers\ManualFormCutController;
use App\Http\Controllers\LapPemakaianController;
use App\Http\Controllers\MasterPartController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\StockerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\DCInController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\SecondaryInController;

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

Auth::routes(['register' => false]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    // User
    Route::controller(UserController::class)->prefix("user")->group(function () {
        Route::put('/update/{id?}', 'update')->name('update-user');
    });

    // Marker
    Route::controller(MarkerController::class)->prefix("marker")->middleware('marker')->group(function () {
        Route::get('/', 'index')->name('marker');
        Route::get('/create', 'create')->name('create-marker');
        Route::post('/store', 'store')->name('store-marker');
        Route::get('/edit', 'edit')->name('edit-marker');
        Route::put('/update', 'update')->name('update-marker');
        Route::post('/show', 'show')->name('show-marker');
        Route::post('/show_gramasi', 'show_gramasi')->name('show_gramasi');
        Route::post('/update_status', 'update_status')->name('update_status');
        Route::put('/update_marker', 'update_marker')->name('update_marker');
        Route::post('/print-marker/{kodeMarker?}', 'printMarker')->name('print-marker');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('get-marker-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('get-marker-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('get-marker-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('get-marker-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('get-marker-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('get-marker-number');
    });

    // Spreading
    Route::controller(SpreadingController::class)->prefix("spreading")->middleware('spreading')->group(function () {
        Route::get('/', 'index')->name('spreading');
        Route::get('/create', 'create')->name('create-spreading');
        Route::post('/getno_marker', 'getno_marker')->name('getno_marker');
        Route::get('/getdata_marker', 'getdata_marker')->name('getdata_marker');
        Route::get('/getdata_ratio', 'getdata_ratio')->name('getdata_ratio');
        Route::post('/store', 'store')->name('store-spreading');
        Route::put('/update', 'update')->name('update-spreading');
        Route::get('/get-order-info', 'getOrderInfo')->name('get-spreading-data');
        Route::get('/get-cut-qty', 'getCutQty')->name('get-cut-qty-data');
        // export excel
        // Route::get('/export_excel', 'export_excel')->name('export_excel');
        // Route::get('/export', 'export')->name('export');
    });

    // Form Cut Input
    Route::controller(FormCutInputController::class)->prefix("form-cut-input")->middleware("meja")->group(function () {
        Route::get('/', 'index')->name('form-cut-input');
        Route::get('/process/{id?}', 'process')->name('process-form-cut-input');
        Route::get('/get-number-data', 'getNumberData')->name('get-number-form-cut-input');
        Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-form-cut-input');
        Route::get('/get-item', 'getItem')->name('get-item-form-cut-input');
        Route::put('/start-process/{id?}', 'startProcess')->name('start-process-form-cut-input');
        Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-form-cut-input');
        Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-form-cut-input');
        Route::get('/get-time-record/{noForm?}', 'getTimeRecord')->name('get-time-form-cut-input');
        Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-form-cut-input');
        Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-form-cut-input');
        Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-form-cut-input');
        Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-form-cut-input');
        Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-form-cut-input');
        Route::get('/check-spreading-form/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-form-cut-input');
        Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-form-cut-input');
        Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-form-cut-input');
        Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-form-cut-input');
        Route::get('/get-form-cut-ratio', 'getRatio')->name('get-form-cut-ratio');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('form-cut-get-marker-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('form-cut-get-marker-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('form-cut-get-marker-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('form-cut-get-marker-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('form-cut-get-marker-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('form-cut-get-marker-number');

        // no cut update
        Route::put('/update-no-cut', 'updateNoCut')->name('form-cut-update-no-cut');
    });

    // Manual Form Cut Input
    Route::controller(ManualFormCutController::class)->prefix("manual-form-cut")->middleware("meja")->group(function () {
        Route::get('/', 'index')->name('manual-form-cut');
        Route::get('/create', 'create')->name('create-manual-form-cut');
        Route::get('/create-new', 'createNew')->name('create-new-manual-form-cut');
        Route::get('/process/{id?}', 'process')->name('process-manual-form-cut');
        Route::get('/get-number-data', 'getNumberData')->name('get-number-manual-form-cut');
        Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-manual-form-cut');
        Route::get('/get-item', 'getItem')->name('get-item-manual-form-cut');
        Route::put('/start-process', 'startProcess')->name('start-process-manual-form-cut');
        Route::post('/store-marker/{id?}', 'storeMarker')->name('store-marker-manual-form-cut');
        Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-manual-form-cut');
        Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-manual-form-cut');
        Route::get('/get-time-record/{noForm?}', 'getTimeRecord')->name('get-time-manual-form-cut');
        Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-manual-form-cut');
        Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-manual-form-cut');
        Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-manual-form-cut');
        Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-manual-form-cut');
        Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-manual-form-cut');
        Route::get('/check-spreading-form/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-manual-form-cut');
        Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-manual-form-cut');
        Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-manual-form-cut');
        Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-manual-form-cut');
        Route::get('/get-form-cut-ratio', 'getRatio')->name('get-manual-form-cut-ratio');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('manual-form-cut-get-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('manual-form-cut-get-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('manual-form-cut-get-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('manual-form-cut-get-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('manual-form-cut-get-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('manual-form-cut-get-number');
    });

    // Cutting Plan
    Route::controller(CutPlanController::class)->prefix("cut-plan")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('cut-plan');
        Route::get('/create', 'create')->name('create-cut-plan');
        Route::post('/store', 'store')->name('store-cut-plan');
        Route::put('/update/{id?}', 'update')->name('update-cut-plan');
        Route::delete('/destroy', 'destroy')->name('destroy-cut-plan');
        Route::get('/get-selected-form/{noCutPlan?}', 'getSelectedForm')->name('get-selected-form');
        Route::get('/get-cut-plan-form', 'getCutPlanForm')->name('get-cut-plan-form');
    });

    // Cutting Plan New
    // Route::controller(CutPlanNewController::class)->prefix("cut-plan-new")->middleware('admin')->group(function () {
    //     Route::get('/', 'index')->name('cut-plan-new');
    //     Route::post('/show_detail', 'show_detail')->name('show_detail');
    //     Route::get('/create', 'create')->name('create-cut-plan');
    //     Route::post('/store', 'store')->name('store-cut-plan');
    //     Route::put('/update', 'update')->name('update-cut-plan');
    //     Route::delete('/destroy', 'destroy')->name('destroy-cut-plan');
    //     Route::get('/get-selected-form/{noCutPlan?}', 'getSelectedForm')->name('get-selected-form');
    // });

    // Laporan
    Route::controller(LapPemakaianController::class)->prefix("lap_pemakaian")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('lap_pemakaian');
        // export excel
        Route::get('/export_excel', 'export_excel')->name('export_excel');
        Route::get('/export', 'export')->name('export');
    });

    // Master Part
    Route::controller(MasterPartController::class)->prefix("master-part")->middleware('stocker')->group(function () {
        Route::get('/', 'index')->name('master-part');
        Route::post('/store', 'store')->name('store-master-part');
        Route::put('/update/{id?}', 'update')->name('update-master-part');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-part');
    });

    // Part
    Route::controller(PartController::class)->prefix("part")->middleware('stocker')->group(function () {
        Route::get('/', 'index')->name('part');
        Route::get('/create', 'create')->name('create-part');
        Route::post('/store', 'store')->name('store-part');
        Route::get('/edit', 'edit')->name('edit-part');
        Route::put('/update/{id?}', 'update')->name('update-part');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-part');

        // part form
        Route::get('/manage-part-form/{id?}', 'managePartForm')->name('manage-part-form');
        Route::get('/get-form-cut/{id?}', 'getFormCut')->name('get-part-form-cut');
        Route::post('/store-part-form', 'storePartForm')->name('store-part-form');
        Route::delete('/destroy-part-form', 'destroyPartForm')->name('destroy-part-form');
        Route::get('/show-part-form', 'showPartForm')->name('show-part-form');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('get-part-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('get-part-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('get-part-panels');
    });

    // Stocker
    Route::controller(StockerController::class)->prefix("stocker")->middleware('stocker')->group(function () {
        Route::get('/', 'index')->name('stocker');
        Route::get('/show/{partDetailId?}/{formCutId?}', 'show')->name('show-stocker');
        Route::post('/print-stocker/{index?}', 'printStocker')->name('print-stocker');
        Route::post('/print-numbering/{index?}', 'printNumbering')->name('print-numbering');

        Route::put('/count-stocker-update', 'countStockerUpdate')->name('count-stocker-update');
    });

    // DC IN
    Route::controller(DCInController::class)->prefix("dc-in")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('dc-in');
        Route::get('/create/{no_form?}', 'create')->name('create-dc-in');
        Route::get('/getdata_stocker_info', 'getdata_stocker_info')->name('getdata_stocker_info');
        Route::get('/getdata_dc_in', 'getdata_dc_in')->name('getdata_dc_in');
        Route::post('/store', 'store')->name('store_dc_in');
    });

    // Secondary IN
    Route::controller(SecondaryInController::class)->prefix("secondary-in")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('secondary-in');
        Route::get('/create', 'create')->name('create-secondary-in');
    });

    // Rack
    Route::controller(RackController::class)->prefix("rack")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('rack');
        Route::get('/create', 'create')->name('create-rack');
        Route::post('/store', 'store')->name('store-rack');
        Route::put('/update', 'update')->name('update-rack');

        Route::get('/rack-detail', 'rackDetail')->name('rack-detail');
    });

    // Mutasi Karywawan
    Route::controller(EmployeeController::class)->prefix("mut-karyawan")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('mut-karyawan');
        Route::get('/create', 'create')->name('create-mut-karyawan');
        Route::post('/store', 'store')->name('store-mut-karyawan');
        Route::put('/update', 'update')->name('update-mut-karyawan');
        Route::delete('/destroy', 'destroy')->name('destroy-mut-karyawan');
        Route::get('/getdataline', 'getdataline')->name('getdataline');
        Route::get('/gettotal', 'gettotal')->name('gettotal');
        Route::get('/getdatanik', 'getdatanik')->name('getdatanik');
        Route::get('/getdatalinekaryawan', 'getdatalinekaryawan')->name('getdatalinekaryawan');
        Route::get('/export_excel_mut_karyawan', 'export_excel_mut_karyawan')->name('export_excel_mut_karyawan');
        Route::get('/line-chart-data', 'lineChartData')->name('line-chart-data');
    });

    Route::controller(SummaryController::class)->prefix("summary")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('summary');
    });

    // Manager
    Route::controller(ManagerController::class)->prefix("manager")->middleware('manager')->group(function () {
        Route::get('/cutting', 'cutting')->name('manage-cutting');
        Route::get('/cutting/detail/{id?}', 'detailCutting')->name('detail-cutting');
        Route::put('/cutting/generate/{id?}', 'generateStocker')->name('generate-stocker');
    });
});

// Dashboard
Route::get('/dashboard-cutting', function () {
    return view('dashboard', ['page' => 'dashboard-cutting']);
})->middleware('auth')->name('dashboard-cutting');

Route::get('/dashboard-stocker', function () {
    return view('dashboard', ['page' => 'dashboard-stocker']);
})->middleware('auth')->name('dashboard-stocker');

Route::get('/dashboard-dc', function () {
    return view('dashboard', ['page' => 'dashboard-dc']);
})->middleware('auth')->name('dashboard-dc');

Route::get('/dashboard-mut-karyawan', function () {
    return view('dashboard', ['page' => 'dashboard-mut-karyawan']);
})->middleware('auth')->name('dashboard-mut-karyawan');

// Misc
Route::get('/timer', function () {
    return view('example.timeout');
})->middleware('auth');

Route::get('/widgets', function () {
    return view('component.widgets');
})->middleware('auth');

Route::get('/kanban', function () {
    return view('component.kanban');
})->middleware('auth');

Route::get('/gallery', function () {
    return view('component.gallery');
})->middleware('auth');

Route::get('/calendar', function () {
    return view('component.calendar');
})->middleware('auth');

Route::get('/timeline', function () {
    return view('component.UI.timeline');
})->middleware('auth');

Route::get('/sliders', function () {
    return view('component.UI.sliders');
})->middleware('auth');

Route::get('/modals', function () {
    return view('component.UI.modals');
})->middleware('auth');

Route::get('/ribbons', function () {
    return view('component.UI.ribbons');
})->middleware('auth');

Route::get('/general', function () {
    return view('component.UI.general');
})->middleware('auth');

Route::get('/datatable', function () {
    return view('component.tables.data');
})->middleware('auth');

Route::get('/jsgrid', function () {
    return view('component.tables.jsgrid');
})->middleware('auth');

Route::get('/simpletable', function () {
    return view('component.tables.simple');
})->middleware('auth');

Route::get('/advanced-form', function () {
    return view('component.forms.advanced');
})->middleware('auth');

Route::get('/general-form', function () {
    return view('component.forms.general');
})->middleware('auth');

Route::get('/validation-form', function () {
    return view('component.forms.validation');
})->middleware('auth');
