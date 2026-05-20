<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HealthCheckup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    // หน้าหลัก จัดการผู้ใช้งาน
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    // หน้าฟอร์มสร้างผู้ใช้ใหม่
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // ดึงรายชื่อแผนกจาก config
        $departments = config('departments', []);

        return view('admin.users.create', compact('departments'));
    }

    // บันทึกผู้ใช้ใหม่
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:4',
            'name' => 'required',
            'dept_name' => 'required',
        ]);

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'dept_name' => $request->dept_name,
            'role' => 'user',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'สร้างบัญชีผู้ใช้สำเร็จ');
    }

    // เปลี่ยนแปลงสิทธิ์ (เฉพาะ Admin เท่านั้น)
    public function updateRole(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขระดับสิทธิ์ของผู้ใช้งาน');
        }

        $request->validate(['role_id' => 'required|in:admin,user']);
        
        $user->role = $request->role_id;
        $user->is_admin = ($request->role_id === 'admin');
        $user->save();

        return back()->with('success', 'อัปเดตสิทธิ์สำเร็จ');
    }

    // ลบผู้ใช้งาน
    public function destroy(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // ห้ามลบตัวเอง
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'คุณไม่สามารถลบบัญชีของตัวเองได้']);
        }

        $user->delete();
        return back()->with('success', 'ลบผู้ใช้งานสำเร็จ');
    }
}
