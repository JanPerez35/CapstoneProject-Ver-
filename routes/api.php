<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacilityCostController;

Route::post('/facility/test-store', [FacilityCostController::class, 'storeEvent']);