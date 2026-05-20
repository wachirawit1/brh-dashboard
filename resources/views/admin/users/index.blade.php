@extends('layouts.app')

@section('page_title', 'จัดการผู้ใช้งาน (Admin)')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">จัดการผู้ใช้งาน</h2>
        <p class="text-gray-500 mt-1">ตั้งค่าสิทธิ์การเข้าถึงระบบสำหรับผู้ใช้ในองค์กร</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        เพิ่มผู้ใช้งานใหม่
    </a>
</div>



<div class="bg-white rounded-3xl shadow-xl shadow-gray-100 overflow-hidden border border-gray-100">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">ชื่อ-นามสกุล/แผนก</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">แผนกที่สังกัด</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">ระดับสิทธิ์</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider text-right">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="px-8 py-4 font-semibold text-gray-800">{{ $user->name }}</td>
                    <td class="px-8 py-4 text-sm text-gray-500">{{ $user->username }}</td>
                    <td class="px-8 py-4 text-sm text-gray-500">{{ $user->dept_name ?? '-' }}</td>
                    <td class="px-8 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-50 text-blue-600' }}">
                            {{ $user->role === 'admin' ? '👑 แอดมิน' : '👤 ผู้ใช้งาน' }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-right">
                        @if($user->username !== Auth::user()->username)
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('แน่ใจหรือไม่ที่จะลบผู้ใช้คนนี้?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-white hover:bg-red-500 px-3 py-1 rounded-lg text-sm font-bold transition">ลบผู้ใช้</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-lg">ตัวคุณเอง</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-8 py-20 text-center text-gray-400">ยังไม่มีผู้ใช้งานในระบบ (นอกจากคุณ)</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
