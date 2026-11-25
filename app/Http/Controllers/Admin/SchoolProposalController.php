<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Support\SchoolCbtPricing;
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

    private const DETAILS = [
        'absensi' => 'Impor data siswa, kartu barcode, dan riwayat jam masuk serta pulang.',
        'guru' => 'Absensi guru melalui ponsel dengan titik dan radius yang ditetapkan sekolah.',
        'keuangan' => 'Pembayaran sekolah per kategori, pencatatan pengeluaran, setoran, dan rekap per periode.',
        'sarpras' => 'Inventaris per lokasi, pencatatan sumber dana, stok habis pakai, dan peminjaman barang.',
        'kesiswaan' => 'Catatan siswa, status tindak lanjut, serta pencatatan ketidakhadiran tanpa keterangan.',
        'kurikulum' => 'Program mengajar per guru, kelas, mata pelajaran, dan semester.',
        'perpustakaan' => 'Katalog, stok buku, peminjaman guru dan siswa, serta pemberitahuan jatuh tempo.',
    ];

    private const CBT_DETAILS = [
        'Pengelolaan peserta, kelas, mata pelajaran, serta akun panitia dan guru.',
        'Penjadwalan sesi ujian reguler dan susulan, bank soal, serta pelaksanaan ujian.',
        'Pemantauan peserta, koreksi jawaban, dan rekap hasil sesuai hak akses.',
    ];

    public function form()
    {
        return view('admin.school-registrations.proposal', [
            'packages' => self::PACKAGES,
            'cbtBases' => SchoolCbtPricing::BASES,
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
            'prices'=>'nullable|array:portal,cbt,pilihan,lengkap','prices.*'=>'nullable|integer|min:0|max:9999999999999',
            'cbt_basis'=>['nullable',Rule::in(array_keys(SchoolCbtPricing::BASES))],
            'cbt_units'=>'nullable|integer|min:1|max:100000',
            'cbt_note'=>'nullable|string|max:500',
            'modules'=>'nullable|array', 'modules.*'=>['distinct',Rule::in(array_keys($modules))],
        ]);
        $company = CompanySetting::first();
        $logo = $company?->logo ? storage_path('app/public/'.$company->logo) : '';
        return Pdf::loadView('admin.school-registrations.proposal-pdf', [
            'data'=>$data, 'packages'=>array_intersect_key(self::PACKAGES, array_flip($data['packages'])),
            'modules'=>$modules,
            'selectedModules'=>array_intersect_key($modules, array_flip($data['modules'] ?? [])),
            'details'=>self::DETAILS, 'cbtDetails'=>self::CBT_DETAILS, 'cbtBases'=>SchoolCbtPricing::BASES,
            'company'=>$company, 'logoPath'=>is_file($logo) ? $logo : public_path('logo/siscombg2.png'),
        ])->setPaper('a4')->download('Proposal-Layanan-Sekolah.pdf');
    }
}
