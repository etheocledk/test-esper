<?php

namespace App\Http\Controllers;


use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
            'lien_hypertexte' => 'required|url',
            'icone' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'message.required' => 'Le champ message est requis.',
            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.max' => 'Le message ne doit pas dépasser 255 caractères.',

            'intitule.required' => 'Le champ intitulé est requis.',
            'intitule.string' => 'L\'intitulé doit être une chaîne de caractères.',
            'intitule.max' => 'L\'intitulé ne doit pas dépasser 255 caractères.',

            'lien_hypertexte.required' => 'Le champ lien hypertexte est requis.',
            'lien_hypertexte.url' => 'Le lien hypertexte doit être une URL valide.',

            'icone.image' => 'L\'icône doit être une image valide (JPEG, PNG ou JPG).',
            'icone.mimes' => 'L\'icône doit être un fichier avec l\'extension JPEG, PNG ou JPG.',
            'icone.max' => 'L\'icône ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $iconePath = null;
        if ($request->hasFile('icone')) {
            $iconePath = $request->file('icone')->store('icons', 'public');
        }

        $activity = Activity::create([
            'message' => $request->message,
            'intitule' => $request->intitule,
            'lien_hypertexte' => $request->lien_hypertexte,
            'icone' => $iconePath ? asset('storage/' . $iconePath) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activité ajoutée avec succès.',
            'data' => $activity,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
            'lien_hypertexte' => 'required|url',
            'icone' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'message.required' => 'Le champ message est requis.',
            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.max' => 'Le message ne doit pas dépasser 255 caractères.',

            'intitule.required' => 'Le champ intitulé est requis.',
            'intitule.string' => 'L\'intitulé doit être une chaîne de caractères.',
            'intitule.max' => 'L\'intitulé ne doit pas dépasser 255 caractères.',

            'lien_hypertexte.required' => 'Le champ lien hypertexte est requis.',
            'lien_hypertexte.url' => 'Le lien hypertexte doit être une URL valide.',

            'icone.image' => 'L\'icône doit être une image valide (JPEG, PNG ou JPG).',
            'icone.mimes' => 'L\'icône doit être un fichier avec l\'extension JPEG, PNG ou JPG.',
            'icone.max' => 'L\'icône ne doit pas dépasser 2 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $activity = Activity::findOrFail($id);

        if ($request->hasFile('icone')) {
            if ($activity->icone && Storage::exists('public/' . $activity->icone)) {
                Storage::delete('public/' . $activity->icone);
            }

            $iconePath = $request->file('icone')->store('icons', 'public');
            $activity->icone = asset('storage/' . $iconePath);
        }

        $activity->update([
            'message' => $request->message,
            'intitule' => $request->intitule,
            'lien_hypertexte' => $request->lien_hypertexte,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activité mise à jour avec succès.',
            'data' => $activity,
        ]);
    }

    public function index()
    {
        $activities = Activity::orderBy('created_at', 'desc')->paginate(10);

        if ($activities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune activité trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function edit($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'L\'activité demandée n\'a pas été trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activity,
        ], 200);
    }

    public function destroy($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'L\'activité à supprimer n\'a pas été trouvée.',
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activité supprimée avec succès.',
        ]);
    }
}
