<?php

use Illuminate\Support\Facades\Route;
use App\Http\controllers\AuthController;
use App\Http\controllers\incidentController;

Route::prefix('auth')->group(function(){
    Route::post('/inscription',[AuthController::class,'inscription']);
    Route::post('/connexion',[AuthController::class,'connexion']);
    Route::put('/modifierUtilisateur',[AuthController::class,'modifierUtilisateur']);
    Route::delete('/SupprimerUtilisateur',[AuthController::class,'supprimerUtilisateur']);
});

Route::prefix('incident')->group(function(){
    Route::post('/creeIcident',[incidentController::class,'creeIcident']);
    Route::post('/ListeIncident',[incidentController::class,'listeIncident']);
    Route::put('/modifierIncident',[incidentController::class,'modifierIncident']);
    Route::delete('/SupprimerIncident',[incidentController::class,'supprimerIncident']);
});

Route::prefix('alerte')->group(function(){
    Route::post('/creerAlerte',[alerteController::class,'creerAlerte']);
    Route::post('/listeAlerte',[alerteController::class,'listeAlerte']);
    Route::put('/modifierAlerte',[alerteController::class,'modifierAlerte']);
    Rooute::delete('/supprimerAlerte',[alerteController::class,'supprimerAlerte']);
});
