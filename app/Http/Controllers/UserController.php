<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::all();

        // dd($users); // Debugging line to check users
        return view('users.list', compact('users'));
    }

    public function show(User $user)
{
    return response()->json($user);
}

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
    ]);

    $user->update($validated);

    return response()->json($user);
}

public function destroy(User $user)
{
    $user->delete();
    return response()->json(['success' => true]);
}

public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            // Return validation errors with 422 status (AJAX friendly)
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user (no password here — add if needed)
        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Return the created user as JSON
        return response()->json($user, 201);
    }
    

    // Other methods for creating, editing, and deleting users can be added here
}
