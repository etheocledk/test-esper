<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    if (Contact::where('email', $value)->where('company_id', auth()->user()->id)->exists()) {
                        $fail('Cet email a déjà été utilisé pour un autre contact de votre entreprise.');
                    }
                }
            ]
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'email.regex' => 'L\'email fourni n\'est pas valide.',
            'fullname.string' => 'Le nom complet doit être une chaîne de caractères.',
            'fullname.max' => 'Le nom complet ne doit pas dépasser 255 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $request->merge(['company_id' => auth()->user()->id]);

        $contact = Contact::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $contact,
        ], 201);
    }

    public function getSingleContact($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact non trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string|max:255',
        ], [
            'fullname.string' => 'Le nom complet doit être une chaîne de caractères.',
            'fullname.max' => 'Le nom complet ne doit pas dépasser 255 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $contact = Contact::findOrFail($id);
        $contact->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $contact,
        ], 200);
    }

    public function destroy($id)
    {
        $contact = Contact::where('id', $id)->where('company_id', auth()->user()->id)->first();
        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact non trouvé.',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Le contact a été supprimé avec succès.',
        ], 200);
    }

    public function index()
    {
        $contacts = Contact::where('company_id', auth()->user()->id)->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ], 200);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string|max:255',
            'email' => 'nullable'
        ], [
            'fullname.string' => 'Le nom complet doit être une chaîne de caractères.',
            'fullname.max' => 'Le nom complet ne doit pas dépasser 255 caractères.',
            'email.email' => 'L\'email doit être valide.',
            'email.regex' => 'L\'email fourni n\'est pas valide.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $query = Contact::query();

        if ($request->has('fullname') && $request->fullname) {
            $query->where('company_id', auth()->user()->id)->where('fullname', 'like', '%' . $request->fullname . '%');
        }

        if ($request->has('email') && $request->email) {
            $query->where('company_id', auth()->user()->id)->where('email', 'like', '%' . $request->email . '%');
        }

        $contacts = $query->get();

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ], 200);
    }
}
