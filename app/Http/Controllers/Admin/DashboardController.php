<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\Ticket;


class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();

        $totalClients = Client::count();

        $totalProjects = Project::count();
        $totalIncome = Payment::sum('amount');
        $currentMonthIncome = Payment::whereMonth(
            'payment_date',
            now()->month
        )->sum('amount');

        $paidInvoices = Invoice::where(
            'status',
            'paid'
        )->count();

        $unpaidInvoices = Invoice::where(
            'status',
            'unpaid'
        )->count();

        $partialInvoices = Invoice::where(
            'status',
            'partial'
        )->count();

        $monthlyPayments = Payment::select(

            DB::raw(
                "DATE_FORMAT(payment_date,'%Y-%m') as month"
            ),

            DB::raw(
                "SUM(amount) as total"
            )

        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $completedProjects = Project::where(
            'status',
            'completed'
        )->count();

        $pendingProjects = Project::where(
            'status',
            'pending'
        )->count();

        $totalCashback = Invoice::get()
        ->sum(function ($invoice) {

            return (
                $invoice->grand_total *
                $invoice->cashback / 100
            );

        });


        $totalPaymentAmount = Payment::sum('amount');

        $pendingRevenue = Invoice::whereIn(
            'status',
            [
                'unpaid',
                'partial'
            ]
        )->sum('grand_total');

        $latestInvoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get();

        $latestPayments = Payment::with('invoice')
            ->latest()
            ->take(5)
            ->get();

        $topClients = Client::withSum(
            'invoices',
            'grand_total'
        )
        ->withCount('projects')
        ->orderByDesc('invoices_sum_grand_total')
        ->limit(5)
        ->get();

        $openTickets = Ticket::where(
            'status',
            '!=',
            'closed'
        )->count();

        $closedTickets = Ticket::where(
            'status',
            'closed'
        )->count();

        $progressProjects = Project::where(
            'status',
            'progress'
        )->count();

        $rewardClients = Invoice::where(
            'cashback',
            '>',
            0
        )->distinct('client_id')
        ->count('client_id');

        $latestTickets = Ticket::with('client')
            ->latest()
            ->take(5)
            ->get();

        $projectCompletionRate =
            $totalProjects > 0
            ? round(
                ($completedProjects / $totalProjects) * 100
            )
            : 0;
        $overdueInvoices = Invoice::where(
            'due_date',
            '<',
            now()
        )
        ->whereNotIn(
            'status',
            ['paid','cancelled']
        )
        ->count();

        $totalInvoices = Invoice::count();



        return view(
            'admin.dashboard',
            compact(

                    'totalUsers',
                    'totalClients',
                    'totalProjects',
                    'completedProjects',
                    'pendingProjects',
                    'totalIncome',

                    'currentMonthIncome',
                    'paidInvoices',
                    'unpaidInvoices',
                    'partialInvoices',
                    'monthlyPayments',

                    'totalCashback',

                    'totalPaymentAmount',
                    'pendingRevenue',

                    'latestInvoices',
                    'latestPayments',

                    'topClients',

                    'openTickets',
                    'closedTickets',
                    'latestTickets',

                    'projectCompletionRate',

                    'totalInvoices',
                    'progressProjects',
                    'rewardClients',
                    'overdueInvoices',
                )

            );

    }

}
