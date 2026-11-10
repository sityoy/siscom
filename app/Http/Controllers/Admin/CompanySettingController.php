<?php

namespace App\Http\Controllers\Admin;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $setting = \App\Models\CompanySetting::first();

        if (!$setting) {

            $setting = \App\Models\CompanySetting::create([

                'company_name' => 'SIS.COM',

                'bank_jakarta' => '300-23-31341-1',
                'bank_jakarta_name' => 'TIO IRFAN ANTONI',

                'bank_mandiri' => '11800-1378-1322',
                'bank_mandiri_name' => 'TIO IRFAN ANTONI',

                'bank_bca' => '5310-74114-2',
                'bank_bca_name' => 'TIO IRFAN ANTONI',

            ]);

        }

        return view(
            'admin.settings.index',
            compact('setting')
        );
    }



    public function update(Request $request)
    {
        $setting = CompanySetting::firstOrCreate(
            [],
            [
                'company_name' => 'SIS.COM'
            ]
        );

        $request->validate([

            'company_name' => 'required|max:255',

            'legal_name' => 'nullable|max:255',

            'company_email' => 'nullable|email',

            'company_phone' => 'nullable|max:30',

            'company_address' => 'nullable',

            'nib' => 'nullable|max:50',
            'npwp' => 'nullable|max:50',
            'tax_status' => 'required|in:pkp,non_pkp',
            'income_tax_scheme' => 'required|in:umkm_final,normal',
            'income_tax_rate' => 'required|numeric|min:0|max:100',
            'pp55_active' => 'nullable|boolean',
            'pp55_bpe_number' => 'nullable|max:100',
            'pp55_case_number' => 'nullable|max:100',
            'pp55_effective_date' => 'nullable|date',
            'pp55_document' => 'nullable|file|mimes:pdf|max:5120',
            'director_name' => 'nullable|max:255',
            'director_title' => 'nullable|max:255',
            'city' => 'nullable|max:100',

            'logo' =>'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',


        ]);

        $logo = $setting->logo;

        if($request->hasFile('logo')){

            if(
                $setting->logo &&
                Storage::disk('public')->exists(
                    $setting->logo
                )
            ){
                Storage::disk('public')->delete(
                    $setting->logo
                );
            }

            $logo = $request
                ->file('logo')
                ->store(
                    'company',
                    'public'
                );
        }

        $pp55Document = $setting->pp55_document;

        if ($request->hasFile('pp55_document')) {
            if (
                $setting->pp55_document
                && Storage::disk('public')->exists($setting->pp55_document)
            ) {
                Storage::disk('public')->delete($setting->pp55_document);
            }

            $pp55Document = $request
                ->file('pp55_document')
                ->store('company/tax', 'public');
        }



        $setting->update([

            'company_name' => $request->company_name,
            'legal_name' => $request->legal_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'company_address' => $request->company_address,

            'nib' => $request->nib,
            'npwp' => $request->npwp,
            'tax_status' => $request->tax_status,
            'income_tax_scheme' => $request->income_tax_scheme,
            'income_tax_rate' => $request->income_tax_rate,
            'pp55_active' => $request->boolean('pp55_active'),
            'pp55_bpe_number' => $request->pp55_bpe_number,
            'pp55_case_number' => $request->pp55_case_number,
            'pp55_effective_date' => $request->pp55_effective_date,
            'pp55_document' => $pp55Document,
            'director_name' => $request->director_name,
            'director_title' => $request->director_title,
            'city' => $request->city,

            'bank_jakarta' => $request->bank_jakarta,
            'bank_jakarta_name' => $request->bank_jakarta_name,

            'bank_mandiri' => $request->bank_mandiri,
            'bank_mandiri_name' => $request->bank_mandiri_name,

            'bank_bca' => $request->bank_bca,
            'bank_bca_name' => $request->bank_bca_name,

            'website' => $request->website,

            'instagram' => $request->instagram,
            'facebook' => $request->facebook,
            'linkedin' => $request->linkedin,

            'logo' => $logo,

        ]);

        return back()->with(
            'success',
            'Setting berhasil diupdate'
        );
    }
}
