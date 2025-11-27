<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function store(Request $request, $vacancyId)
    {
        $vacancy = Vacancy::findOrFail($vacancyId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'message' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'privacy_agreement' => 'accepted',
        ]);

        if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'The email must be a valid email address.',
                'errors' => ['email' => ['The email must be a valid email address.']]
            ], 422);
        }

        $data = $request->only(['name', 'email', 'phone', 'message']);
        $data['privacy_agreement'] = $request->boolean('privacy_agreement');
        $data['vacancy_id'] = $vacancy->id;

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('applications/resumes', 'public');
            $data['resume'] = $path;
        }

        $application = Application::create($data);

        return response()->json($application, 201);
    }

    public function index()
    {
        $applications = Application::with('vacancy')->get();
        return response()->json($applications);
    }

    public function show($id)
    {
        $application = Application::with('vacancy')->findOrFail($id);
        return response()->json($application);
    }

    public function download($filename)
    {
        $path = 'applications/resumes/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => Storage::disk('public')->size($path)
        ];
        
        return response()->download(
            storage_path('app/public/' . $path),
            $filename,
            $headers
        );
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $application->delete();

        return response()->json(null, 204);
    }
}