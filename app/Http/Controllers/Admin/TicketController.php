<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\TicketMessage;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with([

            'client',
            'project'

        ])->latest()->paginate(10);

        return view(

            'admin.tickets.index',

            compact('tickets')

        );
    }

    public function show(Ticket $ticket)
    {
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

            'admin.tickets.show',

            compact('ticket')

        );
    }

    public function updateStatus(
        Request $request,
        Ticket $ticket
    ) {

        $ticket->update([

            'status' => $request->status

        ]);

        Notification::create([

            'client_id' => $ticket->client_id,

            'title' => 'Ticket Ditutup',

            'message' =>
                'Ticket "' .
                $ticket->subject .
                '" telah ditutup oleh Admin.',

        ]);

        return back()->with(

            'success',

            'Status ticket berhasil diupdate'

        );
    }

    public function reply(
        Request $request,
        Ticket $ticket
    ) {
        // $client = auth()->user()->client;
            if ($ticket->status == 'closed') {

            return back()->with(

                'error',

                'Ticket sudah ditutup'

            );

        }

        $request->validate([

            'message' => 'required|string|min:3'

        ]);

        TicketMessage::create([

            'ticket_id' => $ticket->id,

            'sender_type' => 'admin',

            'sender_name' => auth()->user()->name,

            'message' => $request->message,

        ]);

        Notification::create([

            'client_id' => $ticket->client_id,

            'title' => 'Status Ticket Diperbarui',

            'message' =>
                'Status ticket "' .
                $ticket->subject .
                '" diubah menjadi "' .
                strtoupper($request->status) .
                '".',

        ]);



        $request->validate([

            'status' =>
                'required|in:open,progress,closed'

        ]);

        return back()->with(

            'success',

            'Balasan berhasil dikirim'

        );
    }
}
