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

            'budget'      => $request->budget,

            'deadline'    => $request->deadline,

            'status'      => $request->status,

            'progress'    => $request->progress ?? 0,

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

        $status = $request->status;

            if($request->progress >= 100){

                $status = 'completed';

            }
            elseif($request->progress > 0){

                $status = 'progress';

            }
            elseif($request->progress == 0){

                $status = 'pending';

            }

        $project->update([

            'client_id'   => $request->client_id,

            'title'       => $request->title,

            'description' => $request->description,

            'budget'      => $request->budget,

            'deadline'    => $request->deadline,

            'status'      => $status,

            'progress'    => $request->progress ?? 0,

        ]);

        Notification::create([

            'client_id' => $project->client_id,

            'title' => 'Project Diperbarui',

            'message' =>
                'Project "' .
                $project->title .
                '" telah diperbarui.',

        ]);

        return redirect()
            ->route('projects.index')
            ->with(
                'success',
                'Project berhasil diperbarui'
            );
    }

    public function destroy(Project $project)
    {
        ProjectFile::where(
            'project_id',
            $project->id
        )->delete();

        $project->delete();

        return back()->with(
            'success',
            'Project berhasil dihapus',
        );
    }
}
