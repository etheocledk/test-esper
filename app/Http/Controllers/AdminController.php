<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.regex' => 'L\'email fourni n\'est pas valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Les informations d\'identification fournies sont incorrectes.',
            ], 401);
        }

        $admin = Admin::where('email', $request->email)->firstOrFail();

        $token = $admin->createToken(config('app.name', 'admin-token'))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin connecté avec succès.',
            'data' => $admin,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $admin = Auth::user();
        $admin->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.'
        ]);
    }

    public function me(Request $request)
    {
        if ($request->user()) {
            return response()->json([
                'success' => true,
                'message' => 'Informations de l\'utilisateur récupérées avec succès.',
                'data' => $request->user(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non authentifié.',
        ], 401);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
        ], [
            'old_password.required' => 'L\'ancien mot de passe est requis.',
            'old_password.min' => 'L\'ancien mot de passe doit comporter au moins 6 caractères.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.min' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'new_password.regex' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $admin = Auth::user();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ancien mot de passe est incorrect.',
            ], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe a été mis à jour avec succès.',
        ]);
    }

    public function changeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_email' => ['required', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'new_email' => [
                'required',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'unique:admins,email',
            ],
        ], [
            'old_email.required' => 'L\'email ancien est requis.',
            'old_email.email' => 'L\'email ancien n\'est pas valide.',
            'new_email.required' => 'L\'email est requis.',
            'new_email.regex' => 'L\'email fourni n\'est pas valide.',
            'new_email.unique' => 'Cet email est déjà utilisé par un autre administrateur.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $admin = Auth::user();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $admin = Admin::where('id', $admin->id)->where('email', $request->old_email)->first();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ancien email ne correspond à aucun compte.',
            ], 400);
        }

        $admin->email = $request->new_email;
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'L\'email a été mis à jour avec succès.',
            'admin' => $admin
        ]);
    }

    public function updateInfo(Request $request)
    {
        Log::info($request->all());
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'tel' => 'nullable|string|max:15',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
        ], [
            'firstname.required' => 'Le prénom est requis.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',
            'lastname.required' => 'Le nom est requis.',
            'lastname.string' => 'Le nom doit être une chaîne de caractères.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'tel.max' => 'Le numéro de téléphone ne peut pas dépasser 15 caractères.',
            'country.string' => 'Le pays doit être une chaîne de caractères.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'city.string' => 'La ville doit être une chaîne de caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $admin = Auth::user();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $admin->update([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'tel' => $request->input('tel'),
            'country' => $request->input('country'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Les informations de l\'administrateur ont été mises à jour avec succès.',
            'admin' => $admin
        ]);
    }

    // public function updateAvatar(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    //     ], [
    //         'avatar.required' => 'L\'avatar est requis.',
    //         'avatar.image' => 'Le fichier doit être une image.',
    //         'avatar.mimes' => 'L\'image doit être au format JPEG, PNG et JPG.',
    //         'avatar.max' => 'L\'image ne doit pas dépasser 2 Mo.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     $admin = Auth::user();
    //     if (!$admin) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Utilisateur non authentifié.',
    //         ], 401);
    //     }

    //     if ($request->hasFile('avatar')) {
    //         if ($admin->avatar && Storage::exists('public/' . $admin->avatar)) {
    //             Storage::delete('public/' . $admin->avatar);
    //         }

    //         $avatarPath = $request->file('avatar')->store('avatars', 'public');
    //         $admin->avatar = asset('storage/' . $avatarPath);
    //         $admin->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Avatar mis à jour avec succès.',
    //             'avatar' => asset('storage/' . $avatarPath),
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Aucun fichier téléchargé.',
    //     ], 400);
    // }

public function updateAvatar(Request $request)
{
    $validator = Validator::make($request->all(), [
        'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'avatar.required' => 'L\'avatar est requis.',
        'avatar.image' => 'Le fichier doit être une image.',
        'avatar.mimes' => 'L\'image doit être au format JPEG, PNG ou JPG.',
        'avatar.max' => 'L\'image ne doit pas dépasser 2 Mo.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $admin = Auth::user();
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non authentifié.',
        ], 401);
    }

    if ($request->hasFile('avatar')) {
        // Supprimer l'ancien avatar s'il existe
        if ($admin->avatar && Storage::exists('public/' . $admin->avatar)) {
            Storage::delete('public/' . $admin->avatar);
        }

        // Générer un nom unique pour le fichier
        $filename = 'avatar_' . time() . '.' . $request->file('avatar')->extension();
        $path = storage_path('app/public/avatars/' . $filename);

        // Redimensionner et optimiser l'image
        $image = Image::make($request->file('avatar'))
            ->fit(300, 300) // Redimensionner à 300x300 px
            ->encode(null, 80); // Compression de l'image à 80% de qualité

        // Stocker l'image dans le dossier public/avatars
        Storage::put('public/avatars/' . $filename, $image->stream());

        // Enregistrer le chemin de l'avatar dans la base de données
        $admin->avatar = 'avatars/' . $filename;
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar mis à jour avec succès.',
            'avatar' => asset('storage/' . $admin->avatar),
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Aucun fichier téléchargé.',
    ], 400);
}

}
