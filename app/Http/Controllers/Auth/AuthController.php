<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * REGISTER
     * - validate
     * - create user
     * -generete token
     * - response
     */

    /**
     * LOGIN
     * - validate
     * -generete token
     * - return a response with the token
     */

    /**
     * PASSWORD RESET
     * - validation: email
     * - send a password reset
     * -
     */

    /**
     * LOGOUT
     * - delete token
     */


    // REGISTER
    public function register(Request $request): JsonResponse
    {
        // validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_photo' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('user_photo')) {
            $filename = $request->file('user_photo')->store('users', 'public');
        } else {
            $filename = Null;
        }

        $validated['user_photo'] = $filename;

        //createUser
        $user = User::create($validated);

        // Optionally create token for the user
        $token = $user->createToken('auth-token')->plainTextToken;

        try {
            //Welcome email
            $userEmail = $validated['email'];
            Mail::to($userEmail)->send(new WelcomeEmail());

            return response()->json([
                'message' => 'Registration successful and confirmation email sent',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Registration successful but email failed to send',
                'user' => $user,
                'token' => $token
            ], 404);
        }
    }

    //LOGIN
    public function login(Request $request): JsonResponse
    {
        //validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //find the user
        $user = User::where('email', $request->email)->first();
        //$user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive.'],
            ]);
        }

        //Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            // 'abilities' => $user->abilities(),
        ], 201);
    }

    //LOGOUT
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
