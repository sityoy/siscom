<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Support\SchoolCbtPricing;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolRegistrationController extends Controller
{
    // Same MySQL instance as SISCOM. Only this tenant directory is needed.
    private const SCHOOLS = 'db_portalsiscom.schools';
    public const MODULES = [
        'absensi' => 'Absensi siswa', 'guru' => 'Absensi guru', 'keuangan' => 'Keuangan',
        'sarpras' => 'Sarana dan prasarana', 'kesiswaan' => 'Kesiswaan',
        'cbt' => 'Ujian CBT', 'kurikulum' => 'Kurikulum', 'perpustakaan' => 'Perpustakaan',
    ];

    public function index()
    {
        try {
            $pending = DB::table(self::SCHOOLS)->where('active', false)->whereNull('until_date')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('school_approvals')
                        ->whereColumn('school_approvals.portal_school_id', self::SCHOOLS.'.id');
                })->orderByDesc('id')->paginate(15, ['id','name','code','contact','npsn','requested_package','requested_modules'], 'pending');
            $registered = DB::table(self::SCHOOLS.' as s')
                ->leftJoin('school_cbt_prices as p', 'p.portal_school_id', '=', 's.id')
                ->whereNotNull('s.until_date')
                ->when(request()->filled('cari'), function ($query) {
                    $term = '%'.mb_substr((string) request()->string('cari'), 0, 80).'%';
                    $query->where(fn ($q) => $q->where('s.name', 'like', $term)->orWhere('s.npsn', 'like', $term)->orWhere('s.code', 'like', $term));
                })->orderBy('s.name')
                ->paginate(15, ['s.id','s.name','s.npsn','s.code','s.active','s.until_date','s.modules','s.cbt_status','p.basis','p.amount','p.units','p.note'], 'registered');
            $history = DB::table('school_approvals as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.approved_by')
                ->leftJoin(self::SCHOOLS.' as s', 's.id', '=', 'a.portal_school_id')
                ->orderByDesc('a.id')->paginate(15, ['a.school_code','a.client_label as client','u.name as operator','a.until_date','a.modules','a.approved_at','s.modules as current_modules','s.cbt_status'], 'history');
        } catch (QueryException $e) {
            report($e);
            return response()->view('admin.school-registrations.index', ['unavailable' => true], 503);
        }
        $clients = Client::orderBy('company')->orderBy('name')->get(['id','company','name','package_name','subscription_end']);
        return view('admin.school-registrations.index', [
            'users' => User::role('client')->orderBy('name')->get(['id','name','email']),
            'pending' => $pending, 'registered' => $registered, 'cbtBases' => SchoolCbtPricing::BASES, 'history' => $history, 'clients' => $clients, 'modules' => self::MODULES,
        ]);
    }

    public function addCbt(Request $request)
    {
        $request->merge(['modules' => ['cbt']]);
        return $this->addModules($request);
    }

    public function addModules(Request $request)
    {
        $data = $request->validate([
            'code'=>'required|string|max:32','npsn'=>['required','regex:/^[a-zA-Z0-9]{8}$/D'],
            'modules'=>'required|array|min:1','modules.*'=>['required','distinct',Rule::in(array_keys(self::MODULES))],
        ]);
        $code = strtolower($data['code']);
        $npsn = strtoupper($data['npsn']);
        DB::transaction(function () use ($code,$npsn,$data) {
            $school = DB::table(self::SCHOOLS)->where('code',$code)->where('npsn',$npsn)->lockForUpdate()->first();
            if (!$school || !$school->active || $school->until_date < now('Asia/Jakarta')->toDateString()) {
                throw ValidationException::withMessages(['code'=>'Periksa kode, NPSN dan masa aktif sekolah.']);
            }
            $modules = array_filter(explode(',', $school->modules));
            $added = array_diff($data['modules'], $modules);
            if (!$added) return;
            $modules = array_merge($modules, $added);
            DB::table(self::SCHOOLS)->where('id',$school->id)->update(['modules'=>implode(',', $modules)]);
        }, 3);
        return back()->with('success','Modul telah ditambahkan untuk sekolah '.strtoupper($code).'.');
    }

    public function updateServices(Request $request, int $school)
    {
        $data = $request->validate([
            'current_modules' => 'nullable|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => ['required','distinct',Rule::in(array_keys(self::MODULES))],
            'basis' => ['nullable',Rule::in(array_keys(SchoolCbtPricing::BASES))],
            'amount' => 'nullable|required_with:basis,units,note|integer|min:0|max:9999999999999',
            'units' => 'nullable|integer|min:1|max:100000',
            'note' => 'nullable|string|max:500',
        ]);
        DB::transaction(function () use ($data, $school) {
            $entry = DB::table(self::SCHOOLS)->where('id', $school)->lockForUpdate()->first();
            abort_unless($entry && $entry->until_date, 404);
            if (array_key_exists('current_modules',$data) && ($data['current_modules'] ?? '') !== $entry->modules) {
                throw ValidationException::withMessages(['modules'=>'Daftar modul telah berubah. Muat ulang halaman sebelum menyimpan.']);
            }
            $known = array_keys(self::MODULES);
            $modules = array_merge($data['modules'] ?? [], array_diff(array_filter(explode(',', $entry->modules)), $known));
            DB::table(self::SCHOOLS)->where('id', $school)->update(['modules'=>implode(',', $modules)]);
            if (in_array('cbt', $modules, true) && isset($data['amount'])) {
                $basis = $data['basis'] ?? 'kesepakatan';
                DB::table('school_cbt_prices')->updateOrInsert(['portal_school_id'=>$school], [
                    'basis'=>$basis, 'amount'=>$data['amount'],
                    'units'=>$basis === 'kesepakatan' ? null : ($data['units'] ?? null),
                    'note'=>$data['note'] ?? null, 'updated_at'=>now(),
                ]);
            }
        }, 3);
        return back()->with('success', 'Layanan sekolah diperbarui.');
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
