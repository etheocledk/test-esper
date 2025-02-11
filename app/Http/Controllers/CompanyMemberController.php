<?php

namespace App\Http\Controllers;

use App\Mail\InvitationEmail;
use App\Models\CompanyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class CompanyMemberController extends Controller
{
    public function index()
    {
        $company = auth()->user();
        $members = CompanyMember::where('company_id', $company->id)->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $company = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:company_members,email,NULL,id,company_id,' . $company->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Le nom du membre est obligatoire.',
            'name.string' => 'Le nom du membre doit être une chaîne de caractères.',
            'name.max' => 'Le nom du membre ne doit pas dépasser 255 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format jpeg, png ou jpg.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $password = $this->generateSecurePassword(8);

        $companyMember = CompanyMember::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'company_id' => $company->id,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');

            $companyMember->image = asset('storage/' . $imagePath);
            $companyMember->save();
        }

        Mail::to($request->email)->send(new InvitationEmail($company->name, $request->email, $password));

        return response()->json([
            'success' => true,
            'message' => 'Membre ajouté avec succès.',
            'company_member' => $companyMember
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

    public function update(Request $request, $companyId, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Le nom du membre est obligatoire.',
            'name.string' => 'Le nom du membre doit être une chaîne de caractères.',
            'name.max' => 'Le nom du membre ne doit pas dépasser 255 caractères.',

            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format jpeg, png, jpg, gif ou svg.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $companyMember = CompanyMember::findOrFail($memberId);

        if (!$companyMember || $companyMember->company_id !== $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Le membre n\'existe pas ou n\'appartient pas à cette entreprise.',
            ], 404);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');

            if ($companyMember->image && Storage::disk('public')->exists($companyMember->image)) {
                Storage::disk('public')->delete($companyMember->image);
            }

            $companyMember->image = asset('storage/' . $imagePath);
        }


        $companyMember->update([
            'name' => $request->name,
            'password' => $request->password ? Hash::make($request->password) : $companyMember->password,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Membre mis à jour avec succès.',
            'company_member' => $companyMember,
        ]);
    }


    public function destroy($companyId, $memberId)
    {
        $companyMember = CompanyMember::findOrFail($memberId);

        if ($companyMember->company_id !== $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Le membre n\'appartient pas à cette entreprise.',
            ], 404);
        }

        $companyMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membre supprimé avec succès.',
        ]);
    }

    public function companyMembers($companyId)
    {
        $members = CompanyMember::where('company_id', $companyId)->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }
}
