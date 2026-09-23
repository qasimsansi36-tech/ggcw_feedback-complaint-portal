<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Get All Complaints (Feedback isme shamil nahi hoti)
    public function getComplaints()
    {
        $complaints = DB::table('complaints')
            ->where('category', '!=', 'Feedback')
            ->select('id', 'student_roll', 'department', 'category', 'description', 'date_time', 'status', 'admin_remarks')
            ->latest('date_time')
            ->get();

        $formatted = $complaints->map(function($item) {
            return (object)[
                'id' => $item->id,
                'student_roll' => $item->student_roll,
                'department' => $item->department,
                'category' => $item->category,
                'description' => $item->description,
                'date_time' => $item->date_time,
                'status' => $item->status ?? 'Pending',
                'admin_remarks' => $item->admin_remarks,
            ];
        });

        return response()->json([
            'success' => true,
            'complaints' => $formatted
        ]);
    }

    // Get All Feedbacks (complaints table se, category='Feedback')
    public function getFeedbacks()
    {
        $feedbacks = DB::table('complaints')
            ->where('category', 'Feedback')
            ->select('id', 'description', 'date_time')
            ->latest('date_time')
            ->get();

        $formatted = $feedbacks->map(function($item) {
            return (object)[
                'id' => $item->id,
                'user_name' => 'Student',
                'description' => $item->description,
                'rating' => null,
                'date_time' => $item->date_time,
            ];
        });

        return response()->json([
            'success' => true,
            'feedbacks' => $formatted
        ]);
    }

    // Update Complaint Status
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
}