<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryAllocationController extends Controller
{
    private function breakdown(Invoice $invoice): array
    {
        $payments = BigDecimal::of((string) $invoice->payments()->whereDate('payment_date', '<=', today())->sum('amount'));
        $capital = $invoice->capitalContributions()->whereNull('voided_at')->whereDate('contribution_date', '<=', today())->sum('amount');
        $profit = $payments->minus((string) $capital);
        $base = BigDecimal::max('0', $profit);
        $remaining = $base;
        $rows = DB::table('salary_allocations')->where('invoice_id', $invoice->id)->orderBy('recipient')->get();
        foreach ($rows as $row) {
            $value = BigDecimal::of((string) $row->value);
            $row->amount = $row->mode === 'percent'
                ? $base->multipliedBy($value)->dividedBy(100, 2, RoundingMode::HalfUp)
                : $value;
            $remaining = $remaining->minus($row->amount);
        }

        return compact('payments', 'capital', 'profit', 'base', 'remaining', 'rows');
    }

    public function show(Invoice $invoice)
    {
        return view('admin.capital.salary', ['invoice' => $invoice] + $this->breakdown($invoice));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'recipient' => 'required|string|max:100',
            'mode' => 'required|in:percent,fixed',
            'value' => ['required','numeric','decimal:0,2','min:0.01',
                $request->input('mode') === 'percent' ? 'max:100' : 'max:9999999999999.99'],
        ]);
        DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            DB::table('salary_allocations')->updateOrInsert(
                ['invoice_id' => $invoice->id, 'recipient' => $data['recipient']],
                ['mode' => $data['mode'], 'value' => $data['value']]
            );
            $result = $this->breakdown($invoice);
            if ($result['remaining']->isNegative() || $result['rows']->where('mode', 'percent')->sum('value') > 100) {
                throw ValidationException::withMessages(['value' => 'Total pembagian melebihi laba tersedia atau 100%.']);
            }
        }, 3);

        return back()->with('success', 'Pembagian gaji tersimpan.');
    }

    public function destroy(Invoice $invoice, int $allocation)
    {
        DB::transaction(function () use ($invoice, $allocation) {
            Invoice::lockForUpdate()->findOrFail($invoice->id);
            abort_unless(DB::table('salary_allocations')->where('invoice_id', $invoice->id)->where('id', $allocation)->delete(), 404);
        });
        return back()->with('success', 'Alokasi gaji dihapus.');
    }
}
