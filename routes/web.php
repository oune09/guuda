<?php

use App\Http\Controllers\adminController;
use Illuminate\Support\Facades\Route;
use App\Http\controllers\AuthController;
use App\Http\controllers\incidentController;
use App\Http\controllers\alerteController;
use App\Http\controllers\secteurController;
use App\Http\controllers\autoriteSecteurController;
use App\Http\Controllers\citoyenController;
use App\Http\Controllers\superAdminController;

Route::prefix('auth')->group(function(){
    Route::post('/inscription',[AuthController::class,'inscription']);
    Route::post('/connexion',[AuthController::class,'connexion']);
    Route::put('/modifierUtilisateur',[AuthController::class,'modifierUtilisateur']);
    Route::delete('/deconnexion',[AuthController::class,'deconnexion']);
});

Route::prefix('citoyen')->group(function(){
    Route::post('/creeIcident',[citoyenController::class,'creeIcident']);
    Route::post('/ListeIncident',[citoyenController::class,'listeIncident']);
    Route::put('/modifierIncident',[citoyenController::class,'modifierIncident']);
    Route::delete('/SupprimerIncident',[citoyenController::class,'supprimerIncident']);
    Route::put('/modifierUtilisateur',[citoyenController::class,'modifierUtilisateur']);
    Route::delete('/SupprimerUtilisateur',[citoyenController::class,'supprimerUtilisateur']);
});

Route::prefix('admin')->group(function(){
    Route::post('/creerAlerte',[adminController::class,'creerAlerte']);
    Route::post('/listeAlerte',[adminController::class,'listeAlerte']);
    Route::put('/modifierAlerte',[adminController::class,'modifierAlerte']);
    Route::delete('/supprimerAlerte',[adminController::class,'supprimerAlerte']);
});

Route::prefix('superadmin')->group(function(){
    Route::post('/creersecteur',[superAdminController::class,'creerSecteur']);
    Route::put('/modifiersecteur',[superAdminController::class,'modifierSecteur']);
    Route::delete('/supprimersecteur',[superAdminController::class,'supprimerSecteur']);
    Route::post('/creerville',[superAdminController::class,'creerVille']);
    Route::put('/modifierVille',[superAdminController::class,'modifierVille']);
    Route::put('/listeVille',[superAdminController::class,'listeVille']);
    Route::delete('/supprimerVille',[superAdminController::class,'supprimerVille']);
    Route::post('/creerOrganisation',[superAdminController::class,'creerOrganisation']);
    Route::put('/modifierOrganisation',[superAdminController::class,'modifierOrganisation']);
    Route::delete('/supprimerOrganisation',[superAdminController::class,'supprimerOrganisation']);
     Route::post('/retrograderAdmin',[superAdminController::class,'retrograderAdmin']);
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
