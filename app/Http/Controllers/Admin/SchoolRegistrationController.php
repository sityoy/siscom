<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Validation\ValidationException;
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
                })->orderByDesc('id')->paginate(15, ['id','name','code','contact','npsn'], 'pending');
            $history = DB::table('school_approvals as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.approved_by')
                ->orderByDesc('a.id')->paginate(15, ['a.school_code','a.client_label as client','u.name as operator','a.until_date','a.modules','a.approved_at'], 'history');
        } catch (QueryException $e) {
            report($e);
            return response()->view('admin.school-registrations.index', ['unavailable' => true], 503);
        }
        $clients = Client::orderBy('company')->orderBy('name')->get(['id','company','name','package_name','subscription_end']);
        return view('admin.school-registrations.index', [
            'users' => User::role('client')->orderBy('name')->get(['id','name','email']),
            'pending' => $pending, 'history' => $history, 'clients' => $clients, 'modules' => self::MODULES,
        ]);
    }

    private function serviceRules(): array
    {
        return [
            'npsn' => ['required','string','regex:/^[A-Z0-9]{8}$/D'],
            'until_date' => ['required','date_format:Y-m-d','after_or_equal:'.now('Asia/Jakarta')->toDateString()],
            'modules' => ['required','array','min:1'],
            'modules.*' => ['required','distinct',Rule::in(array_keys(self::MODULES))],
        ];
    }

    private function checkNpsn($entry, string $npsn): void
    {
        if ($entry && $entry->npsn !== null && $entry->npsn !== $npsn) {
            throw ValidationException::withMessages(['npsn'=>'NPSN berbeda dari sekolah yang dipilih. Periksa kode dan NPSN.']);
        }
        $matches = DB::table(self::SCHOOLS)->where('npsn', $npsn);
        if ($entry) {
            $matches->where('id', '<>', $entry->id);
        }
        if ($matches->exists()) {
            throw ValidationException::withMessages(['npsn'=>'NPSN sudah ada. Gunakan kode sekolah yang tercatat atau periksa daftar menunggu verifikasi.']);
        }
    }

    public function link(Request $request)
    {
        if (is_string($request->input('npsn'))) {
            $request->merge(['npsn' => strtoupper(trim($request->input('npsn')))]);
        }
        $rules = $this->serviceRules();
        if ($request->filled('code')) {
            unset($rules['modules'], $rules['modules.*'], $rules['until_date']);
        }
        $data = $request->validate($rules + [
            'user_id'=>'required|integer',
            'name'=>'required_without:code|nullable|string|max:160',
            'code'=>['nullable','string','max:32'],
        ]);
        try {
            $code = DB::transaction(function () use ($request,$data) {
                $user = User::role('client')->lockForUpdate()->findOrFail($data['user_id']);
                $client = Client::where('user_id',$user->id)->first();
                $entry = null;
                if (!empty($data['code'])) {
                    $entry = DB::table(self::SCHOOLS)->where('code',strtolower($data['code']))->lockForUpdate()->first();
                    if (!$entry || !$entry->active) {
                        throw ValidationException::withMessages(['code'=>'Gunakan kode sekolah yang aktif. Untuk pendaftaran tertunda gunakan bagian Menunggu verifikasi.']);
                    }
                    $approval = DB::table('school_approvals')->where('portal_school_id', $entry->id)->first();
                    if (($entry->siscom_user_id && $entry->siscom_user_id != $user->id) ||
                        ($approval?->client_id && !Client::whereKey($approval->client_id)->where('user_id',$user->id)->exists())) {
                        throw ValidationException::withMessages(['user_id'=>'Sekolah ini telah terhubung dengan klien lain.']);
                    }
                }
                $this->checkNpsn($entry, $data['npsn']);
                $identity = ['npsn'=>$data['npsn'], 'siscom_user_id'=>$user->id];
                if ($entry) {
                    DB::table(self::SCHOOLS)->where('id',$entry->id)->update($identity);
                    return $entry->code;
                }
                $code = 'sis-'.bin2hex(random_bytes(4));
                $service = ['until_date'=>$data['until_date'],'modules'=>implode(',', $data['modules'])];
                $id = DB::table(self::SCHOOLS)->insertGetId($identity + $service + [
                    'code'=>$code,'name'=>$data['name'],'contact'=>$user->email,'active'=>true,
                ]);
                DB::table('school_approvals')->insert($service + [
                    'portal_school_id'=>$id,'school_code'=>$code,'client_id'=>$client?->id,'client_label'=>$client?->company ?: $user->name,
                    'approved_by'=>$request->user()->id,'approved_at'=>now(),
                ]);
                return $code;
            }, 3);
        } catch (QueryException $error) {
            report($error);
            return back()->withErrors(['portal'=>'Hubungan belum tersimpan. Periksa NPSN, migrasi, dan izin database.'])->withInput();
        }
        return back()->with('success','Akun SISCOM terhubung. Kode sekolah '.strtoupper($code).'.');
    }

    public function approve(Request $request, int $school)
    {
        if (is_string($request->input('npsn'))) {
            $request->merge(['npsn' => strtoupper(trim($request->input('npsn')))]);
        }
        $data = $request->validate($this->serviceRules() + [
            'client_id' => ['nullable','integer',Rule::exists('clients','id')],
            'user_id' => ['nullable','integer'],
        ]);

        try {
            $result = DB::transaction(function () use ($data, $school, $request) {
                $entry = DB::table(self::SCHOOLS)->where('id', $school)->lockForUpdate()->first();
                abort_unless($entry, 404);
                if ($entry->active || $entry->until_date !== null ||
                    DB::table('school_approvals')->where('portal_school_id', $school)->exists()) {
                    return 'Pendaftaran ini sudah diproses. Periksa status sekolah sebelum mengambil tindakan lain.';
                }
                $this->checkNpsn($entry, $data['npsn']);
                $client = !empty($data['client_id']) ? Client::lockForUpdate()->findOrFail($data['client_id']) : null;
                $owner = $client ? User::role('client')->find($client->user_id) : null;
                if (!empty($data['user_id'])) {
                    $owner = User::role('client')->findOrFail($data['user_id']);
                    if ($client && $client->user_id != $owner->id) {
                        throw ValidationException::withMessages(['user_id'=>'Pengguna yang dipilih berbeda dari penanggung jawab klien.']);
                    }
                }
                $modules = implode(',', $data['modules']);
                DB::table('school_approvals')->insert([
                    'portal_school_id' => $school, 'school_code' => $entry->code, 'client_id' => $client?->id,
                    'client_label' => $client?->company ?: ($client?->name ?? $entry->name),
                    'approved_by' => $request->user()->id, 'approved_at' => now(),
                    'until_date' => $data['until_date'], 'modules' => $modules,
                ]);
                DB::table(self::SCHOOLS)->where('id', $school)->update([
                    'npsn' => $data['npsn'], 'siscom_user_id' => $owner?->id,
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
