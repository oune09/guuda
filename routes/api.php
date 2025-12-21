<?php

use App\Http\Controllers\adminController;
use Illuminate\Support\Facades\Route;
use App\Http\controllers\AuthController;
use App\Http\controllers\incidentController;
use App\Http\controllers\alerteController;
use App\Http\Controllers\citoyenController;
use App\Http\Controllers\superAdminController;


// ROUTES PUBLIQUES (sans authentification)
Route::options('/sanctum/csrf-cookie', function() {
    return response()->json('OK', 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept, X-CSRF-TOKEN',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
});

Route::options('/auth/inscription', function() {
    return response()->json('OK', 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
});

Route::options('/auth/connexion', function() {
    return response()->json('OK', 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
});

Route::options('/api/test', function() {
    return response()->json('OK', 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
});

// ROUTES D'AUTHENTIFICATION (publiques)
Route::prefix('auth')->group(function(){
    Route::post('/inscription', [AuthController::class, 'inscription']);
    Route::post('/connexion', [AuthController::class, 'connexion']);
    Route::put('/modifierUtilisateur', [AuthController::class, 'modifierUtilisateur']);
    Route::delete('/deconnexion', [AuthController::class, 'deconnexion']);
});

// ROUTES SUPERVISEUR (publiques pour l'inscription seulement)
Route::prefix('superadmin')->group(function () {
    Route::post('/inscription', [AuthController::class, 'inscriptionSuperAdmin']);
    Route::put('/modifier/{id}', [AuthController::class, 'modifierSuperAdmin']);
    Route::get('/liste', [AuthController::class, 'listerSuperAdmins']);
});

// ROUTES CITOYEN
Route::prefix('citoyen')->group(function(){
    Route::post('/creeIncident', [citoyenController::class, 'creeIncident']);
    Route::post('/ListeIncident', [citoyenController::class, 'listeIncident']);
    Route::put('/modifierIncident', [citoyenController::class, 'modifierIncident']);
    Route::delete('/SupprimerIncident', [citoyenController::class, 'supprimerIncident']);
    Route::put('/modifierUtilisateur', [citoyenController::class, 'modifierUtilisateur']);
    Route::delete('/SupprimerUtilisateur', [citoyenController::class, 'supprimerUtilisateur']);
});

// ROUTES ADMIN
Route::prefix('admin')->group(function(){
    
   
    Route::post('/alerteDetail', [adminController::class, 'alerteDetail']);
    Route::put('/modifierAlerte', [adminController::class, 'modifierAlerte']);
    Route::post('/dashboardStatistiques', [adminController::class, 'dashboardStatistiques']);
    Route::post('/statistiquesMensuelles', [adminController::class, 'statistiquesMensuelles']);
    Route::post('/getCitoyensDansRayon', [adminController::class, 'getCitoyensDansRayon']);
    Route::post('/detailAutorite', [adminController::class, 'detailAutorite']);
    Route::delete('/supprimerAlerte', [adminController::class, 'supprimerAlerte']);
});
Route::post('/creerUnite', [superAdminController::class, 'creerUnite']);

// ⚠️ ROUTES PROTÉGÉES (nécessitent authentification)
Route::middleware(['auth:sanctum'])->group(function(){
    // Ces routes ne sont accessibles qu'aux superadministrateurs authentifiés
    Route::post('/creerOrganisation', [superAdminController::class, 'creerOrganisation']);
    Route::put('/modifierOrganisation/{id}', [superAdminController::class, 'modifierOrganisation']);
    Route::delete('/supprimerOrganisation/{id}', [superAdminController::class, 'supprimerOrganisation']);
    Route::get('listeOrganisation', [superAdminController::class, 'listeOrganisation']);
    Route::post('creerAdmin', [authController::class,'creerAdmin']);
    Route::post('creerAutorite', [authController::class,'creerAutorite']);
    Route::put('/modifierUnite', [superAdminController::class, 'modifierUnite']);
    Route::delete('/supprimerUnite/{id}', [superAdminController::class, 'supprimerUnite']);
    Route::get('/listeUtilisateur', [superAdminController::class,'listeUtilisateur']);
    Route::get('/listeUnite', [superAdminController::class, 'listeUnite']);
    Route::post('/promouvoirEnAutorite/{id}', [superAdminController::class, 'promouvoirEnAutorite']);
    Route::post('/promouvoirEnAdmin/{id}', [superAdminController::class, 'promouvoirEnAdmin']);
    Route::post('/retrograderAdmin/{id}', [superAdminController::class, 'retrograderAdmin']);
    Route::post('/supprimerAutorite/{id}', [superAdminController::class, 'supprimerAutorite']);
    Route::post('/monUnite', [adminController::class, 'monUnite']);
    Route::post('/uniteCoordonnee', [AdminController::class,'uniteCoordonnee']);
    Route::get('/detailUnite/{id}', [superAdminController::class,'detailUnite']);
    Route::post('/creerSuperAdmin', [superAdminController::class, 'creerSuperAdmin']);
    Route::get('/listeAdmin', [superAdminController::class,'listeAdmin']);
    Route::post('/listeAutorite', [superAdminController::class,'listeAutorite']);
    Route::post('/listeAdminAutorite', [AdminController::class,'listeAutorite']);
    Route::post('/test', [superAdminController::class, 'creerOrganisation']);
    Route::post('/admin/creerAlerte', [adminController::class, 'creerAlerte']);
    Route::post('/incidentsParUnite', [incidentController::class, 'incidentsParUnite']);
    Route::post('/listeAlerte', [adminController::class, 'listeAlerte']);
});

// ROUTES INCIDENT
Route::prefix('incident')->group(function() {
    Route::post('/creerincident', [incidentController::class, 'creerincidenet']);
    Route::post('/incidentsProches', [incidentController::class, 'incidentsProches']);
    Route::post('/detailIncident', [incidentController::class, 'detailIncident']);
    Route::post('/trouverOrganisationUniteResponsable', [incidentController::class, 'notifierAdministrateursUnite']);
    Route::post('/notifierAdministrateursUnite', [incidentController::class, 'creerincidenet']);
    Route::post('/determinerPriorite', [incidentController::class, 'determinerPriorite']);
});