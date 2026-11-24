<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortalLoginController extends Controller
{
    private function schools(Request $request)
    {
        return DB::table('db_portalsiscom.schools')
            ->where('siscom_user_id', $request->user()->id)
            ->where('active', true)->whereDate('until_date', '>=', now('Asia/Jakarta')->toDateString());
    }

    public function choose(Request $request)
    {
        $data = $request->validate(['state' => ['required', 'regex:/^[a-f0-9]{64}$/D']]);
        $request->session()->put('portal_login_state', $data['state']);
        if (!$request->user()) {
            return redirect()->route('login');
        }
        abort_unless($request->user()->hasRole('client'), 403, 'Masuk menggunakan akun klien sekolah SISCOM.');
        $schools = $this->schools($request)->orderBy('name')->get(['id','code','name','npsn']);
        return response()->view('portal-login', compact('schools'))->header('Cache-Control','no-store');
    }

    public function issue(Request $request)
    {
        $data = $request->validate(['school_id' => 'required|integer', 'state' => ['required','regex:/^[a-f0-9]{64}$/D']]);
        $state = $request->session()->get('portal_login_state');
        abort_unless(is_string($state) && hash_equals($state, $data['state']), 403, 'Mulai kembali dari portal sekolah.');
        $school = $this->schools($request)->where('id', $data['school_id'])->first();
        abort_unless($school, 403, 'Akun ini belum terhubung dengan sekolah aktif tersebut.');
        $ticket = bin2hex(random_bytes(32));
        DB::table('db_portalsiscom.siscom_login_tickets')->insert([
            'digest' => hash('sha256', $ticket), 'state_hash' => hash('sha256', $state),
            'school_id' => $school->id, 'siscom_user_id' => $request->user()->id,
            'name' => $request->user()->name, 'expires_at' => time() + 90,
        ]);
        $request->session()->forget('portal_login_state');
        return redirect()->away('https://sityoyitsolutions.com/masuk/siscom/kembali?ticket='.$ticket)
            ->header('Cache-Control','no-store')->header('Referrer-Policy','no-referrer');
    }
}
