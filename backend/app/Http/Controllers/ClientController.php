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
        // Pre-check for duplicate PAN, GST, TIN numbers
        if ($request->pan_number) {
            $existingPan = \App\Models\Client::where('pan_number', $request->pan_number)->first();
            if ($existingPan) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'pan_number' => ['This PAN number is already registered with another client: ' . $existingPan->business_name]
                    ]
                ], 422);
            }
        }

        if ($request->gst_number) {
            $existingGst = \App\Models\Client::where('gst_number', $request->gst_number)->first();
            if ($existingGst) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'gst_number' => ['This GST number is already registered with another client: ' . $existingGst->business_name]
                    ]
                ], 422);
            }
        }

        if ($request->tin_number) {
            $existingTin = \App\Models\Client::where('tin_number', $request->tin_number)->first();
            if ($existingTin) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'tin_number' => ['This TIN number is already registered with another client: ' . $existingTin->business_name]
                    ]
                ], 422);
            }
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'file_id' => 'required|numeric',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:50',
            'filing_cycle' => 'nullable|string|in:Yearly,Quarterly,Monthly',
            'address' => 'nullable|string',
            'whatsapp_notification_enabled' => 'boolean',
        ]);

        $client = $this->clientService->createClient($validated);
        return response()->json($client, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // Pre-check for duplicate PAN, GST, TIN numbers (excluding current client)
        if ($request->pan_number) {
            $existingPan = \App\Models\Client::where('pan_number', $request->pan_number)
                ->where('id', '!=', $id)
                ->first();
            if ($existingPan) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'pan_number' => ['This PAN number is already registered with another client: ' . $existingPan->business_name]
                    ]
                ], 422);
            }
        }

        if ($request->gst_number) {
            $existingGst = \App\Models\Client::where('gst_number', $request->gst_number)
                ->where('id', '!=', $id)
                ->first();
            if ($existingGst) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'gst_number' => ['This GST number is already registered with another client: ' . $existingGst->business_name]
                    ]
                ], 422);
            }
        }

        if ($request->tin_number) {
            $existingTin = \App\Models\Client::where('tin_number', $request->tin_number)
                ->where('id', '!=', $id)
                ->first();
            if ($existingTin) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => [
                        'tin_number' => ['This TIN number is already registered with another client: ' . $existingTin->business_name]
                    ]
                ], 422);
            }
        }

        $validated = $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'file_id' => 'required|numeric',
            'email' => 'nullable|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'tin_number' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:50',
            'filing_cycle' => 'nullable|string|in:Yearly,Quarterly,Monthly',
            'address' => 'nullable|string',
            'whatsapp_notification_enabled' => 'boolean',
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
