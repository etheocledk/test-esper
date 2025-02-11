<?php

namespace App\Http\Controllers;

use App\Mail\CampaignVoteMail;
use App\Models\BillingInformation;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Company;
use App\Models\CompanyProject;
use App\Models\Contact;
use App\Models\Project;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignController extends Controller
{
    public function checkCampaignSteps()
    {
        $userId = auth()->user()->id;
        $company = Company::find($userId);

        $contactsValidatedCount = CampaignContact::where('company_id', $userId)
            ->whereNull('campaign_id')
            ->count();
        $contactsValidated = $contactsValidatedCount >= 2;

        $projectsValidatedCount = $company->projects()
            ->where(function ($query) {
                $query->where('is_validated', true)
                    ->whereNull('campaign_id');
            })
            ->count();

        $projectsValidated = $projectsValidatedCount >= 2;

        $billingInformationFilled = BillingInformation::where('company_id', $userId)
            ->whereNotNull('bank_name')
            ->whereNotNull('iban')
            ->whereNotNull('bic_swift')
            ->whereNotNull('account_number')
            ->where('bank_name', '!=', '')
            ->where('iban', '!=', '')
            ->where('bic_swift', '!=', '')
            ->where('account_number', '!=', '')
            ->exists();

        $latestCampaign = Campaign::where('company_id', $userId)
            ->latest()
            ->first();

        $showResults = null;
        if ($latestCampaign) {
            if ($projectsValidatedCount <= 1 && $latestCampaign->status == 'completed') {
                $showResults = true;
            } elseif ($projectsValidatedCount >= 2 && $latestCampaign->status == 'completed') {
                $showResults = null;
            } elseif ($latestCampaign->status == 'running') {
                $showResults = false;
            }
        }

        $response = [
            'contactsValidated' => $contactsValidated,
            'projectsValidated' => $projectsValidated,
            'billingInformationFilled' => $billingInformationFilled,
        ];

        if ($showResults !== null) {
            $response['showResults'] = $showResults;
        }

        return response()->json($response);
    }

    public function launchCampaign(Request $request)
    {
        $userId = auth()->user()->id;
        $company = Company::find($userId);

        $projects = $company->projects()->where('is_validated', true)
            ->whereNull('campaign_id')->get();

        if ($projects->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Il faut au moins 2 projets validés pour lancer la campagne.',
            ], 400);
        }

        $contacts = CampaignContact::where('company_id', $userId)
            ->whereNull('campaign_id')
            ->get();

        if ($contacts->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Il faut au moins 2 contacts pour lancer la campagne.',
            ], 400);
        }

        if ($company->amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Votre solde est insuffisant. Veuillez contacter l\'administrateur pour recharger votre compte.',
            ], 400);
        }

        $campaign = Campaign::create([
            'company_id' => $company->id,
            'amount' => $company->amount,
            'start_date' => now(),
        ]);

        $company->amount = 0;
        $company->save();

        foreach ($contacts as $contact) {
            $contact->campaign_id = $campaign->id;
            $contact->save();
        }

        foreach ($projects as $project) {
            $projectAssign = CompanyProject::where('project_id', $project->id)
                ->whereNull('campaign_id')
                ->first();
            $projectAssign->campaign_id = $campaign->id;
            $projectAssign->save();
        }

        foreach ($contacts as $contact) {
            $contact = Contact::find($contact->contact_id);
            $url = 'http://localhost:3000/vote/' . $campaign->id . '/' . $contact->id;
            Mail::to($contact->email)->send(new CampaignVoteMail($url, $contact->name, $company->name));
        }

        return response()->json([
            'success' => true,
            'message' => 'La campagne a été lancée avec succès.',
            'data' => $campaign,
        ], 201);
    }

    public function getCampaignStats()
    {
        $userId = auth()->user()->id;

        $campaign = Campaign::where('company_id', $userId)
            ->where('status', 'running')
            ->latest()
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune campagne en cours.',
            ], 404);
        }

        $campaignContacts = CampaignContact::where('company_id', $userId)
            ->where('campaign_id', $campaign->id)
            ->get();

        $totalContacts = $campaignContacts->count();

        $totalVotes = $campaignContacts->where('has_voted', true)->count();

        $voteRate = $totalContacts > 0 ? ($totalVotes / $totalContacts) * 100 : 0;

        $votesByProject = $campaignContacts->where('has_voted', true)->groupBy('project_id')->map(function ($group) {
            return $group->count();
        });

        $projectVotes = $votesByProject->map(function ($voteCount, $projectId) {
            $project = Project::find($projectId);

            return [
                'project_name' => $project,
                'votes_count' => $voteCount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => $campaign,
                'total_contacts' => $totalContacts,
                'total_votes' => $totalVotes,
                'vote_rate' => $voteRate,
                'votes_by_project' => $projectVotes,
            ],
        ]);
    }

    public function getCampaignResults()
    {
        $userId = auth()->user()->id;

        $campaign = Campaign::where('company_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune campagne terminée pour le moment.',
            ], 404);
        }

        $companyProjects = CompanyProject::where('campaign_id', $campaign->id)->get();

        $campaignContacts = CampaignContact::where('campaign_id', $campaign->id)
            ->where('company_id', $userId)
            ->get();

        $totalVotes = $campaignContacts->where('has_voted', true)->count();

        $totalAmount = floatval($campaign->amount);

        $projectResults = [];

        foreach ($companyProjects as $companyProject) {

            $votesForProject = $campaignContacts->where('project_id', $companyProject->project_id)
                ->where('has_voted', true);

            $votesCount = $votesForProject->count();

            $votePercentage = $totalVotes > 0 ? ($votesCount / $totalVotes) * 100 : 0;

            $projectAmount = ($votePercentage / 100) * $totalAmount;

            $project = $companyProject->project;

            $projectResults[] = [
                'project' => $project,
                'votes_count' => $votesCount,
                'vote_percentage' => $votePercentage,
                'project_amount' => $projectAmount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $projectResults,
        ]);
    }

    public function finishCampaign()
    {
        $userId = auth()->user()->id;

        $campaign = Campaign::where('company_id', $userId)
            ->where('status', 'running')
            ->latest()
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campagne non trouvée ou déjà terminée.',
            ], 404);
        }

        $campaign->status = 'completed';
        $campaign->end_date = now();
        $campaign->save();

        $companyProjects = CompanyProject::where('campaign_id', $campaign->id)->get();

        $campaignContacts = CampaignContact::where('campaign_id', $campaign->id)
            ->where('company_id', $userId)
            ->get();

        $totalVotes = $campaignContacts->where('has_voted', true)->count();

        $totalAmount = floatval($campaign->amount);

        if ($totalVotes === 0) {
            $totalProjects = $companyProjects->count();

            if ($totalProjects > 0) {
                $equalAmount = $totalAmount / $totalProjects;

                foreach ($companyProjects as $companyProject) {
                    $companyProject->amount = $equalAmount;
                    $companyProject->save();
                }
            }
        } else {
            foreach ($companyProjects as $companyProject) {

                $votesForProject = $campaignContacts->where('project_id', $companyProject->project_id)
                    ->where('has_voted', true);

                $votesCount = $votesForProject->count();

                $votePercentage = ($totalVotes > 0) ? ($votesCount / $totalVotes) * 100 : 0;

                $projectAmount = ($votePercentage / 100) * $totalAmount;

                $companyProject->amount = $projectAmount;
                $companyProject->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'La campagne a été terminée avec succès.',
            'data' => $campaign,
        ]);
    }

    public function getCampaignProjects($campaignId, $contactId)
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        $projects = CompanyProject::where('campaign_id', $campaignId)
            ->with('project')
            ->get();

        $totalVotes = CampaignContact::where('campaign_id', $campaignId)
            ->where('has_voted', true)
            ->count();

        $votes = CampaignContact::select('project_id', DB::raw('COUNT(*) as total_votes'))
            ->where('campaign_id', $campaignId)
            ->where('has_voted', true)
            ->groupBy('project_id')
            ->get();

        $projectVotes = $projects->map(function ($project) use ($campaignId, $totalVotes) {

            $vote = CampaignContact::where('campaign_id', $campaignId)
                ->where('project_id', $project->project_id)
                ->where('has_voted', true)
                ->count();

            $project->vote_count = $vote ? floatval($vote) : 0;

            if ($totalVotes > 0) {
                $project->vote_percentage = ($project->vote_count / $totalVotes) * 100;
            } else {
                $project->vote_percentage = 0;
            }

            return $project;
        });


        $contact = CampaignContact::where("contact_id", $contactId)->where('campaign_id', $campaignId)->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact non trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'projects' => [
                'data' => $projectVotes,
                'has_voted' => $contact->has_voted,
            ],
        ]);
    }



    public function submitVote(Request $request, $campaignId)
    {
        $request->validate([
            'contact_id' => 'required|exists:campaign_contacts,contact_id',
            'project_id' => 'required|exists:company_project,project_id',
        ]);

        $campaign = Campaign::find($campaignId);

        if (!$campaign || $campaign->status != 'running') {
            return response()->json([
                'success' => false,
                'message' => 'La campagne n\'existe pas ou elle est déjà clôturé.',
            ], 400);
        }

        $contact = CampaignContact::where("contact_id", $request->contact_id)->where('campaign_id', $campaignId)->first();

        if ($contact->has_voted) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà voté.',
            ], 400);
        }

        $contact->project_id = $request->project_id;
        $contact->has_voted = true;
        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Votre vote a été enregistré.',
        ]);
    }

    public function relaunchCampaign($id)
    {
        $campaign = Campaign::where('id', $id)->where('status', 'running')->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campagne non trouvée ou clôturée.',
            ], 404);
        }

        $contacts = CampaignContact::where('campaign_id', $id)
            ->with('contact')
            ->get();

        if ($contacts->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Il faut au moins 2 contacts pour relancer la campagne.',
            ], 400);
        }

        $contactCompany = Company::find($campaign->company_id);

        if (!$contactCompany) {
            return response()->json([
                'success' => false,
                'message' => 'Entreprise associée non trouvée.',
            ], 404);
        }

        foreach ($contacts as $campaignContact) {
            $contact = $campaignContact->contact;

            if ($contact && $contact->email) {
                try {
                    $url = 'http://localhost:3000/vote/' . $campaign->id . '/' . $contact->id;
                    Mail::to($contact->email)->send(new CampaignVoteMail($url, $contact->name, $contactCompany->name));
                } catch (Exception $e) {
                    Log::error("Erreur lors de l'envoi de l'email : {$e->getMessage()}");
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'La campagne a été relancée avec succès.',
        ], 201);
    }
}
