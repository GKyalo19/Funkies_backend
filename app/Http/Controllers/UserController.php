<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('role')->paginate();
        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'user_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        if ($request->hasFile('user_photo')) {
            $filename = $request->file('user_photo')->store('posts', 'public');
        } else {
            $filename = Null;
        }

        $validated['user_photo'] = $filename;


        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        return response()->json($user->load('role'), 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        // Load the relationship and get the user data
        $userData = $user->load('role')->toArray();

        $userData['user_photo_url'] = $user->user_photo
            ?asset('storage/' . $user->user_photo)
            :null;

        return response()->json($userData);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'user_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'role_id' => ['sometimes', 'exists:roles,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('user_photo')) {
            $filename = $request->file('user_photo')->store('posts', 'public');
        } else {
            $filename = Null;
        }
        $user->user_photo = $filename;

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        return response()->json($user->load('role'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(null, 204);
    }
}
