<?php

namespace App\Http\Controllers\Client;

use App\Models\Project;
use App\Http\Controllers\Controller;

class ProjectFileController extends Controller
{
    public function index(Project $project)
    {
        $client = auth()->user()->client;

        if ($project->client_id != $client->id) {

            abort(403);

        }

        $files = $project->files;

        return view(
            'clients.project-files.index',
            compact(
                'project',
                'files'
            )
        );
    }
}
