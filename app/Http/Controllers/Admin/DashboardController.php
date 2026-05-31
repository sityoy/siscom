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

        $monthlyPayments = Payment::select(

                DB::raw('MONTH(payment_date) as month'),

                DB::raw('SUM(amount) as total')

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

        $totalCashback = Invoice::sum('cashback');

        $totalPayments = Payment::count();

        $pendingRevenue = Invoice::where(
            'status',
            'unpaid'
        )->sum('grand_total');

        $latestInvoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get();

        $latestPayments = Payment::with('invoice')
            ->latest()
            ->take(5)
            ->get();

        $topClients = Client::withCount('projects')
            ->orderByDesc('projects_count')
            ->take(5)
            ->get();

        $openTickets = Ticket::where(
            'status',
            '!=',
            'closed'
        )->count();

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
                    'monthlyPayments',

                    'totalCashback',
                    'totalPayments',
                    'pendingRevenue',

                    'latestInvoices',
                    'latestPayments',

                    'topClients',

                    'openTickets',
                    'latestTickets',

                    'projectCompletionRate',
                )

            );

    }

}
