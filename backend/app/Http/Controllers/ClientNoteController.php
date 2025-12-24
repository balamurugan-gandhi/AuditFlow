<?php

namespace App\Http\Controllers;

use App\Models\ClientNote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientNoteController extends Controller
{
    /**
     * Display a listing of notes for a specific client.
     */
    public function index(Request $request, int $clientId): JsonResponse
    {
        $assessmentYear = $request->query('assessment_year');
        $query = ClientNote::with('user')->where('client_id', $clientId);
        if ($assessmentYear) {
            $query->where('assessment_year', $assessmentYear);
        }
        $notes = $query->orderBy('created_at', 'desc')->get();
        return response()->json($notes);
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request, int $clientId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'assessment_year' => 'required|string|max:20',
        ]);

        $note = ClientNote::create([
            'client_id' => $clientId,
            'reason' => $validated['reason'],
            'assessment_year' => $validated['assessment_year'],
            'user_id' => auth()->id(),
        ]);

        $note->load('user');

        return response()->json($note, 201);
    }

    /**
     * Display the specified note.
     */
    public function show(int $clientId, int $noteId): JsonResponse
    {
        $note = ClientNote::with('user')
            ->where('client_id', $clientId)
            ->findOrFail($noteId);

        return response()->json($note);
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, int $clientId, int $noteId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $note = ClientNote::where('client_id', $clientId)->findOrFail($noteId);
        $note->update($validated);

        $note->load('user');

        return response()->json($note);
    }

    /**
     * Remove the specified note.
     */
    public function destroy(int $clientId, int $noteId): JsonResponse
    {
        $note = ClientNote::where('client_id', $clientId)->findOrFail($noteId);
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
