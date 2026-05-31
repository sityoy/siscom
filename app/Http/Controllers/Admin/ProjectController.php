<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProjectFile;
use App\Models\Notification;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('client')
            ->latest()
            ->paginate(10);

        return view(
            'admin.projects.index',
            compact('projects'),
        );
    }

    public function create()
    {
        $clients = Client::all();

        return view(
            'admin.projects.create',
            compact('clients'),
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'client_id' => 'required',

            'title' => 'required',

        ]);

        $project = Project::create([

            'client_id'   => $request->client_id,

            'title'       => $request->title,

            'description' => $request->description,

            'status'      => $request->status,

        ]);

        Notification::create([

            'client_id' => $project->client_id,

            'title' => 'Project Baru',

            'message' =>
                'Project baru dibuat: ' .
                $project->title,



        ]);

        return redirect()
            ->route('projects.index')
            ->with(
                'success',
                'Project berhasil dibuat',
            );
    }



    public function edit(Project $project)
    {
        $clients = Client::all();

        return view(
            'admin.projects.edit',
            compact(
                'project',
                'clients',
            )
        );
    }

    public function update(
        Request $request,
        Project $project
    ) {

        $project->update($request->all());

        return redirect()
            ->route('projects.index')
            ->with(
                'success',
                'Project berhasil diupdate',
            );
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with(
            'success',
            'Project berhasil dihapus',
        );
    }
}
