<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 1. Fetch All Pending Accounts for Admin Review
    public function getPendingUsers()
    {
        $users = User::where('is_approved', 0)
                     ->where('role', '!=', 'admin')
                     ->latest()
                     ->get();

        return response()->json([
            'success' => true,
            'users'   => $users
        ]);
    }

    // 2. Fetch All Approved Users
    public function getApprovedUsers()
    {
        $users = User::where('is_approved', 1)
                     ->latest()
                     ->get();

        return response()->json([
            'success' => true,
            'users'   => $users
        ]);
    }

    // 3. Approve User
    public function approveUser(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->is_approved = 1;
            $user->status = 'approved';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} has been approved successfully!"
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User not found!'], 404);
    }

    // 4. Reject User
    public function rejectUser(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->is_approved = 0;
            $user->status = 'rejected';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} application rejected."
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User not found!'], 404);
    }

    // 5. ✅ FIXED: Submit Complaint (student_roll column add)
    public function submitComplaint(Request $request)
    {
        DB::table('complaints')->insert([
            'user_id'     => $request->user_id ?? null,
            'user_name'   => $request->user_name ?? 'Anonymous',
            'email'       => $request->email ?? '',
            'subject'     => $request->subject ?? 'General Complaint',
            'category'    => $request->category ?? 'General',
            'description' => $request->description ?? $request->message ?? '',
            'department'  => $request->department ?? null,
            'roll_no'     => $request->roll_no ?? null,
            'student_roll' => $request->roll_no ?? null,  // ✅ ADDED for complaints table
            'status'      => 'Pending',
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint submitted successfully!'
        ]);
    }

    // 6. Submit Feedback
    public function submitFeedback(Request $request)
    {
        DB::table('feedbacks')->insert([
            'user_id'    => $request->user_id ?? null,
            'user_name'  => $request->user_name ?? 'Anonymous',
            'email'      => $request->email ?? '',
            'rating'     => $request->rating ?? 5,
            'category'   => $request->category ?? 'General',
            'feedback'   => $request->feedback ?? $request->comments ?? '',
            'status'     => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully!'
        ]);
    }

    // 7. Get All Complaints (Frontend Compatible)
    public function getComplaints()
    {
        $complaints = DB::table('complaints')
            ->select('id', 'student_roll', 'department', 'category', 'description', 'date_time', 'status', 'admin_remarks')
            ->latest('date_time')
            ->get();

        $formatted = $complaints->map(function($item) {
            return (object)[
                'id' => $item->id,
                'student_roll' => $item->student_roll,
                'roll_no' => $item->student_roll,
                'department' => $item->department,
                'category' => $item->category,
                'subject' => $item->category,
                'description' => $item->description,
                'date_time' => $item->date_time,
                'created_at' => $item->date_time,
                'status' => $item->status ?? 'Pending',
                'admin_remarks' => $item->admin_remarks,
                'user_name' => 'Student',
            ];
        });

        return response()->json([
            'success' => true,
            'complaints' => $formatted
        ]);
    }

    // 8. Get All Feedbacks
    public function getFeedbacks()
    {
        $feedbacks = DB::table('feedbacks')->latest()->get();
        return response()->json(['success' => true, 'feedbacks' => $feedbacks]);
    }

    // 9. Update Complaint Status
    public function updateComplaintStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'admin_remarks' => 'nullable|string'
        ]);

        DB::table('complaints')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'admin_remarks' => $request->admin_remarks,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint status updated successfully!'
        ]);
    }

    // 10. Get Single Complaint Details
    public function getComplaint($id)
    {
        $complaint = DB::table('complaints')->where('id', $id)->first();
        
        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'complaint' => $complaint
        ]);
    }
}