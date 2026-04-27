<?php

use App\Http\Controllers\General\DashboardController;
use App\Http\Controllers\DashboardWipLineController;

// Dashboard
Route::get('/dashboard-track', [DashboardController::class, 'track'])->middleware('auth')->name('dashboard-track');
Route::get('/dashboard-marker', [DashboardController::class, 'marker'])->middleware('auth')->name('dashboard-marker');
Route::get('/dashboard-wip', [DashboardWipLineController::class, 'index'])->middleware('auth')->name('dashboard-wip');
Route::get('/dashboard-wip/wip-line/{id?}', [DashboardWipLineController::class, 'show_wip_line'])->name('show_wip_line');
// Factory
Route::get('/dashboard-wip/factory-performance/{year?}/{month?}', [DashboardWipLineController::class, 'factoryPerformance'])->name('dashboard-factory-performance');
// Chief
Route::get('/dashboard-wip/chief-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'chiefSewing'])->name('dashboard-chief-sewing');
Route::get('/dashboard-wip/chief-sewing-data', [DashboardWipLineController::class, 'chiefSewingData'])->name('dashboard-chief-sewing-data');
// Chief Range
Route::get('/dashboard-wip/chief-sewing-range/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'chiefSewingRange'])->middleware('auth')->name('dashboard-chief-sewing-range');
Route::get('/dashboard-wip/chief-sewing-range-data', [DashboardWipLineController::class, 'chiefSewingRangeData'])->middleware('auth')->name('dashboard-chief-sewing-range-data');
Route::post('/dashboard-wip/chief-sewing-range-data-export', [DashboardWipLineController::class, 'chiefSewingRangeDataExport'])->middleware('auth')->name('dashboard-chief-sewing-range-data-export');
// Leader
Route::get('/dashboard-wip/leader-sewing/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'leaderSewing'])->middleware('auth')->name('dashboard-leader-sewing');
Route::get('/dashboard-wip/leader-sewing-data', [DashboardWipLineController::class, 'leaderSewingData'])->middleware('auth')->name('dashboard-leader-sewing-data');
Route::get('/dashboard-wip/leader-sewing-filter', [DashboardWipLineController::class, 'leaderSewingFilter'])->middleware('auth')->name('dashboard-leader-sewing-filter');
Route::post('/dashboard-wip/leader-sewing-range-data-export', [DashboardWipLineController::class, 'leaderSewingRangeDataExport'])->middleware('auth')->name('dashboard-leader-sewing-range-data-export');
// Line Support
Route::get('/dashboard-wip/support-line-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'supportLineSewing'])->name('dashboard-support-line-sewing');
Route::get('/dashboard-wip/support-line-sewing-data', [DashboardWipLineController::class, 'supportLineSewingData'])->name('dashboard-support-line-sewing-data');
// Factory
Route::get('/dashboard-wip/factory-daily-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'factoryDailyPerformance'])->name('dashboard-factory-daily-sewing');
Route::get('/dashboard-wip/factory-daily-sewing-data', [DashboardWipLineController::class, 'factoryDailyPerformanceData'])->name('dashboard-factory-daily-sewing-data');
// Chief Leader
Route::get('/dashboard-wip/chief-leader-sewing/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'chiefLeaderSewing'])->middleware('auth')->name('dashboard-chief-leader-sewing');
Route::get('/dashboard-wip/chief-leader-sewing-data', [DashboardWipLineController::class, 'chiefLeaderSewingData'])->middleware('auth')->name('dashboard-chief-leader-sewing-data');
Route::post('/dashboard-wip/chief-leader-sewing-range-data-export', [DashboardWipLineController::class, 'chiefLeaderSewingRangeDataExport'])->middleware('auth')->name('dashboard-chief-leader-sewing-range-data-export');
// Chief Top
Route::get('/dashboard-wip/top-chief-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'topChiefSewing'])->name('dashboard-top-chief-sewing');
Route::get('/dashboard-wip/top-chief-sewing-data', [DashboardWipLineController::class, 'topChiefSewingData'])->name('dashboard-top-chief-sewing-data');
// Leader Top
Route::get('/dashboard-wip/top-leader-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'topLeaderSewing'])->name('dashboard-top-leader-sewing');
Route::get('/dashboard-wip/top-leader-sewing-data', [DashboardWipLineController::class, 'topLeaderSewingData'])->name('dashboard-top-leader-sewing-data');

Route::get('/marker-qty', [DashboardController::class, 'markerQty'])->middleware('auth')->name('marker-qty');
Route::get('/dashboard-cutting', [DashboardController::class, 'cutting'])->middleware('auth')->name('dashboard-cutting');
Route::get('/dashboard-cutting-chart', [DashboardController::class, 'cutting_chart'])->middleware('auth')->name('dashboard-cutting-chart');
Route::get('/meja-dashboard-cutting', [DashboardController::class, 'get_cutting_chart_meja'])->middleware('auth')->name('meja-dashboard-cutting');
Route::get('/cutting-chart-by-mejaid', [DashboardController::class, 'cutting_chart_by_mejaid'])->middleware('auth')->name('cutting-chart-by-mejaid');
Route::get('/cutting-qty', [DashboardController::class, 'cuttingQty'])->middleware('auth')->name('cutting-qty');
Route::get('/cutting-dashboard-list', [DashboardController::class, 'cuttingDashboardList'])->middleware('auth')->name('cutting-dashboard-list');
Route::get('/cutting-form-list', [DashboardController::class, 'cuttingFormList'])->middleware('auth')->name('cutting-form-list');
Route::get('/cutting-form-chart', [DashboardController::class, 'cuttingFormChart'])->middleware('auth')->name('cutting-form-chart');
Route::get('/cutting-worksheet-list', [DashboardController::class, 'cuttingWorksheetList'])->middleware('auth')->name('cutting-worksheet-list');
Route::get('/cutting-worksheet-total', [DashboardController::class, 'cuttingWorksheetTotal'])->middleware('auth')->name('cutting-worksheet-total');
Route::get('/cutting-output-list', [DashboardController::class, 'cuttingOutputList'])->middleware('auth')->name('cutting-output-list');
Route::get('/cutting-output-list-all', [DashboardController::class, 'cuttingOutputListAll'])->middleware('auth')->name('cutting-output-list-all');
Route::get('/cutting-output-list-panels', [DashboardController::class, 'cuttingOutputListPanels'])->middleware('auth')->name('cutting-output-list-panels');
Route::get('/cutting-output-list-data', [DashboardController::class, 'cuttingOutputListData'])->middleware('auth')->name('cutting-output-list-data');
Route::get('/cutting-stock-list-data', [DashboardController::class, 'cuttingStockListData'])->middleware('auth')->name('cutting-stock-list-data');

Route::get('/dashboard-stocker', [DashboardController::class, 'stocker'])->middleware('auth')->name('dashboard-stocker');
Route::get('/dashboard-stocker/show/{actCostingId?}', [DashboardController::class, 'showStocker'])->middleware('auth')->name('dashboard-stocker-show');

Route::get('/dashboard-dc', [DashboardController::class, 'dc'])->middleware('auth')->name('dashboard-dc');
Route::get('/dc-qty', [DashboardController::class, 'dcQty'])->middleware('auth')->name('dc-qty');
Route::get('/dashboard-sewing-eff', [DashboardController::class, 'sewingEff'])->middleware('auth')->name('dashboard-sewing-eff');
Route::get('/sewing-summary', [DashboardController::class, 'sewingSummary'])->middleware('auth')->name('dashboard-sewing-sum');
Route::get('/sewing-output-data', [DashboardController::class, 'sewingOutputData'])->middleware('auth')->name('dashboard-sewing-output');
Route::get('/dashboard-manage-user', [DashboardController::class, 'manageUser'])->middleware('auth')->name('dashboard-manage-user');
Route::get('/dashboard-export-import', [DashboardController::class, 'exportImport'])->middleware('auth')->name('dashboard-export-import');
