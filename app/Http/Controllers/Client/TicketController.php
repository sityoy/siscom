<?php

namespace App\Http\Controllers\Client;

use App\Models\Ticket;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TicketMessage;
use App\Models\AdminNotification;
use App\Models\Notification;

class TicketController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $tickets = Ticket::where(

            'client_id',

            $client->id

        )->latest()->paginate(10);

        return view(

            'clients.tickets.index',

            compact('tickets')

        );
    }

    public function create()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $projects = Project::where(

            'client_id',

            $client->id

        )->get();

        return view(

            'clients.tickets.create',

            compact('projects')

        );
    }

    public function store(Request $request)
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $request->validate([

            'subject' => 'required',

            'message' => 'required',

        ]);

        $ticket = Ticket::create([

            'client_id' => $client->id,

            'project_id' => $request->project_id,

            'subject' => $request->subject,

            'message' => $request->message,

            'status' => 'open',
        ]);

        AdminNotification::create([
            'title' => 'Ticket Baru',
            'message' =>
                $client->name .
                ' membuat ticket baru: ' .
                $ticket->subject,
            'is_read' => false,
        ]);

        return redirect()

            ->route('client.tickets.index')

            ->with(

                'success',

                'Ticket berhasil dibuat'

            );
    }

    public function show(Ticket $ticket)
    {
        $client = auth()->user()->client;

        if (
            !$client ||
            $ticket->client_id != $client->id
        ) {

            abort(403);

        }

        $ticket->load([

            'client',

            'project',

            'messages' => function ($query) {

                $query->orderBy(
                    'created_at',
                    'asc'
                );

            }

        ]);

        return view(

            'clients.tickets.show',

            compact('ticket')

        );
    }

    public function reply(
        Request $request,
        Ticket $ticket
    )

    {
        $client = auth()->user()->client;
            if ($ticket->status == 'closed') {

            return back()->with(

                'error',

                'Ticket sudah ditutup'

            );

        }

        if (
            !$client ||
            $ticket->client_id != $client->id
        ) {

            abort(403);

        }

        $request->validate([

            'message' => 'required'

        ]);

        TicketMessage::create([

            'ticket_id' => $ticket->id,

            'sender_type' => 'client',

            'sender_name' => auth()->user()->name,

            'message' => $request->message,

        ]);

        $ticket->update([

            'status' => 'open'

        ]);

        AdminNotification::create([
            'title' => 'Balasan Ticket',
            'message' =>
                $client->name .
                ' membalas ticket: ' .
                $ticket->subject,
            'is_read' => false,
        ]);

        return back()->with(

            'success',

            'Balasan berhasil dikirim'

        );
    }
}
