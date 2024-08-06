<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:8',
            'profile_image' => 'required|image',
            'date_of_birth' => 'required',
        ]);
        $filePath = $request->profile_image->store('profile_images', 'public');

        $profileImage = Storage::url($filePath);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'profile_image' => $profileImage,
            'date_of_birth' => $request->date_of_birth,
        ]);
        $token = $user->createToken('')->plainTextToken;
        return response()->json([
            'success' => 'User created successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required|min:8',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('')->plainTextToken;
            return response()->json([
                'success' => 'User logged in successfully',
                'user' => $user,
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'error' => 'Invalid email or password',
            ], 400);
        }
    }
}
