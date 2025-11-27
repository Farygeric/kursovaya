<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,rar|max:20480', 
            'privacy_agreement' => 'accepted',
        ]);

        if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'The email must be a valid email address.',
                'errors' => ['email' => ['The email must be a valid email address.']]
            ], 422);
        }

        $data = $request->only(['name', 'email', 'subject', 'message', 'privacy_agreement']);
        $data['status'] = 'новый';

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('proposals/files', 'public');
            $data['file_src'] = $path;
        }

        $proposal = Proposal::create($data);

        return response()->json($proposal, 201);
    }

    public function index()
    {
        $proposals = Proposal::all();
        return response()->json($proposals);
    }

    public function show($id)
    {
        $proposal = Proposal::findOrFail($id);
        return response()->json($proposal);
    }

    public function updateStatus(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        $request->validate([
            'status' => 'required|in:новый,в работе,отклонено,принято',
        ]);

        $proposal->update(['status' => $request->status]);

        return response()->json($proposal);
    }

    public function downloadFile($id)
    {
        $proposal = Proposal::findOrFail($id);
        if (empty($proposal->file_src)) {
            return response()->json(['message' => 'No file attached'], 404);
        }
        $relativePath = $proposal->file_src;
        $disk = Storage::disk('public');
        if (!$disk->exists($relativePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        $originalName = basename($relativePath);
        $fileSize = $disk->size($relativePath);
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $originalName . '"',
            'Content-Length' => $fileSize
        ];
        $filePath = $disk->path($relativePath);
        return response()->download($filePath, $originalName, $headers);
    }

    public function destroy($id)
    {
        $proposal = Proposal::findOrFail($id);
        $proposal->delete();
        return response()->json(null, 204);
    }
}