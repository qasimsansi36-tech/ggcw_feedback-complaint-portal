<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $complaint = Complaint::create([
            'student_roll' => $user->roll_no,
            'department' => $user->department,
            'category' => $request->category,
            'description' => $request->description,
            'date_time' => now(),
            'status' => 'Unresolved',
            'admin_remarks' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint submitted successfully!',
            'complaint' => $complaint
        ], 201);
    }

    public function myComplaints(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::where('student_roll', $user->roll_no)
            ->orderBy('date_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'complaints' => $complaints
        ]);
    }

    public function studentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // 🔒 SECURITY FIX: pehle client se bheja hua 'roll' field seedha save ho
        // raha tha, matlab koi bhi student kisi doosre student ke roll number se
        // complaint daal sakta tha. Ab hamesha login karne wale user ka apna
        // roll_no hi use hoga, request se bheja gaya roll ignore hota hai.
        $type = $request->input('type', 'complaint');

        $complaint = Complaint::create([
            'student_roll' => $user->roll_no,
            'department' => $user->department,
            'category' => $type === 'feedback' ? 'Feedback' : $request->input('category', 'General'),
            'description' => $request->description,
            'date_time' => now(),
            'status' => $type === 'feedback' ? 'Submitted' : 'Unresolved',
            'admin_remarks' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submitted successfully!',
            'complaint' => $complaint
        ], 201);
    }

    public function studentHistory(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::where('student_roll', $user->roll_no)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                $item->created_at = $item->date_time;
                $item->type = ($item->category === 'Feedback') ? 'feedback' : 'complaint';
                return $item;
            });

        return response()->json([
            'success' => true,
            'complaints' => $complaints
        ]);
    }

    // ✅ NAYA: teacher khud complaint/feedback submit karta hai
    public function teacherSubmit(Request $request)
    {
        $user = $request->user();

        // 🔒 SECURITY FIX: sirf teacher (ya admin) hi is route se submit kar sake,
        // koi student seedha URL call karke teacher jaisi entry na bana sake.
        if (!in_array($user->role, ['teacher', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Teacher access required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type', 'teacher');

        $complaint = Complaint::create([
            'student_roll' => 'TEACHER:' . $user->id,
            'department' => $user->department,
            'category' => $type === 'feedback' ? 'Feedback' : $request->input('category', 'General'),
            'description' => $request->description,
            'date_time' => now(),
            'status' => $type === 'feedback' ? 'Submitted' : 'Unresolved',
            'admin_remarks' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submitted successfully!',
            'complaint' => $complaint
        ], 201);
    }

    // ✅ NAYA: teacher apne department ki saari complaints + apni history dekhta hai
    public function teacherDepartmentComplaints(Request $request)
    {
        $user = $request->user();

        // 🔒 SECURITY FIX: sirf teacher/admin apne department ki complaints dekh sakein,
        // koi student is URL se doosre logon ki complaints na dekh sake.
        if (!in_array($user->role, ['teacher', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Teacher access required.'
            ], 403);
        }

        $complaints = Complaint::where('department', $user->department)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) use ($user) {
                $item->is_mine = ($item->student_roll === 'TEACHER:' . $user->id);

                if (str_starts_with((string) $item->student_roll, 'TEACHER:')) {
                    $teacherId = str_replace('TEACHER:', '', $item->student_roll);
                    $teacherUser = \App\Models\User::find($teacherId);
                    $item->display_name = $teacherUser ? $teacherUser->name : 'Teacher';
                    $item->type = ($item->category === 'Feedback') ? 'feedback' : 'teacher';
                } else {
                    $item->display_name = $item->student_roll;
                    $item->type = ($item->category === 'Feedback') ? 'feedback' : 'complaint';
                }

                return $item;
            });

        return response()->json([
            'success' => true,
            'complaints' => $complaints
        ]);
    }

    // ✅ NAYA: teacher kisi complaint ka status/remarks update karta hai
    public function teacherUpdateStatus(Request $request, $id)
    {
        $user = $request->user();

        // 🔒 SECURITY FIX: sirf teacher/admin hi kisi complaint ka status/remarks
        // badal sakein — pehle koi bhi logged-in student ye kar sakta tha.
        if (!in_array($user->role, ['teacher', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Teacher access required.'
            ], 403);
        }

        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found.'
            ], 404);
        }

        $complaint->update([
            'status' => $request->input('status', $complaint->status),
            'admin_remarks' => $request->input('remarks', $complaint->admin_remarks),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully!',
            'complaint' => $complaint
        ]);
    }
}