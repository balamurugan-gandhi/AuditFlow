<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        // Get all users with 'employee' or 'manager' role
        $employees = User::role(['employee', 'manager'])->with('roles')->get();
        return response()->json($employees);
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        if (isset($validated['clients'])) {
            $user->clients()->sync($validated['clients']);
        }

        return response()->json($user->load(['roles', 'clients']), 201);
    }

    public function show(int $id)
    {
        $user = User::with(['roles', 'clients'])->find($id);
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
