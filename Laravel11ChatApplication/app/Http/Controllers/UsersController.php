<?php

namespace App\Http\Controllers;
use App\Models\Admin;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Models\User;
 use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public function updateProfile(Request $request)
    {
        $userId = session('LoggedUserInfo');
        $user = User::find($userId);
    
        if (!$user) {
            return redirect('user/login')->with('fail', 'You must be logged in to update the profile');
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'bio' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Update user details
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->bio = $request->bio;
    
        if ($request->hasFile('picture')) {
            // Delete the old profile picture if it exists
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }
    
            // Store the new picture
            $file = $request->file('picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
    
            // Save the path to the user's picture
            $user->picture = $path;
        }
    
        // Save the user profile updates
        $user->save();
    
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
    
 

    
    public function post() {

        $users = User::all();
        return view('user.post', [
            'users' => $users 
        ]);
    }
    
    public function register() {
        return view("user.register");
    }
    public function login() {
        return view("user.login");
    }
    public function chats()
    {
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
    
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
    
        // Retrieve all admins
        $admins = Admin::all();
    
        return view('user.chats', [
            'LoggedUserInfo' => $LoggedUserInfo,
            'admins' => $admins // Pass only admins to the view
        ]);
    }
    
    public function edit()
    {
        
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
    
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
     
       
    return view('user.profileedit', [
        'LoggedUserInfo' => $LoggedUserInfo,
       
    ]);
    }
    
    public function profile()
    {
        
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
    
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
     
       
    return view('user.profileview', [
        'LoggedUserInfo' => $LoggedUserInfo,
      
    ]);
    }
    
    
    public function dashboard()
    {
        $userId = session('LoggedUserInfo');
    
        // Check if the session has the correct user ID
        if (!$userId) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
    
        $LoggedUserInfo = User::find($userId);
    
        // Fetch the count of messages for the user
     
        // Fetch the messages, ensuring they are ordered by the newest first
        
    
        return view('user.dashboard', [
            'LoggedUserInfo' => $LoggedUserInfo,
       
        ]);
    }
    
    
    
    public function check(Request $request)
{
     $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:5|max:12'
    ]);

     $userInfo = User::where('email', $request->email)->first();

     if (!$userInfo) {
        return back()->withInput()->withErrors(['email' => 'Email not found']);
    }
      if ($userInfo->status === 'inactive') {
         return back()->withInput()->withErrors(['status' => 'Your account is inactive']);
        }

     if (!Hash::check($request->password, $userInfo->password)) {
        return back()->withInput()->withErrors(['password' => 'Incorrect password']);
    }

     session([
        'LoggedUserInfo' => $userInfo->id,
        'LoggedUserName' => $userInfo->name,  
    ]);
     return redirect()->route('user.dashboard');
}

    

    

    public function logout()
    {
         if (session()->has('LoggedUserInfo')) {
             session()->forget('LoggedUserInfo');
        }
        session()->flush();

         return redirect()->route('user.dashboard');
    }
    
 


    public function save(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|regex:/^\S*$/',
    ], [
        'email.unique' => 'This email is already registered.',
        'password.min' => 'Password must be at least 8 characters long.',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return redirect()->route('user.login')->with('success', 'User created successfully!');
}
}
