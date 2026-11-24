<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolProposalController extends Controller
{
    public const PACKAGES = [
        'portal' => ['Portal Sekolah', 'Absensi siswa dan guru, keuangan, sarana dan prasarana, kesiswaan, kurikulum, dan perpustakaan.'],
        'cbt' => ['CBT Sekolah', 'Bank soal, penjadwalan ujian, akun peserta, pemantauan pelaksanaan, dan koreksi hasil ujian.'],
        'pilihan' => ['CBT + Modul Pilihan', 'Layanan CBT dengan modul portal yang dipilih sekolah.'],
        'lengkap' => ['Portal Sekolah + CBT', 'Seluruh modul Portal Sekolah serta layanan ujian CBT untuk sekolah.'],
    ];

    public function form()
    {
        return view('admin.school-registrations.proposal', [
            'packages' => self::PACKAGES,
            'modules' => array_diff_key(SchoolRegistrationController::MODULES, ['cbt'=>true]),
        ]);
    }

    public function pdf(Request $request)
    {
        $modules = array_diff_key(SchoolRegistrationController::MODULES, ['cbt'=>true]);
        $data = $request->validate([
            'school'=>'required|string|max:150', 'signer'=>'nullable|string|max:100',
            'title'=>'required|string|max:80', 'date'=>'required|date_format:Y-m-d',
            'period'=>'required|in:bulan,semester,tahun', 'packages'=>'required|array|min:1',
            'packages.*'=>['required','distinct',Rule::in(array_keys(self::PACKAGES))],
            'prices'=>'nullable|array','prices.*'=>'nullable|integer|min:0|max:9999999999999',
            'modules'=>'nullable|array', 'modules.*'=>['distinct',Rule::in(array_keys($modules))],
        ]);
        $company = CompanySetting::first();
        $logo = $company?->logo ? storage_path('app/public/'.$company->logo) : '';
        return Pdf::loadView('admin.school-registrations.proposal-pdf', [
            'data'=>$data, 'packages'=>array_intersect_key(self::PACKAGES, array_flip($data['packages'])),
            'modules'=>array_intersect_key($modules, array_flip($data['modules'] ?? [])),
            'company'=>$company, 'logoPath'=>is_file($logo) ? $logo : public_path('logo/siscombg2.png'),
        ])->setPaper('a4')->download('Proposal-Layanan-Sekolah.pdf');
    }
}
