<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cmsUserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class cmsUserController extends Controller
{
    public function getAllUsers(Request $request)
    {
        $limit = (int) $request->input('limit', 5);
        $offset = (int) $request->input('offset', 0);
        $users = cmsUserModel::orderBy('user_id')
            ->limit($limit)
            ->offset($offset)
            ->get();
        return response()->json(["users" => $users, "offset" => $offset]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:cms_users',
            'email' => 'required|string|email|max:255|unique:cms_users',
            'password' => 'required|string|min:4|confirmed'
        ],[
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email already exists.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 4 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                ->withErrors($validator)
                ->withInput();
        } else {
            $user = new cmsUserModel();
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->save();

            return redirect('/')->with('success', 'Registration successful! You can now log in.');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|min:4|string',
        ],[
            'username.required' => 'username is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                ->withErrors($validator)
                ->withInput();
        }

        $user = cmsUserModel::where('username', $request->input('username'))->first();
        if ($user && Hash::check($request->input('password'), $user->password)) {
            $remember = $request->boolean('remember-me');
            if (!$remember) {

            }
            Auth::login($user, $remember);
            return redirect('/')->with('success', 'Welcome Back!');
        }

        return redirect('/')
            ->withErrors('Invalid username or password.')
            ->withInput();
    }

    public function logout(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->withErrors('You are not logged in.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You Have Been Logged Out');
    }

    public function editProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|string|email|max:255|unique:cms_users',
            'username' => 'nullable|string|max:255|unique:cms_users',
            'password' => 'nullable|string|min:4|confirmed',
        ],[
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email already exists.',
            'username.unique' => 'Username already exists.',
            'password.min' => 'Password must be at least 4 characters.',
            'password.confirmed' => 'Passwords do not match.'
        ]);
        if ($validator->fails()) {
            return redirect('/')->withErrors($validator)->withInput();
        }
        $user = new cmsUserModel();
        $user = cmsUserModel::where('user_id', Auth::user()->user_id)->first();
        if ($user) {
            $user->username = $request->input('username') ?: $user->username;
            $user->email = $request->input('email') ?: $user->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->save();
        } else {
            return redirect('/')->withErrors('Data not found.');
        }
        return redirect('/')->with('success', 'Profile Updated');
    }
}