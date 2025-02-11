<?php

namespace App\Http\Controllers;

use App\Mail\CompanyCredentialsEmail;
use App\Models\BillingInformation;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\CompanyProject;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $company = Company::where('email', $request->email)->first();

        if ($company) {
            if (Auth::guard('company')->attempt($request->only('email', 'password'))) {
                $token = $company->createToken(config('app.name', 'company-token'))->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie en tant qu\'entreprise.',
                    'data' => [
                        'company' => $company,
                        'token' => $token,
                    ],
                ]);
            }
        }

        $companyMember = CompanyMember::where('email', $request->email)->first();

        if ($companyMember) {
            if (Hash::check($request->password, $companyMember->password)) {
                $company = Company::where('id', $companyMember->company_id)->first();

                if ($company) {
                    $token = $company->createToken(config('app.name', 'company-token'))->plainTextToken;

                    return response()->json([
                        'success' => true,
                        'message' => 'Connexion réussie en tant que membre.',
                        'data' => [
                            'company' => $company,
                            'member' => $companyMember,
                            'token' => $token,
                        ],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Email ou Mot de passe incorrect.',
        ], 401);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:companies',
            'abonnement' => 'required|string|max:255',
            'amount' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'abonnement.required' => 'Le type d\'abonnement est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'logo.image' => 'Le fichier doit être une image valide.',
            'logo.mimes' => 'L\'image doit être de type jpeg, png ou jpg.',
            'logo.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $password = $this->generateSecurePassword(8);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('logos', 'public');
        }

        $company = Company::create([
            'name' => $request->name,
            'email' => $request->email,
            'abonnement' => $request->abonnement,
            'amount' => floatval($request->amount),
            'logo' => $logoPath ? asset('storage/' . $logoPath) : null,
            'password' => Hash::make($password),
        ]);

        Notification::create([
            'company_id' => $company->id,
            'notifications_by_email' => false,
            'update_reminder_notifications' => false,
            'new_projects_notifications_by_email' => false,
        ]);

        BillingInformation::create([
            'company_id' => $company->id,
            'bank_name' => null,
            'iban' => null,
            'bic_swift' => null,
            'account_number' => null,
        ]);

        Mail::to($request->email)->send(new CompanyCredentialsEmail($request->email, $password));


        return response()->json([
            'success' => true,
            'message' => 'La société a été ajoutée avec succès, et les informations de connexion ont été envoyées sur le mail de l\'entreprise.',
            'company' => $company,
        ]);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:companies,email,' . $id,
            'abonnement' => 'required|string|max:255',
            'amount' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'abonnement.required' => 'Le type d\'abonnement est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = Company::findOrFail($id);

        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::exists('public/' . $company->logo)) {
                Storage::delete('public/' . $company->logo);
            }

            $logo = $request->file('logo');
            $logoPath = $logo->store('logos', 'public');

            $company->update([
                'logo' => asset('storage/' . $logoPath) ?? $company->logo
            ]);
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'abonnement' => $request->abonnement,
            'amount' => floatval($request->amount),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'La société a été mise à jour avec succès.',
            'company' => $company,
        ]);
    }

    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'La société a été supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'La société demandée n\'existe pas.',
            ], 404);
        }
    }

    public function show($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'La société avec l\'ID spécifié n\'existe pas.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company,
        ]);
    }

    private function generateSecurePassword($length = 12)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specialChars = '@$!%*?&';

        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specialChars[random_int(0, strlen($specialChars) - 1)],
        ];

        $allChars = $lowercase . $uppercase . $digits . $specialChars;
        for ($i = count($password); $i < $length; $i++) {
            $password[] = $allChars[random_int(0, strlen($allChars) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }

    public function index()
    {
        $companies = Company::paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Liste des sociétés récupérée avec succès.',
            'data' => $companies
        ]);
    }

    public function logout(Request $request)
    {
        $company = Auth::user();
        $company->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.'
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'old_password.required' => 'L\'ancien mot de passe est requis.',
            'old_password.string' => 'L\'ancien mot de passe doit être une chaîne de caractères.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.string' => 'Le nouveau mot de passe doit être une chaîne de caractères.',
            'new_password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'new_password.regex' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
            'new_password_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'new_password_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères.',
            'new_password_confirmation.same' => 'La confirmation du mot de passe doit correspondre au mot de passe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = Auth::user();

        if (!Hash::check($request->old_password, $company->password)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ancien mot de passe est incorrect.',
            ], 401);
        }

        $company->password = Hash::make($request->new_password);
        $company->save();

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe a été changé avec succès.',
        ]);
    }

    public function filter(Request $request)
    {
        $query = Company::query();

        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('min_amount') && !empty($request->min_amount)) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->has('max_amount') && !empty($request->max_amount)) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $companies = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Filtrage effectué avec succès.',
            'data' => $companies,
        ]);
    }

    public function updateForClient(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'code_postal' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'numerofiscal' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'name.max' => 'Le nom de l\'entreprise ne doit pas dépasser 255 caractères.',
            'bio.string' => 'La bio doit être une chaîne de caractères.',
            'bio.max' => 'La bio ne doit pas dépasser 1000 caractères.',
            'code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'code_postal.max' => 'Le code postal ne doit pas dépasser 20 caractères.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'numerofiscal.string' => 'Le numéro fiscal doit être une chaîne de caractères.',
            'numerofiscal.max' => 'Le numéro fiscal ne doit pas dépasser 50 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = Company::where('id', $id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Cette entreprise n\'existe pas ou vous n\'êtes pas autorisé à la modifier.',
            ], 404);
        }

        $company->update([
            'name' => $request->name ?? $company->name,
            'bio' => $request->bio ?? $company->bio,
            'code_postal' => $request->code_postal ?? $company->code_postal,
            'phone' => $request->phone ?? $company->phone,
            'address' => $request->address ?? $company->address,
            'numerofiscal' => $request->numerofiscal ?? $company->numerofiscal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Les informations de votre société ont été mises à jour avec succès.',
            'company' => $company,
        ]);
    }

    public function getCompanyDonations(Request $request)
    {
        $userId = auth()->user()->id;

        $query = CompanyProject::where('company_id', $userId);

        if ($request->filled('year') && $request->year != 'tous') {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('theme') && $request->theme != 'tous') {
            $query->whereHas('project', function ($query) use ($request) {
                $query->where('theme', 'like', '%' . $request->theme . '%');
            });
        }

        if ($request->filled('name')) {
            $query->whereHas('project', function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->name . '%');
            });
        }

        $donations = $query->with(['project', 'campaign'])
            ->get()
            ->map(function ($donation) {
                $campaignStatus = $donation->campaign && $donation->campaign->status === 'completed';
                return [
                    'donation' => $donation,
                    'campaign_status' => $campaignStatus,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }


    public function getCompanyDonationsByAdmin($companyId)
    {
        $donations = CompanyProject::where('company_id', $companyId)
            ->where('is_validated', true)
            ->with('project')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }
}
