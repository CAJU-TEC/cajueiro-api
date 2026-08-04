<?php

use App\Http\Controllers\Trails\MyCajueiroController;
use App\Http\Controllers\Trails\TrailBadgesController;
use App\Http\Controllers\Trails\TrailCertificateController;
use App\Http\Controllers\Trails\TrailCollaboratorsDestroyController;
use App\Http\Controllers\Trails\TrailCollaboratorsStoreController;
use App\Http\Controllers\Trails\TrailLevelCompleteController;
use App\Http\Controllers\Trails\TrailLevelPeriodController;
use App\Http\Controllers\Trails\TrailLevelSubmitController;
use App\Http\Controllers\Trails\TrailLevelsDestroyController;
use App\Http\Controllers\Trails\TrailLevelsStoreController;
use App\Http\Controllers\Trails\TrailLevelsUpdateController;
use App\Http\Controllers\Trails\TrailLevelUndoController;
use App\Http\Controllers\Trails\TrailMaterialsDestroyController;
use App\Http\Controllers\Trails\TrailMaterialsStoreController;
use App\Http\Controllers\Trails\TrailMaterialsUpdateController;
use App\Http\Controllers\Trails\TrailMineController;
use App\Http\Controllers\Trails\TrailProgressController;
use App\Http\Controllers\Trails\TrailsDestroyController;
use App\Http\Controllers\Trails\TrailsIndexController;
use App\Http\Controllers\Trails\TrailsShowController;
use App\Http\Controllers\Trails\TrailStageAdvanceController;
use App\Http\Controllers\Trails\TrailStagesDestroyController;
use App\Http\Controllers\Trails\TrailStagesReorderController;
use App\Http\Controllers\Trails\TrailStagesStoreController;
use App\Http\Controllers\Trails\TrailStagesUpdateController;
use App\Http\Controllers\Trails\TrailStageUndoController;
use App\Http\Controllers\Trails\TrailsStoreController;
use App\Http\Controllers\Trails\TrailsUpdateController;
use Illuminate\Support\Facades\Route;

// minha trilha (visão do próprio colaborador)
Route::get('trails/mine', TrailMineController::class)->name('mine')->middleware(['role_or_permission:super-admin|trails.mine|trails.index']);

// badges de todos os colaboradores, para o avatar em qualquer tela
Route::get('trails/badges', TrailBadgesController::class)->name('badges');

// meu cajueiro: conquistas do colaborador; o pôster é desenhado e exportado no front
Route::get('trails/my-cajueiro', MyCajueiroController::class)->name('cajueiro')->middleware(['role_or_permission:super-admin|trails.mine|trails.index']);

// etapas
Route::post('trails/stages/{stage}/levels', TrailLevelsStoreController::class)->name('levels.store')->middleware(['role_or_permission:super-admin|trails.update']);
Route::post('trails/stages/{stage}/advance', TrailStageAdvanceController::class)->name('stages.advance')->middleware(['role_or_permission:super-admin|trails.advance']);
Route::delete('trails/stages/{stage}/advance', TrailStageUndoController::class)->name('stages.undo')->middleware(['role_or_permission:super-admin|trails.advance']);
Route::get('trails/stages/{stage}/certificate/{collaborator}', TrailCertificateController::class)->name('stages.certificate')->middleware(['role_or_permission:super-admin|trails.mine|trails.index']);
Route::put('trails/stages/{stage}', TrailStagesUpdateController::class)->name('stages.update')->middleware(['role_or_permission:super-admin|trails.update']);
Route::delete('trails/stages/{stage}', TrailStagesDestroyController::class)->name('stages.destroy')->middleware(['role_or_permission:super-admin|trails.update']);

// níveis
Route::post('trails/levels/{level}/complete', TrailLevelCompleteController::class)->name('levels.complete')->middleware(['role_or_permission:super-admin|trails.advance']);
Route::delete('trails/levels/{level}/complete', TrailLevelUndoController::class)->name('levels.undo')->middleware(['role_or_permission:super-admin|trails.advance']);
// envio do nível pelo colaborador: permissão ampla porque quem envia é o
// próprio liderado; o controller confere que é a trilha dele
Route::post('trails/levels/{level}/submit', TrailLevelSubmitController::class)->name('levels.submit')->middleware(['role_or_permission:super-admin|trails.mine|trails.advance']);

// prazo do nível por matrícula: quem planeja é quem edita a trilha
Route::put('trails/levels/{level}/period', TrailLevelPeriodController::class)->name('levels.period')->middleware(['role_or_permission:super-admin|trails.update']);
Route::put('trails/levels/{level}', TrailLevelsUpdateController::class)->name('levels.update')->middleware(['role_or_permission:super-admin|trails.update']);
Route::delete('trails/levels/{level}', TrailLevelsDestroyController::class)->name('levels.destroy')->middleware(['role_or_permission:super-admin|trails.update']);

// materiais de apoio
Route::post('trails/materials', TrailMaterialsStoreController::class)->name('materials.store')->middleware(['role_or_permission:super-admin|trails.update']);
Route::put('trails/materials/{material}', TrailMaterialsUpdateController::class)->name('materials.update')->middleware(['role_or_permission:super-admin|trails.update']);
Route::delete('trails/materials/{material}', TrailMaterialsDestroyController::class)->name('materials.destroy')->middleware(['role_or_permission:super-admin|trails.update']);

// trilhas
Route::get('trails', TrailsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|trails.index']);
Route::post('trails', TrailsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|trails.store']);
Route::put('trails/{trail}/stages/reorder', TrailStagesReorderController::class)->name('stages.reorder')->middleware(['role_or_permission:super-admin|trails.update']);
Route::post('trails/{trail}/stages', TrailStagesStoreController::class)->name('stages.store')->middleware(['role_or_permission:super-admin|trails.update']);
Route::get('trails/{trail}/collaborators/{collaborator}/progress', TrailProgressController::class)->name('progress')->middleware(['role_or_permission:super-admin|trails.index']);
Route::post('trails/{trail}/collaborators', TrailCollaboratorsStoreController::class)->name('collaborators.store')->middleware(['role_or_permission:super-admin|trails.update']);
Route::delete('trails/{trail}/collaborators/{collaborator}', TrailCollaboratorsDestroyController::class)->name('collaborators.destroy')->middleware(['role_or_permission:super-admin|trails.update']);
Route::get('trails/{trail}', TrailsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|trails.show']);
Route::put('trails/{trail}', TrailsUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|trails.update']);
Route::delete('trails/{trail}', TrailsDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|trails.destroy']);
