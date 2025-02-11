<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('categorie')->paginate(10);

        if ($faqs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune FAQ trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ], 200);
    }

    public function showByCategory($categorie)
    {
        $faqs = Faq::where('categorie', $categorie)->get();

        if ($faqs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune FAQ trouvée pour cette catégorie.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ], 200);
    }

    public function edit($id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ non trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faq,
        ], 200);
    }

    public function destroy($id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ non trouvée.',
            ], 404);
        }

        if ($faq->icone && Storage::exists('public/' . $faq->icone)) {
            Storage::delete('public/' . $faq->icone);
        }

        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ supprimée avec succès.',
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'reponse' => 'required|string',
            'categorie' => 'required|string|max:255',
            'icone' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'titre.required' => 'Le titre est requis.',
            'reponse.required' => 'La réponse est requise.',
            'categorie.required' => 'La catégorie est requise.',
            'icone.image' => 'L\'icône doit être une image.',
            'icone.mimes' => 'L\'icône doit être au format jpeg, png ou jpg.',
            'icone.max' => 'L\'icône ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $faq = new Faq();
        $faq->titre = $request->titre;
        $faq->reponse = $request->reponse;
        $faq->categorie = $request->categorie;

        if ($request->hasFile('icone')) {
            $iconPath = $request->file('icone')->store('icons', 'public');
            $faq->icone = asset('storage/' . $iconPath);
        }

        $faq->save();

        return response()->json([
            'success' => true,
            'message' => 'FAQ ajoutée avec succès.',
            'data' => $faq,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'reponse' => 'required|string',
            'categorie' => 'required|string|max:255',
            'icone' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'titre.required' => 'Le titre est requis.',
            'reponse.required' => 'La réponse est requise.',
            'categorie.required' => 'La catégorie est requise.',
            'icone.image' => 'L\'icône doit être une image.',
            'icone.mimes' => 'L\'icône doit être au format jpeg, png ou jpg.',
            'icone.max' => 'L\'icône ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ non trouvée.',
            ], 404);
        }

        $faq->titre = $request->titre;
        $faq->reponse = $request->reponse;
        $faq->categorie = $request->categorie;

        if ($request->hasFile('icone')) {
            if ($faq->icone && Storage::exists('public/' . $faq->icone)) {
                Storage::delete('public/' . $faq->icone);
            }

            $iconPath = $request->file('icone')->store('icons', 'public');
            $faq->icone = asset('storage/' . $iconPath);
        }

        $faq->save();

        return response()->json([
            'success' => true,
            'message' => 'FAQ mise à jour avec succès.',
            'data' => $faq,
        ], 200);
    }
}
