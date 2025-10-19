<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

            public function login(Request $req)
            {
                $validator = Validator::make($req->all(), [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6',
                ]);
                
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                $credentials = $req->only('email', 'password');
                
                if (Auth::attempt($credentials)) {
                    
                    $req->session()->regenerate(); 
                    $user = Auth::user();
                    // dd($credentials);

                    return response()->json([
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role
                        ]
                    ], 200);
                }

                return response()->json(['message' => 'Invalid email or password'], 401);
            }

    public function logout(Request $req)
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $req)
    {
        return response()->json(Auth::user());
    }


    public function signup(Request $req)
    {
        
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = new User();

        // Handle optional image upload
        if ($req->hasFile('image')) {
            $image = $req->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = 'UserLogin/';
            $image->move(public_path($path), $filename);
            $user->image = $path . $filename;
        }

        // Assign user data
        $user->name = $req->input('name');
        $user->email = $req->input('email');
        $user->password = Hash::make($req->input('password'));
        $user->role = "User";

        $user->save();

        // Return JSON response
        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'image' => $user->image,
                'created_at' => $user->created_at,
            ]
        ], 201);
    }
    
    public function allUser(Request $req){
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found',
                'users' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Users fetched successfully',
            'users' => $users
        ], 200);
    }
}
