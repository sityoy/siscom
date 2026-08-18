<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function index(Project $project)
    {
        $files = $project->files;

        return view(
            'admin.project-files.index',
            compact(
                'project',
                'files'
            )
        );
    }

    public function store(
        Request $request,
        Project $project
    ) {

        $request->validate([

            'file' =>
            'required|file|
            mimes:pdf,doc,docx,xls,xlsx,zip,rar,png,jpg,jpeg|
            max:10240',

        ]);

        $file = $request->file('file');

        $path = $file->store(
            'project-files',
            'public'
        );

        ProjectFile::create([

            'project_id' => $project->id,

            'file_name' => $file->getClientOriginalName(),

            'file_path' => $path,

        ]);

        return back()->with(
            'success',
            'File berhasil diupload'
        );
    }

    public function destroy(ProjectFile $file)
    {
        if (
            $file->file_path &&
            Storage::disk('public')->exists(
                $file->file_path
            )
        ) {

            Storage::disk('public')->delete(
                $file->file_path
            );

        }

        $file->delete();

        return back()->with(
            'success',
            'File project berhasil dihapus'
        );
    }
}
