@extends('layouts.app')

@section('title', 'ค้นหาผู้ใช้ใหม่ - ระบบบันทึกสุขภาพ')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        กลับหน้าจัดการ
    </a>
    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">เพิ่มผู้ใช้งานใหม่</h2>
    <p class="text-gray-500 mt-1">
        @if(Auth::user()->role === 'manager')
            รายชื่อพนักงานในแผนก {{ Auth::user()->dept_name }} (ดึงข้อมูลจาก SQL Server)
        @else
            ค้นหารายชื่อจากฐานข้อมูลกลางเพื่อมอบสิทธิ์การใช้งาน
        @endif
    </p>
</div>

<div class="bg-white rounded-3xl shadow-xl shadow-gray-100 p-8 mb-8 border border-gray-100">
    <div class="relative flex-1">
        <input type="text" id="searchInput" placeholder="พิมพ์ชื่อ, นามสกุล หรือ Username..." 
            class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition text-lg">
        <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>
</div>

<!-- ตารางแสดงผล AJAX -->
<div class="bg-white rounded-3xl shadow-xl shadow-gray-100 overflow-hidden border border-gray-100 min-h-[400px] relative">
    <div id="loading" class="hidden absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
    </div>

    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">รหัสผู้ใช้</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">แผนก</th>
                <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider text-right">จัดการ</th>
            </tr>
        </thead>
        <tbody id="resultsBody" class="divide-y divide-gray-50">
            <!-- ข้อมูลจะถูกเติมโดย JavaScript -->
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const resultsBody = document.getElementById('resultsBody');
    const loading = document.getElementById('loading');
    let debounceTimer;

    // ฟังก์ชันค้นหา
    const performSearch = (keyword = '') => {
        loading.classList.remove('hidden');
        
        fetch(`{{ route('admin.users.ajax-search') }}?keyword=${keyword}`)
            .then(response => response.json())
            .then(data => {
                resultsBody.innerHTML = '';
                
                if (data.results.length === 0) {
                    resultsBody.innerHTML = `<tr><td colspan="5" class="px-8 py-20 text-center text-gray-400">ไม่พบข้อมูลพนักงาน</td></tr>`;
                } else {
                    data.results.forEach(staff => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-blue-50/30 transition';
                        
                        let actionHtml = '';
                        if (staff.is_added) {
                            actionHtml = `
                                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-bold text-xs flex items-center justify-end gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    เพิ่มแล้ว
                                </span>`;
                        } else {
                            actionHtml = `
                                <form action="{{ route('admin.users.store') }}" method="POST" class="flex justify-end items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="username" value="${staff.username}">
                                    <input type="hidden" name="fname" value="${staff.fname}">
                                    <input type="hidden" name="lname" value="${staff.lname}">
                                    <input type="hidden" name="employee_id" value="${staff.userid}">
                                    <input type="hidden" name="dept_id" value="${staff.deptid || ''}">
                                    <input type="hidden" name="dept_name" value="${staff.dept_name || 'N/A'}">
                                    
                                    ${!data.is_manager ? `
                                        <select name="role_id" class="bg-white border border-gray-200 text-sm rounded-lg focus:ring-blue-500 py-2 px-3">
                                            ${data.all_roles.map(role => `<option value="${role.id}">${role.display_name}</option>`).join('')}
                                        </select>
                                    ` : ''}
                                    
                                    <button type="submit" class="bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-lg font-bold text-sm transition">
                                        ${data.is_manager ? 'อนุญาตเข้าใช้งาน' : 'มอบสิทธิ์'}
                                    </button>
                                </form>`;
                        }

                        row.innerHTML = `
                            <td class="px-8 py-4 font-mono text-sm text-gray-600">${staff.userid}</td>
                            <td class="px-8 py-4 font-semibold text-gray-800">${staff.fname} ${staff.lname}</td>
                            <td class="px-8 py-4 text-sm text-gray-500">${staff.username}</td>
                            <td class="px-8 py-4 text-sm text-gray-500">${staff.dept_name || '-'}</td>
                            <td class="px-8 py-4 text-right">${actionHtml}</td>
                        `;
                        resultsBody.appendChild(row);
                    });
                }
                loading.classList.add('hidden');
            });
    };

    // โหลดครั้งแรก (ถ้าเป็นหัวหน้าแผนกจะลิสต์รายชื่อมาให้เลย)
    performSearch('');

    // ค้นหาเมื่อพิมพ์ (Debounce 500ms)
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(this.value);
        }, 500);
    });
});
</script>
@endsection
