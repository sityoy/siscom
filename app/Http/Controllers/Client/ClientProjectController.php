<?php

namespace App\Http\Controllers\Client;

use App\Models\Project;
use App\Http\Controllers\Controller;

class ClientProjectController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $projects = Project::where(

            'client_id',

            $client->id

        )->latest()->paginate(10);

        return view(

            'clients.projects.index',

            compact('projects')

        );
    }

    public function show(Project $project)
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        // CEK KEPEMILIKAN PROJECT
        if ($project->client_id != $client->id) {

            abort(403);

        }

        $project->load([

            'client',
            'files',
            'invoices'

        ]);

        return view(

            'clients.projects.show',

            compact('project')

        );
    }
}
