<?php

namespace App\Http\Controllers\Admin;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $setting = CompanySetting::first();

        $setting->update(

            $request->only([

                'company_name',

                'company_email',

                'company_phone',

                'company_address',

                'bank_jakarta',
                'bank_jakarta_name',

                'bank_mandiri',
                'bank_mandiri_name',

                'bank_bca',
                'bank_bca_name',

            ])

        );

        return back()->with(
            'success',
            'Setting berhasil diupdate'
        );
    }
}
