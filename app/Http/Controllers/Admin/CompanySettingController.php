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

            'company_email' => 'nullable|email',

            'company_phone' => 'nullable|max:30',

            'company_address' => 'nullable',

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



        $setting->update([

            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'company_address' => $request->company_address,

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
