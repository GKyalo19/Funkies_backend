<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('role')->paginate();
        return response()->json($users);
    }

    public function showUsers(User $user): JsonResponse
    {
        // $this->authorize('view', $user);
        // Load the relationship and get the user data
        $userData = $user;

        $userData['user_photo_url'] = $user->user_photo
            ? asset('storage/' . $user->user_photo)
            : null;

        return response()->json($userData);
    }

    public function getUser($id)
    {

        $fetchedUser = User::findOrFail($id);

        return response()->json($fetchedUser, 200);
    }

    public function updateUser(Request $request, $id)
    {
        $userToUpdate = User::findOrFail($id);

        Log::info($userToUpdate);

        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255'],
                'password' => ['sometimes', 'string', 'min:8'],
                'user_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($request->hasFile('user_photo')) {
                $filename = $request->file('user_photo')->store('users', 'public');

                $userToUpdate->user_photo = $filename;
            }

            $userToUpdate->fill($validated);

            Log::info($validated);

            if (isset($validated['name'])) {
                $userToUpdate->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $userToUpdate->email = $validated['email'];
            }

            if (isset($validated['password'])) {
                $userToUpdate->password = Hash::make($validated['password']);
            }

            Log::info($userToUpdate);
            $userToUpdate->save();

            return response()->json([
                "message" => "User updated successfully",
                "user" => $userToUpdate
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Error updating user",
                "message" => $e->getMessage()
            ], 400);
        }
    }


    public function deleteUser($id)
    {
        $userToDelete = User::findorFail($id);

        if ($userToDelete) {
            try {
                $userToDelete = User::destroy($id);
                return "User deleted successfully";
            } catch (Exception $e) {
                return response()->json([
                    "Error" => "Failed to delete user",
                    "Message" => $e->getMessage()
                ], 500);
            }
        } else {
            return "User not found";
        }
    }

    public function restoreUser($email)
    {
        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && $user->trashed()) {
            $user->restore();
            return response()->json(['message' => 'User restored successfully.']);
        }

        return response()->json(['message' => 'User not found or not deleted.'], 404);
    }
}

