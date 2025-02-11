<?php

namespace App\Http\Controllers;

use App\Models\BillingInformation;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
{
    public function updateBillingInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'iban' => 'required|string',
            'bic_swift' => 'required|string',
            'account_number' => 'required|string',
        ], [
            'bank_name.required' => 'Le nom de la banque est obligatoire.',
            'iban.required' => 'L\'IBAN est obligatoire.',
            'bic_swift.required' => 'Le code BIC/SWIFT est obligatoire.',
            'account_number.required' => 'Le numéro de compte est obligatoire.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $company = Auth::user();
            $billingInfo = BillingInformation::where('company_id', $company->id)->first();

            if ($billingInfo) {
                $billingInfo->update([
                    'bank_name' => $request->bank_name,
                    'iban' => $request->iban,
                    'bic_swift' => $request->bic_swift,
                    'account_number' => $request->account_number,
                ]);
            } else {
                $billingInfo = BillingInformation::create([
                    'bank_name' => $request->bank_name,
                    'iban' => $request->iban,
                    'bic_swift' => $request->bic_swift,
                    'account_number' => $request->account_number,
                    'company_id' => $company->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Les informations de facturation ont été mises à jour avec succès.',
                'billing_information' => $billingInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour des informations de facturation. Veuillez réessayer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($companyId)
    {
        $company = Company::findOrFail($companyId);

        $billingInfo = BillingInformation::where('company_id', $company->id)->first();

        if (!$billingInfo) {
            $billingInfo = BillingInformation::create([
                'bank_name' => null,
                'iban' => null,
                'bic_swift' => null,
                'account_number' => null,
                'company_id' => $company->id
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $billingInfo,
        ]);
    }
}
