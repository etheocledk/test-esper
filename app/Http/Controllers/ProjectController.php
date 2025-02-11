<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyProject;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'theme' => 'required|string',
            'association_name' => 'required|string',
            'city' => 'required|string',
            'events_offered' => 'required|string',
            'iban' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|mimes:mp4|max:50240',
        ], [
            'title.required' => 'Le titre du projet est obligatoire.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',

            'theme.required' => 'Le thème du projet est requis.',
            'theme.string' => 'Le thème doit être une chaîne de caractères.',

            'association_name.required' => 'Le nom de l\'association est requis.',
            'association_name.string' => 'Le nom de l\'association doit être une chaîne de caractères.',

            'city.required' => 'La ville est requise.',
            'city.string' => 'La ville doit être une chaîne de caractères.',

            'events_offered.required' => 'Veuillez indiquer les événements proposés.',
            'events_offered.string' => 'Les événements proposés doivent être une chaîne de caractères.',

            'iban.required' => 'L\'IBAN est obligatoire.',
            'iban.string' => 'L\'IBAN doit être une chaîne de caractères.',

            'description.required' => 'La description du projet est nécessaire.',
            'description.string' => 'La description doit être une chaîne de caractères.',

            'image.image' => 'L\'image doit être au format JPG, PNG ou JPEG.',

            'video.mimes' => 'Le fichier vidéo doit être au format mp4.',
            'video.max' => 'La taille de la vidéo ne doit pas dépasser 50 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        } else {
            $imagePath = null;
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');
        } else {
            $videoPath = null;
        }

        $project = Project::create([
            'title' => $request->title,
            'theme' => $request->theme,
            'association_name' => $request->association_name,
            'city' => $request->city,
            'events_offered' => $request->events_offered,
            'iban' => $request->iban,
            'description' => $request->description,
            'image' => $imagePath ? asset('storage/' . $imagePath) : null,
            'video' => $videoPath ? asset('storage/' . $videoPath) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le projet a été ajouté avec succès.',
            'data' => $project,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'theme' => 'required|string',
            'association_name' => 'required|string',
            'city' => 'required|string',
            'events_offered' => 'required|string',
            'iban' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|mimes:mp4|max:50240',
        ], [
            'title.required' => 'Le titre du projet est obligatoire.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',

            'theme.required' => 'Le thème du projet est requis.',
            'theme.string' => 'Le thème doit être une chaîne de caractères.',

            'association_name.required' => 'Le nom de l\'association est requis.',
            'association_name.string' => 'Le nom de l\'association doit être une chaîne de caractères.',

            'city.required' => 'La ville est requise.',
            'city.string' => 'La ville doit être une chaîne de caractères.',

            'events_offered.required' => 'Veuillez indiquer les événements proposés.',
            'events_offered.string' => 'Les événements proposés doivent être une chaîne de caractères.',

            'iban.required' => 'L\'IBAN est obligatoire.',
            'iban.string' => 'L\'IBAN doit être une chaîne de caractères.',

            'description.required' => 'La description du projet est nécessaire.',
            'description.string' => 'La description doit être une chaîne de caractères.',

            'image.image' => 'L\'image doit être au format JPG, PNG, GIF ou SVG.',

            'video.mimes' => 'Le fichier vidéo doit être au format mp4, avi, mov, ou mkv.',
            'video.max' => 'La taille de la vidéo ne doit pas dépasser 10 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $project = Project::findOrFail($id);

        $imagePath = null;
        if ($request->hasFile('image')) {
            if ($project->image) {
                Storage::delete($project->image);
            }
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            if ($project->video) {
                Storage::delete($project->video);
            }
            $videoPath = $request->file('video')->store('videos', 'public');
        }

        $project->update([
            'title' => $request->title,
            'theme' => $request->theme,
            'association_name' => $request->association_name,
            'city' => $request->city,
            'events_offered' => $request->events_offered,
            'iban' => $request->iban,
            'description' => $request->description,
            'image' => $imagePath ? asset('storage/' . $imagePath) : $project->image,
            'video' => $videoPath ? asset('storage/' . $videoPath) : $project->video,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le projet a été mis à jour avec succès.',
            'data' => $project,
        ]);
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Le projet avec l\'ID spécifié n\'existe pas.',
            ], 404);
        }

        if ($project->image && Storage::exists($project->image)) {
            Storage::delete($project->image);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Le projet a été supprimé avec succès.',
        ]);
    }

    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Le projet avec l\'ID spécifié n\'existe pas.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->paginate(10);

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun projet trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }


    public function searchByTitle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ], [
            'title.required' => 'Le titre du projet est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $title = $request->input('title');

        $projects = Project::where('title', 'like', '%' . $title . '%')->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun projet trouvé avec ce titre.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function addFiscalReceipt(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'receipt' => 'required|file|mimes:pdf,jpeg,jpg,png',
        ], [
            'receipt.required' => 'Le reçu fiscal est requis.',
            'receipt.file' => 'Le fichier doit être un document.',
            'receipt.mimes' => 'Le reçu fiscal doit être un fichier au format PDF ou une image (JPEG, PNG, GIF, SVG).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $project = Project::findOrFail($id);

        if ($request->file('receipt')->getMimeType() === 'application/pdf') {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        } else {
            $receiptPath = $request->file('receipt')->store('images', 'public');
        }

        $project->update([
            'fiscal_receipt' => asset('storage/' . $receiptPath),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le reçu fiscal a été ajouté avec succès.',
            'project' => $project,
        ]);
    }

    public function getCurrentProjects($companyId)
    {
        $company = Company::findOrFail($companyId);

        $currentProjects = $company->projects()
            ->where(function ($query) {
                $query->where('is_validated', false);
            })
            ->orWhere(function ($query) {
                $query->where('is_validated', true)
                    ->whereNull('campaign_id');
            })
            ->where('company_id', $company->id)
            ->get();


        return response()->json([
            'success' => true,
            'current_projects' => $currentProjects,
        ]);
    }

    public function getCompanyProjects($companyId)
    {
        $company = Company::findOrFail($companyId);

        $currentProjects = $company->projects()
            ->whereNull('campaign_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $currentProjects,
        ]);
    }

    public function assignProjects(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
        ], [
            'project_ids.required' => 'Les projets sont requis.',
            'project_ids.array' => 'Les projets doivent être fournis sous forme de tableau.',
            'project_ids.*.exists' => 'Un ou plusieurs des projets sélectionnés n\'existent pas.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'L\'entreprise spécifiée n\'existe pas.',
            ], 404);
        }

        $assignedProjects = $company->projects()
            ->where(function ($query) {
                $query->where('is_validated', false);
            })
            ->orWhere(function ($query) {
                $query->where('is_validated', true)
                    ->whereNull('campaign_id');
            })
            ->count();

        $remainingProjects = 3 - $assignedProjects;

        if ($remainingProjects <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'L\'entreprise a déjà 3 projets attribués.',
            ], 400);
        }

        $alreadyAssigned = [];
        $assignedNow = [];
        foreach ($request->project_ids as $projectId) {
            $isAlreadyAssigned = CompanyProject::where('company_id', $companyId)
                ->where('project_id', $projectId)
                ->exists();

            if ($isAlreadyAssigned) {
                $alreadyAssigned[] = $projectId;
                continue;
            }

            if (count($assignedNow) < $remainingProjects) {
                CompanyProject::create([
                    'company_id' => $companyId,
                    'project_id' => $projectId,
                    'is_validated' => false,
                ]);
                $assignedNow[] = $projectId;
            }
        }

        $message = 'Les projets ont été attribués avec succès.';
        $status = true;

        if (!empty($alreadyAssigned)) {
            $message = 'Les projets ont été déjà attribués';
            $status = false;
        }

        return response()->json([
            'success' => $status ? $status : true,
            'message' => $message,
        ]);
    }


    public function validateProjects(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
        ], [
            'project_ids.required' => 'Les projets sont requis.',
            'project_ids.array' => 'Les projets doivent être fournis sous forme de tableau.',
            'project_ids.*.exists' => 'Un ou plusieurs des projets sélectionnés n\'existent pas.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'L\'entreprise spécifiée n\'existe pas.',
            ], 404);
        }

        $assignedProjects = $company->projects()
            ->whereNull('campaign_id')
            ->get();

        $validatedProjectsCount = $assignedProjects->filter(function ($project) {
            return $project->pivot->is_validated;
        })->count();

        if ($validatedProjectsCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'L\'entreprise a déjà 3 projets validés pour cette année.',
            ], 400);
        }

        $projectsToValidate = [];

        foreach ($request->project_ids as $projectId) {
            $assignedProject = $company->projects()
                ->where('projects.id', $projectId)
                ->whereNull('campaign_id')
                ->first();

            if (!$assignedProject) {
                return response()->json([
                    'success' => false,
                    'message' => "Le projet $projectId n'est pas attribué à cette entreprise ou n'existe pas.",
                ], 400);
            }

            if ($validatedProjectsCount + count($projectsToValidate) >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas valider plus de projets. L\'entreprise peut seulement valider 3 projets.',
                ], 400);
            }

            $projectsToValidate[] = $assignedProject;
        }

        foreach ($projectsToValidate as $project) {
            $company->projects()->updateExistingPivot($project->id, ['is_validated' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Les projets ont été validés avec succès.',
        ]);
    }
    public function removeProject(Request $request, $companyId, $projectId)
    {
        $company = Company::findOrFail($companyId);

        $assignedProject = $company->projects()->where('project_id', $projectId)->first();
        if (!$assignedProject) {
            return response()->json([
                'success' => false,
                'message' => 'Le projet n\'est pas attribué à cette entreprise.',
            ], 400);
        }

        if ($assignedProject->campany_id != null) {
            return response()->json([
                'success' => false,
                'message' => 'Le projet est déjà associé à une campagne.',
            ], 400);
        }

        $company->projects()->detach($projectId);

        return response()->json([
            'success' => true,
            'message' => 'Le projet a été supprimé avec succès.',
        ]);
    }

    public function getProjectsToAssign()
    {
        $projects = Project::whereDoesntHave('companyProjects')
            ->orderBy('created_at', 'desc')->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun projet disponible à attribuer.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }


    public function searchProjectsToAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ], [
            'title.required' => 'Le titre du projet est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $title = $request->input('title');

        $projects = Project::whereDoesntHave('companyProjects')
            ->where('title', 'like', '%' . $title . '%')
            ->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun projet trouvé avec ce titre.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }
}
