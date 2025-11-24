<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->has('role')) {
            $query->role($request->role);
        } else {
            $query->role(['employee', 'manager']);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['manager', 'employee'])],
            'clients' => 'nullable|array',
            'clients.*' => 'exists:clients,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'doj' => 'nullable|date',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'doj' => $validated['doj'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        if (isset($validated['clients'])) {
            $user->clients()->sync($validated['clients']);
        }

        return response()->json($user->load(['roles', 'clients']), 201);
    }

    public function show(int $id)
    {
        $user = User::with(['roles', 'clients', 'files.client'])->find($id);
        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, int $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => ['nullable', Rule::in(['manager', 'employee'])],
            'clients' => 'nullable|array',
            'clients.*' => 'exists:clients,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'doj' => 'nullable|date',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        if (isset($validated['clients'])) {
            $user->clients()->sync($validated['clients']);
        }

        return response()->json($user->load(['roles', 'clients']));
    }

    public function destroy(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
