<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AdminsController extends Controller
{
    public function register() {
        return view("admin.register");
    }
    public function login() {
        return view("admin.login");
    }public function chats()
    {
        $LoggedAdminInfo = Admin::find(session('LoggedAdminInfo'));
        if (!$LoggedAdminInfo) {
            return redirect()->route('admin.login')->with('fail', 'You must be logged in to access the dashboard');
        }
    
        // Fetch chats where the admin is either the sender or the receiver
        $chats = Chat::with(['senderProfilee', 'receiverProfilee', 'senderSellerProfile', 'receiverSellerProfile'])
            ->where('sender_id', $LoggedAdminInfo->id)
            ->orWhere('receiver_id', $LoggedAdminInfo->id)
            ->get();
    
        // Combine both results and remove duplicates
        $allChats = $chats->map(function($chat) use ($LoggedAdminInfo) {
            if ($chat->sender_id == $LoggedAdminInfo->id) {
                if ($chat->receiverProfilee) {
                    $chat->user_id = $chat->receiver_id;
                    $chat->profile = $chat->receiverProfilee;
                } else {
                    $chat->user_id = $chat->receiver_id;
                    $chat->profile = $chat->receiverSellerProfile;
                }
            } else {
                if ($chat->senderProfilee) {
                    $chat->user_id = $chat->sender_id;
                    $chat->profile = $chat->senderProfilee;
                } else {
                    $chat->user_id = $chat->sender_id;
                    $chat->profile = $chat->senderSellerProfile;
                }
            }
            return $chat;
        })->unique('user_id')->values();
    
        // Pass the logged-in admin's information and chats to the view
        return view('admin.chats', [
            'LoggedAdminInfo' => $LoggedAdminInfo,
            'chats' => $allChats
        ]);
    }
    
     
    
    
    public function dashboard()
    {
        $adminId = session('LoggedAdminInfo');

        // Check if the session has the correct admin ID
        if (!$adminId) {
            return redirect('admin/login')->with('fail', 'You must be logged in to access the dashboard');
        }

        $LoggedAdminInfo = Admin::find($adminId);

        if (!$LoggedAdminInfo) {
            return redirect('admin/login')->with('fail', 'Admin not found');
        }

        return view('admin.dashboard', [
            'LoggedAdminInfo' => $LoggedAdminInfo
        ]);
    }
    
    
    
    public function check(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5|max:12'
        ]);

        // Find the admin by email
        $adminInfo = Admin::where('email', $request->email)->first();

        // Check if the admin exists
        if (!$adminInfo) {
            return back()->withInput()->withErrors(['email' => 'Email not found']);
        }

        // Check if the admin's account is inactive
        if ($adminInfo->status === 'inactive') {
            return back()->withInput()->withErrors(['status' => 'Your account is inactive']);
        }

        // Check if the password is correct
        if (!Hash::check($request->password, $adminInfo->password)) {
            return back()->withInput()->withErrors(['password' => 'Incorrect password']);
        }

        // Set session variables
        session([
            'LoggedAdminInfo' => $adminInfo->id,
            'LoggedAdminName' => $adminInfo->name,
        ]);

        // Redirect to the admin dashboard
        return redirect()->route('admin.dashboard');
    }

    

    
 
 
    public function logout()
    {
        if (Session::has('LoggedAdminInfo')) {
            Session::forget('LoggedAdminInfo');
        }
        Session::flush();

        return redirect()->route('admin.login');
    }

    public function updateProfile(Request $request)
    {
        $adminId = session('LoggedAdminInfo');
        $admin = Admin::find($adminId);

        if (!$admin) {
            return redirect('admin/login')->with('fail', 'You must be logged in to update the profile');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'bio' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update admin details
        $admin->name = $request->name;
        $admin->phone_number = $request->phone_number;
        $admin->bio = $request->bio;

        if ($request->hasFile('picture')) {
            // Delete the old profile picture if it exists
            if ($admin->picture) {
                Storage::disk('public')->delete($admin->picture);
            }

            // Store the new picture
            $file = $request->file('picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            // Save the path to the admin's picture
            $admin->picture = $path;
        }

        // Save the admin profile updates
        $admin->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function edit()
    {
        $adminId = session('LoggedAdminInfo');
        $LoggedAdminInfo = Admin::find($adminId);

        if (!$LoggedAdminInfo) {
            return redirect('admin/login')->with('fail', 'You must be logged in to access the dashboard');
        }

        return view('admin.profileedit', [
            'LoggedAdminInfo' => $LoggedAdminInfo,
        ]);
    }

    public function profile()
    {
        $adminId = session('LoggedAdminInfo');
        $LoggedAdminInfo = Admin::find($adminId);

        if (!$LoggedAdminInfo) {
            return redirect('admin/login')->with('fail', 'You must be logged in to access the dashboard');
        }

        return view('admin.profileview', [
            'LoggedAdminInfo' => $LoggedAdminInfo,
        ]);
    }
    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|regex:/^\S*$/',
         ], [
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
             'picture.max' => 'Profile picture size must be less than 2MB.',
        ]);

        $adminData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        
        Admin::create($adminData);

        return redirect()->route('admin.login')->with('success', 'Admin created successfully!');
    }
}
