<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignContact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CampaignContactController extends Controller
{
    public function getContactsForCampaign()
    {
        $contacts = CampaignContact::where('company_id', Auth::user()->id)
            ->whereNull('campaign_id')
            ->with('contact')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
        ], [
            'contact_id.required' => 'Le contact est requis.',
            'contact_id.exists' => 'Le contact spécifié n\'existe pas.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $existingContact = CampaignContact::where('contact_id', $request->contact_id)
            ->where('company_id', Auth::user()->id)
            ->whereNull('campaign_id')
            ->first();

        if ($existingContact) {
            return response()->json([
                'success' => false,
                'message' => 'Ce contact a déjà été ajouté.',
            ], 400);
        }

        $campaignContact = CampaignContact::create([
            'contact_id' => $request->contact_id,
            'company_id' => Auth::user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact ajouté avec succès.',
            'data' => $campaignContact,
        ], 201);
    }

    public function destroy($contactId)
    {
        $campaignContact = CampaignContact::where('contact_id', $contactId)
            ->where('company_id', Auth::user()->id)
            ->whereNull('campaign_id')
            ->first();

        if (!$campaignContact) {
            return response()->json([
                'success' => false,
                'message' => 'Le contact est déjà associé à une campagne.',
            ], 404);
        }

        $campaignContact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact supprimé de la campagne.',
        ], 200);
    }
}
