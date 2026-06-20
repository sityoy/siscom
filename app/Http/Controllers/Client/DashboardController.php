<?php

namespace App\Http\Controllers\Client;

use App\Models\Invoice;
use App\Models\Project;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403, 'Client belum terhubung');

        }

        $projects = Project::where(
            'client_id',
            $client->id
        )->count();

        $activeProjects = Project::where(
            'client_id',
            $client->id
        )
        ->where('status', '!=', 'completed')
        ->count();

        $invoices = Invoice::where(
            'client_id',
            $client->id
        )->count();

        $payments = Payment::whereHas(
            'invoice',
            function ($query) use ($client) {

                $query->where(
                    'client_id',
                    $client->id
                );

            }
        )->count();

        $openTickets = Ticket::where(
            'client_id',
            $client->id
        )
        ->where('status', '!=', 'closed')
        ->count();

        $notifications = Notification::where(
            'client_id',
            $client->id
        )
        ->where('is_read', false)
        ->count();


        return view(
            'clients.dashboard',
            compact(
                'projects',
                'activeProjects',
                'invoices',
                'payments',
                'openTickets',
                'notifications'
            )
        );
    }


}
