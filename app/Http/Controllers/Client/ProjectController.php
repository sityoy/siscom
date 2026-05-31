<?php

namespace App\Http\Controllers\Client;

use App\Models\Project;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        $projects = Project::where(
            'client_id',
            $client->id
        )->latest()->paginate(10);

        return view(
            'clients.projects.index',
            compact('projects')
        );
    }
}
