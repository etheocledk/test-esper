<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Company;
use App\Models\CompanyProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppController extends Controller
{
    public function getDashboardStatistics()
    {
        $topDonors = Campaign::where('status', 'completed')
            ->select('company_id', DB::raw('SUM(amount) as total_donated'))
            ->groupBy('company_id')
            ->orderByDesc('total_donated')
            ->limit(5)
            ->get()
            ->map(function ($campaign) {
                $company = Company::find($campaign->company_id);
                return [
                    'company' => $company,
                    'total_donated' => $campaign->total_donated,
                ];
            });


        $topProjects = CampaignContact::whereNotNull('has_voted')
            ->where('has_voted', true)
            ->select('project_id', DB::raw('count(*) as vote_count'))
            ->groupBy('project_id')
            ->orderByDesc('vote_count')
            ->limit(3)
            ->get()
            ->map(function ($campaignContact) {
                $project = $campaignContact->project;
                return [
                    'project' => $project,
                    'vote_count' => $campaignContact->vote_count,
                ];
            });


        $totalProjects = CompanyProject::count();
        $totalCompanies = Company::count();
        $totalDonations = Campaign::where('status', 'completed')->get()->sum(function ($campaign) {
            return floatval($campaign->amount);
        });

        $votesByMonth = CampaignContact::where('has_voted', true)
            ->select(DB::raw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as vote_count'))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'top_donors' => $topDonors,
                'top_projects' => $topProjects,
                'total_projects' => $totalProjects,
                'total_companies' => $totalCompanies,
                'total_donations' => $totalDonations,
                'votes_by_month' => $votesByMonth
            ]
        ]);
    }


    public function getCustomerCompanyStatistics()
    {
        $company_id = auth()->user()->id;

        $lastCampaign = CompanyProject::where('company_id', $company_id)
            ->orderBy('created_at', 'desc')
            ->first();


        if (!$lastCampaign) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune campagne trouvée pour cette compagnie.'
            ], 404);
        }

        $campaign_id = $lastCampaign->campaign_id;


        $donationAmount = CompanyProject::where('campaign_id', $campaign_id)->sum('amount');

        $fiscalReduction = $donationAmount * 0.6;
        $fiscalReduction = number_format($fiscalReduction, 2, '.', '');


        $themes = CompanyProject::where('campaign_id', $campaign_id)
            ->join('projects', 'company_project.project_id', '=', 'projects.id')
            ->select(
                'projects.theme',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('projects.theme')
            ->orderBy('total', 'desc')
            ->get();

        $themes = $themes->toArray();
        $topThemes = array_slice($themes, 0, 3);
        $otherThemesTotal = array_sum(array_column(array_slice($themes, 3), 'total'));

        if ($otherThemesTotal > 0) {
            $topThemes[] = [
                'theme' => 'Autres',
                'total' => $otherThemesTotal,
            ];
        } else {
            $topThemes[] = [
                'theme' => 'Autres',
                'total' => 0,
            ];
        }

        $currentYear = now()->year;
        $donationEvolution = CompanyProject::where('company_id', $company_id)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(amount) as total_donations')
            )
            ->whereYear('created_at', '>=', $currentYear - 5)
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->keyBy('year');

        $donationEvolutionData = [];
        for ($year = $currentYear - 5; $year <= $currentYear; $year++) {
            $donationEvolutionData[] = [
                'year' => $year,
                'total_donations' => $donationEvolution[$year]->total_donations ?? 0,
            ];
        }

        $company = Company::find($company_id);
        $campaign_id = $lastCampaign->campaign_id;
        $projectsValidatedCount = $company->projects()
            ->where(function ($query) use ($campaign_id) {
                $query->where('is_validated', true)
                    ->whereNull('campaign_id')
                    ->orWhere('campaign_id', $campaign_id);
            })
            ->count();

        $campaign = Campaign::where('company_id', $company_id)
            ->latest()
            ->first();

        $projectsValidated = $projectsValidatedCount >= 2;

        $showResults = false;
        if ($campaign) {
            if ($campaign->status == 'completed') {
                $showResults = true;
            } else {
                $showResults = false;
            }
        }

        if ($campaign) {
            $projectsValidated = true;
        }

        $campaign_status = $campaign->status == "running" ? true : false;
        if($campaign->status == 'completed'){
            $campaign_status = true;
        }

        $response = [
            'success' => true,
            'data' => [
                'campaign' => [
                    'details' => [
                        'amount' => $donationAmount > 0 ? $donationAmount : 0,
                        'fiscal_reduction' => $donationAmount > 0 ? $fiscalReduction : 0
                    ]
                ],
                'impact' => [
                    'themes_statistics' => $topThemes
                ],
                'evolution' => [
                    'donation_over_years' => $donationEvolutionData
                ],
                'progression' => [
                    'projects_validated' => $projectsValidated,
                    'campaign_status' => $campaign_status,
                    'show_results' => $showResults
                ]
            ]
        ];

        return response()->json($response);
    }


    public function getImpactStatistics(Request $request)
    {
        $company_id = auth()->user()->id;
        $year = $request->input('year');

        $companyDonationsQuery = CompanyProject::where('is_validated', true)->where('company_id', $company_id);

        if ($year && $request->input('year') != "tous") {
            $companyDonationsQuery->whereYear('company_project.created_at', $year);
        }

        if ($companyDonationsQuery->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune donnée trouvée pour cette entreprise.',
                'data' => [
                    'dons' => [
                        'total' => 0,
                        'nombre_de_projets' => 0,
                    ],
                    'associations' => [],
                    'repartitions' => [
                        'themes' => [],
                        'localisations' => [],
                    ],
                    'taux_de_votes' => [],
                    'total_dons_states' => [
                        'company_donations' => [
                            'details' => [],
                        ],
                        'platform_donations' => [
                            'details' => [],
                        ],
                    ],
                ]
            ]);
        }

        $totalCompanyDonations = $companyDonationsQuery->sum('amount');
        $totalCompanyProjects = $companyDonationsQuery->distinct('project_id')->count('project_id');


        $associations = $companyDonationsQuery
            ->join('projects as p1', 'company_project.project_id', '=', 'p1.id')
            ->select('p1.association_name', DB::raw('COUNT(*) as total'), DB::raw('SUM(company_project.amount) as total_donations'))
            ->groupBy('p1.association_name')
            ->orderByRaw('SUM(company_project.amount) DESC')
            ->get();

        if ($associations->isEmpty()) {
            $associations = [];
        }

        $themes = $companyDonationsQuery
            ->join('projects as p3', 'company_project.project_id', '=', 'p3.id')
            ->select('p3.theme', DB::raw('COUNT(*) as total'), DB::raw('SUM(company_project.amount) as total_donations'))
            ->groupBy('p3.theme')
            ->orderByRaw('SUM(company_project.amount) DESC')
            ->orderBy('total', 'desc')
            ->get();

        $topThemes = $themes->take(3);

        $otherThemesCount = $themes->slice(3)->sum('total');

        $groupedThemes = $topThemes->toArray();

        if ($otherThemesCount > 0) {
            $groupedThemes[] = [
                'theme' => 'Autres',
                'total' => $otherThemesCount,
            ];
        }

        $localisations = $companyDonationsQuery
            ->join('projects as p4', 'company_project.project_id', '=', 'p4.id')
            ->select('p4.city', DB::raw('COUNT(*) as total'), DB::raw('SUM(company_project.amount) as total_donations'))
            ->groupBy('p4.city')
            ->orderByRaw('SUM(company_project.amount) DESC')
            ->orderBy('total', 'desc')
            ->get();

        $topLocalisations = $localisations->take(3);

        $otherLocalisationsCount = $localisations->slice(3)->sum('total');

        $groupedLocalisations = $topLocalisations->toArray();

        if ($otherLocalisationsCount > 0) {
            $groupedLocalisations[] = [
                'city' => 'Autres',
                'total' => $otherLocalisationsCount,
            ];
        }

        $recentYears = now()->year - 2;
        $yearsRange = collect([$recentYears, now()->year - 1, now()->year]);

        $votes = CampaignContact::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total_votes')
        )
            ->where('company_id', $company_id)
            ->where('has_voted', true)
            ->whereYear('created_at', '>=', $recentYears)
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        $votesByYear = $votes->pluck('total_votes', 'year')->toArray();

        $votes = $yearsRange->map(function ($year) use ($votesByYear) {
            return [
                'year' => $year,
                'total_votes' => $votesByYear[$year] ?? 0,
            ];
        });

        $companyMonthlyDonations = $companyDonationsQuery
            ->select(
                DB::raw("DATE_FORMAT(company_project.created_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->where('company_project.created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw("DATE_FORMAT(company_project.created_at, '%Y-%m')"))
            ->orderBy('month', 'asc')
            ->get();

        $months = collect([]);
        $startDate = now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $formattedMonth = $currentMonth->format('M') . $currentMonth->format('Y');

            $existingData = $companyMonthlyDonations->firstWhere('month', $currentMonth->format('Y-m'));

            $months->push([
                'month' => $formattedMonth,
                'total' => $existingData ? $existingData->total : 0,
            ]);
        }

        $companyMonthlyDonations = $months;

        $platformMonthlyDonations = DB::table('company_project')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('AVG(amount) as average')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month', 'asc')
            ->get();

        $months = collect([]);
        $startDate = now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $formattedMonth = $currentMonth->format('M') . $currentMonth->format('Y');
            $existingData = $platformMonthlyDonations->firstWhere('month', $currentMonth->format('Y-m'));

            $months->push([
                'month' => $formattedMonth,
                'average' => $existingData ? $existingData->average : 0,
            ]);
        }

        $platformMonthlyDonations = $months;

        return response()->json([
            'success' => true,
            'data' => [
                'dons' => [
                    'total' => $totalCompanyDonations,
                    'nombre_de_projets' => $totalCompanyProjects,
                ],
                'associations' => $associations,
                'repartitions' => [
                    'themes' => $groupedThemes,
                    'localisations' => $groupedLocalisations,
                ],
                'taux_de_votes' => $votes,
                'total_dons_states' => [
                    'company_donations' => [
                        'details' => $companyMonthlyDonations,
                    ],
                    'platform_donations' => [
                        'details' => $platformMonthlyDonations,
                    ],
                ],
            ],
        ]);
    }
}
