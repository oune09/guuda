<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Utilisateur;
use Dflydev\DotAccessData\Util;

Route::prefix('auth')->group(function () {
    Route::post('/inscription', [AuthController::class, 'inscription']);
    Route::post('/connexion', [AuthController::class, 'connexion']);
    Route::post('deconnexion', [AuthController::class, 'deconnexion']);
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'motDePasseOublie']);
    Route::post('activation-autorite', [UtilisateurController::class, 'activerCompte']);
    Route::post('/verification-otp', [AuthController::class, 'verifierOtp']);
    Route::post('/finaliser-compte', [AuthController::class, 'finaliserCompte']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('utilisateur')->group(function () {
        Route::get('/profil', [AuthController::class, 'profil'])
              ->middleware('permission:user.view.profile');

        Route::put('/profil', [AuthController::class, 'modifierUtilisateur'])
            ->middleware('permission:user.update.profile');

    });

    Route::post('tst', [IncidentController::class, 'creerIncident']);

    Route::prefix('incident')->group(function () {

        

        Route::get('/mes-incidents', [IncidentController::class, 'mesIncidents'])
            ->middleware('permission:incident.view.own');

        Route::get('/detail/{id}', [IncidentController::class, 'detailIncident'])
            ->middleware('permission:incident.view.detail');

        Route::put('/traiter/{id}', [IncidentController::class, 'traiterIncident'])
            ->middleware('permission:incident.update.status');

        Route::delete('/supprimer/{id}', [IncidentController::class, 'supprimerIncident'])
            ->middleware('permission:incident.delete');

        Route::get('/unite', [IncidentController::class, 'incidentsParUnite'])
            ->middleware('permission:incident.view.unite');

        Route::get('/incident', [IncidentController::class, 'incidents'])
            ->middleware('permission:incident.view.all');

        Route::get('/incidentCitoyen/{id}', [IncidentController::class, 'citoyenIncidents'])
            ->middleware('permission:incident.view.citoyen');
    });

    Route::prefix('alerte')->group(function () {

        Route::post('/creer', [AlerteController::class, 'creerAlerte'])
            ->middleware('permission:alerte.create');

        Route::post('/unite', [AlerteController::class, 'alertesParUnite'])
            ->middleware('permission:alerte.view.unite');

        Route::get('/liste', [AlerteController::class, 'listeAlerte'])
            ->middleware('permission:alerte.view.all');

        Route::get('/alerte', [AlerteController::class, 'alertes'])
            ->middleware('permission:alerte.view.all');

        Route::get('/mes-alertes', [AlerteController::class, 'mesAlertes'])
            ->middleware('permission:alerte.view.own');

        Route::get('/detail/{id}', [AlerteController::class, 'alerteDetail'])
            ->middleware('permission:alerte.view.detail');

        Route::put('/modifier/{id}', [AlerteController::class, 'modifierAlerte'])
            ->middleware('permission:alerte.update');

        Route::delete('/supprimer/{id}', [AlerteController::class, 'supprimerAlerte'])
            ->middleware('permission:alerte.delete');
    });

    Route::prefix('organisation')->group(function () {

        Route::post('/creer', [OrganisationController::class, 'creerOrganisation'])
            ->middleware('permission:organisation.create');

        Route::get('/liste', [OrganisationController::class, 'listeOrganisation'])
            ->middleware('permission:organisation.view');

        Route::put('/modifier/{id}', [OrganisationController::class, 'modifierOrganisation'])
            ->middleware('permission:organisation.update');

        Route::put('/desactiver/{id}', [OrganisationController::class, 'desactiverOrganisation'])
            ->middleware('permission:organisation.delete');

        Route::get('/detail/{id}', [OrganisationController::class, 'detailOrganisation'])
            ->middleware('permission:organisation.view');
    });

     Route::prefix('unite')->group(function () {

        Route::post('/creer', [UniteController::class, 'creerUnite'])
            ->middleware('permission:unite.create');
        
        Route::put('coordonnes', [UniteController::class, 'uniteCoordonnee'])
            ->middleware('permission:unite.update');

        Route::get('/liste', [UniteController::class, 'listeUnite'])
            ->middleware('permission:unite.view');

        Route::get('/detail/{id}', [UniteController::class, 'detailUnite'])
            ->middleware('permission:unite.view.detail');

        Route::put('/modifier/{id}', [UniteController::class, 'modifierUnite'])
            ->middleware('permission:unite.update');

        Route::put('/desactiver/{id}', [UniteController::class, 'desactiverUnite'])
            ->middleware('permission:unite.delete');

       Route::get('/mon-unite', [UniteController::class, 'monUnite'])
            ->middleware('permission:unite.view.detail');
    });

    Route::prefix('utilisateurs')->group(function () {

        Route::get('/liste', [UtilisateurController::class, 'listeUtilisateur'])
            ->middleware('permission:user.view.all');

        Route::post('/creer-autorite', [UtilisateurController::class, 'creerAutorite'])
            ->middleware('permission:user.create.autorite');
        
        Route::put('/modifier/{id}', [UtilisateurController::class, 'modifierUtilisateur'])
            ->middleware('permission:user.update.profile');

        Route::get('/autorites', [UtilisateurController::class, 'ListeAutorite'])
            ->middleware('permission:user.view.all');
        
        Route::get('/autorite/{id}', [UtilisateurController::class, 'detailAutorite'])
            ->middleware('permission:user.view.all');

        Route::get('/citoyens', [UtilisateurController::class, 'listeCitoyens'])
            ->middleware('permission:user.view.all');
        
       Route::get('/citoyen/{id}', [UtilisateurController::class, 'detailCitoyen'])
            ->middleware('permission:user.view.all'); 
        
    });

    Route::prefix('roles')->group(function () {

        Route::get('/liste', [RoleController::class, 'index'])
            ->middleware('permission:role.view');

        Route::post('/creer', [RoleController::class, 'store'])
            ->middleware('permission:role.create');

        Route::put('/modifier/{id}', [RoleController::class, 'update'])
            ->middleware('permission:role.update');

        Route::delete('/supprimer/{id}', [RoleController::class, 'destroy'])
            ->middleware('permission:role.delete,sanctum');

        Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions'])
            ->middleware('permission:role.assign.permission');
    });

    Route::prefix('permissions')->group(function () {

        Route::get('/liste', [PermissionController::class, 'index'])
            ->middleware('permission:permission.view');

        Route::post('/creer', [PermissionController::class, 'store'])
            ->middleware('permission:permission.create');

        Route::delete('/supprimer/{id}', [PermissionController::class, 'destroy'])
            ->middleware('permission:permission.delete');
    });

});
