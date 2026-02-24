<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\VerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INSCRIPTION CITOYEN (EMAIL OU TELEPHONE + OTP)
    |--------------------------------------------------------------------------
    */
    public function inscription(Request $request)
    {
        $data = $request->validate([
            'nom_utilisateur'       => 'nullable|string',
            'prenom_utilisateur'    => 'nullable|string',
            'email_utilisateur'     => 'nullable|email|unique:utilisateurs,email_utilisateur',
            'telephone_utilisateur' => 'nullable|unique:utilisateurs,telephone_utilisateur',
            'canal'                 => 'required|in:email,sms',
        ]);

        if (empty($data['email_utilisateur']) && empty($data['telephone_utilisateur'])) {
            return response()->json([
                'message' => 'Email ou numéro de téléphone requis'
            ], 422);
        }

        // Création OTP
        $otp = random_int(100000, 999999);

        $utilisateur = Utilisateur::create([
            'nom_utilisateur'       => $data['nom_utilisateur'] ?? '',
            'prenom_utilisateur'    => $data['prenom_utilisateur'] ?? '',
            'email_utilisateur'     => $data['email_utilisateur'] ?? null,
            'telephone_utilisateur' => $data['telephone_utilisateur'] ?? null,
            'is_active'             => false,
            'verified_at'           => null,
        ]);

        // IMPORTANT : PAS de rôle ici (évite comptes fantômes)
        // Le rôle citoyen sera assigné APRES vérification OTP

        VerificationToken::create([
            'utilisateur_id' => $utilisateur->id,
            'token'          => hash('sha256', $otp),
            'canal'          => $data['canal'],
            'type'           => 'otp',
            'expires_at'     => now()->addMinutes(10),
        ]);

        if ($data['canal'] === 'email') {
            Mail::to($utilisateur->email_utilisateur)
                ->send(new \App\Mail\OtpMail($otp));
        } else {
            // SMS local (log)
            Log::info("OTP SMS pour {$utilisateur->telephone_utilisateur} : {$otp}");
        }

        return response()->json([
            'message' => 'OTP envoyé',
            'utilisateur_id' => $utilisateur->id
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICATION OTP + ACTIVATION COMPTE CITOYEN
    |--------------------------------------------------------------------------
    */
    public function verifierOtp(Request $request)
    {
        $data = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'otp'            => 'required',
        ]);

        $verification = VerificationToken::where('utilisateur_id', $data['utilisateur_id'])
            ->where('type', 'otp')
            ->where('expires_at', '>', now())
            ->latest()
            ->firstOrFail();

        if (hash('sha256', $data['otp']) !== $verification->token) {
            return response()->json([
                'message' => 'OTP invalide'
            ], 403);
        }

    
        $verification->update([
            'used_at' => now()
        ]);

        return response()->json([
            'message' => 'Compte citoyen activé avec succès'
        ], 200);
    }


    public function finaliserCompte(Request $request)
    {
        $data = $request->validate([
            'utilisateur_id'        => 'required|exists:utilisateurs,id',
            'nom_utilisateur'       => 'required|string',
            'prenom_utilisateur'    => 'required|string',
            'mot_de_passe_utilisateur'=> 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|max:2048',
        ]);

        $utilisateur = Utilisateur::findOrFail($data['utilisateur_id']);

        if ($utilisateur->is_active) {
            return response()->json([
                'message' => 'Le compte est déjà activé'
            ], 400);
        }

        $utilisateur->update([
            'mot_de_passe_utilisateur' => Hash::make($data['mot_de_passe_utilisateur']),
            'nom_utilisateur'          => $data['nom_utilisateur'],
            'prenom_utilisateur'       => $data['prenom_utilisateur'],
            'photo'                    => $request->hasFile('photo') ? $request->file('photo')->store('photos_utilisateurs') : null,
            'is_active'                => true,
            'verified_at'              => now(),
        ]);

        // Assignation rôle citoyen
        $utilisateur->assignRole('citoyen');

        return response()->json([
            'message' => 'Compte citoyen finalisé avec succès'
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CONNEXION (EMAIL + MOT DE PASSE)
    |--------------------------------------------------------------------------
    */
    public function connexion(Request $request)
    {
        $validation = $request->validate([
            'email_utilisateur'       => 'required|email',
            'mot_de_passe_utilisateur'=> 'required|string|min:8',
        ]);

        $utilisateur = Utilisateur::where('email_utilisateur', $validation['email_utilisateur'])->first();

        if (
            !$utilisateur ||
            !$utilisateur->is_active ||
            !Hash::check($validation['mot_de_passe_utilisateur'], $utilisateur->mot_de_passe_utilisateur)
        ) {
            return response()->json([
                'message' => 'Compte inactif ou identifiants incorrects'
            ], 401);
        }

        // Révoquer anciens tokens
        $utilisateur->tokens()->delete();

        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token'   => $token,
            'token_type' => 'Bearer',
            'utilisateur' => [
                'id'        => $utilisateur->id,
                'nom'       => $utilisateur->nom_utilisateur,
                'prenom'    => $utilisateur->prenom_utilisateur,
                'email'     => $utilisateur->email_utilisateur,
                'telephone' => $utilisateur->telephone_utilisateur,
                'photo'     => $utilisateur->photo ? Storage::url($utilisateur->photo) : null,
                'roles'     => $utilisateur->getRoleNames(),
                'permissions'=> $utilisateur->getAllPermissions()->pluck('name'),
            ]
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DECONNEXION
    |--------------------------------------------------------------------------
    */
    public function deconnexion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    public function deconnexionTousAppareils(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion de tous les appareils réussie'
        ], 200);
    }
}
