<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolRegistrationController extends Controller
{
    // Same MySQL instance as SISCOM. Only this tenant directory is needed.
    private const SCHOOLS = 'db_portalsiscom.schools';
    private const MODULES = [
        'absensi' => 'Absensi siswa', 'guru' => 'Absensi guru', 'keuangan' => 'Keuangan',
        'sarpras' => 'Sarana dan prasarana', 'kesiswaan' => 'Kesiswaan',
        'kurikulum' => 'Kurikulum', 'perpustakaan' => 'Perpustakaan',
    ];

    public function index()
    {
        try {
            $pending = DB::table(self::SCHOOLS)->where('active', false)->whereNull('until_date')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('school_approvals')
                        ->whereColumn('school_approvals.portal_school_id', self::SCHOOLS.'.id');
                })->orderByDesc('id')->paginate(15, ['id','name','code','contact'], 'pending');
            $history = DB::table('school_approvals as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.approved_by')
                ->orderByDesc('a.id')->paginate(15, ['a.school_code','a.client_label as client','u.name as operator','a.until_date','a.modules','a.approved_at'], 'history');
        } catch (QueryException $e) {
            report($e);
            return response()->view('admin.school-registrations.index', ['unavailable' => true], 503);
        }
        $clients = Client::orderBy('company')->orderBy('name')->get(['id','company','name','package_name','subscription_end']);
        return view('admin.school-registrations.index', [
            'pending' => $pending, 'history' => $history, 'clients' => $clients, 'modules' => self::MODULES,
        ]);
    }

    public function approve(Request $request, int $school)
    {
        $data = $request->validate([
            'client_id' => ['required','integer',Rule::exists('clients','id')],
            'until_date' => ['required','date_format:Y-m-d','after_or_equal:'.now('Asia/Jakarta')->toDateString()],
            'modules' => ['required','array','min:1'],
            'modules.*' => ['required','distinct',Rule::in(array_keys(self::MODULES))],
        ]);

        try {
            $result = DB::transaction(function () use ($data, $school, $request) {
                $entry = DB::table(self::SCHOOLS)->where('id', $school)->lockForUpdate()->first();
                abort_unless($entry, 404);
                if ($entry->active || $entry->until_date !== null ||
                    DB::table('school_approvals')->where('portal_school_id', $school)->exists()) {
                    return 'Pendaftaran ini sudah diproses. Periksa status sekolah sebelum mengambil tindakan lain.';
                }
                $client = Client::lockForUpdate()->findOrFail($data['client_id']);
                $modules = implode(',', $data['modules']);
                DB::table('school_approvals')->insert([
                    'portal_school_id' => $school, 'school_code' => $entry->code, 'client_id' => $client->id,
                    'client_label' => $client->company ?: $client->name,
                    'approved_by' => $request->user()->id, 'approved_at' => now(),
                    'until_date' => $data['until_date'], 'modules' => $modules,
                ]);
                DB::table(self::SCHOOLS)->where('id', $school)->update([
                    'active' => true, 'until_date' => $data['until_date'], 'modules' => $modules,
                ]);
                return $entry->name.' aktif. Sekolah dapat masuk ke portal dengan kode '.strtoupper($entry->code).'.';
            }, 3);
        } catch (QueryException $e) {
            report($e);
            return back()->withErrors(['portal' => 'Aktivasi belum tersimpan. Periksa migrasi SISCOM dan izin database portal.']);
        }
        return redirect()->route('school-registrations.index')->with('success', $result);
    }
}
