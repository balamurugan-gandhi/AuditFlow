<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(): JsonResponse
    {
        $clients = $this->clientService->getAllClients(auth()->user());
        return response()->json($clients);
    }

    public function show(int $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        return response()->json($client);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:50',
            'filing_cycle' => 'nullable|string|in:Yearly,Quarterly,Monthly',
            'address' => 'nullable|string',
        ]);

        $client = $this->clientService->createClient($validated);
        return response()->json($client, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:50',
            'filing_cycle' => 'nullable|string|in:Yearly,Quarterly,Monthly',
            'address' => 'nullable|string',
        ]);

        $updated = $this->clientService->updateClient($id, $validated);
        if (!$updated) {
            return response()->json(['message' => 'Client not found or update failed'], 404);
        }
        return response()->json(['message' => 'Client updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->clientService->deleteClient($id);
        if (!$deleted) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        return response()->json(['message' => 'Client deleted successfully']);
    }
}
