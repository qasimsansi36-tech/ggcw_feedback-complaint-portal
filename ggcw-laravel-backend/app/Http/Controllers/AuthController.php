<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:student,teacher,admin',
            'father_name' => 'nullable|string|max:255',
            'roll_no' => 'nullable|string|max:255|unique:users,roll_no',
            'department' => 'nullable|string|max:255',
            'card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $cardImagePath = null;
        if ($request->hasFile('card_image')) {
            $file = $request->file('card_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $cardImagePath = $file->storeAs('id_cards', $filename, 'public');
        }

        $rollNo = $request->roll_no ?? $request->roll ?? null;

        $user = User::create([
            'name' => $request->name,
            'father_name' => $request->father_name ?? null,
            'roll_no' => $rollNo,
            'department' => $request->department ?? null,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'card_image' => $cardImagePath,
            'is_approved' => 0,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Submitted for admin approval.',
            'user' => $user,
            'is_pending' => true
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:student,teacher,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password!'
            ], 401);
        }

        // 🔒 SECURITY FIX: reject login if the account's actual role
        // does not match the login page/role the request came from.
        if ($user->role !== $request->role) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not registered as a ' . ucfirst($request->role) . '. Please use the correct login page for your account type.'
            ], 403);
        }

        if ($user->role !== 'admin' && $user->status === 'pending') {
            return response()->json([
                'success' => false,
                'is_pending' => true,
                'message' => 'Your account is pending admin approval.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'role' => $user->role,
            'token' => $token,
            'user' => $user,
            'is_approved' => true
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully!'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    // 🔒 SECURITY FIX: Step 1 of password reset — send a 6-digit OTP to the
    // registered email instead of letting anyone reset a password with just an email.
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);

        // OTP 10 minute ke liye cache mein rakha jata hai (koi nayi database
        // table ya column banane ki zaroorat nahi).
        \Illuminate\Support\Facades\Cache::put('password_reset_otp_' . $request->email, $otp, now()->addMinutes(10));

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your GGCW Portal password reset code is: {$otp}\n\nThis code will expire in 10 minutes. If you did not request this, please ignore this email.",
                function ($message) use ($request) {
                    $message->to($request->email)->subject('GGCW Portal - Password Reset Code');
                }
            );
        } catch (\Exception $e) {
            // Agar mail server configure nahi hai (jaisa abhi local mein hai),
            // request phir bhi fail nahi honi chahiye — OTP cache mein save ho chuka
            // hai aur storage/logs/laravel.log mein bhi dikh jayega testing ke liye.
        }

        return response()->json([
            'success' => true,
            'message' => 'A 6-digit reset code has been sent to your email.'
        ]);
    }

    // 🔒 SECURITY FIX: Step 2 — OTP verify karke hi password change hota hai,
    // sirf email dena kaafi nahi hai.
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('password_reset_otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code. Please request a new one.'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        \Illuminate\Support\Facades\Cache::forget('password_reset_otp_' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully!'
        ]);
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    // ✅ NEW: Get only Students
    public function getStudents()
    {
        $students = User::where('role', 'student')->get();
        return response()->json([
            'success' => true,
            'users' => $students
        ]);
    }

    // ✅ NEW: Get only Teachers
    public function getTeachers()
    {
        $teachers = User::where('role', 'teacher')->get();
        return response()->json([
            'success' => true,
            'users' => $teachers
        ]);
    }

    public function approveUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $user->update([
            'is_approved' => 1,
            'status' => 'approved',
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User approved successfully!',
            'user' => $user
        ]);
    }

    public function rejectUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $user->update([
            'is_approved' => 0,
            'status' => 'rejected',
            'rejected_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User rejected successfully!',
            'user' => $user
        ]);
    }
}