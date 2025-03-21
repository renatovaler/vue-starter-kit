<?php

use App\Http\Controllers\CalculationController;

Route::inertia('/calculations', 'Calculations')->name('calculations.form');

Route::post('/calculations', [CalculationController::class, 'processCalculations'])->name('calculations.process');

Route::get('/calculations/export/excel', [CalculationController::class, 'exportExcel'])->name('calculations.export.excel');
Route::get('/calculations/export/pdf', [CalculationController::class, 'exportPdf'])->name('calculations.export.pdf');

