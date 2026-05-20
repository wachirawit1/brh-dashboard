@extends('layouts.app')

@section('page_title', 'เพิ่มผู้ใช้งานใหม่')

@section('content')
<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control { border-radius: 0.75rem; padding: 0 1rem; border-color: #e2e8f0; background-color: #f8fafc; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); transition: all 0.2s; height: 46px !important; display: flex; align-items: center; }
    .ts-control.focus { background-color: #ffffff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); }
    .ts-control > input { font-size: 0.875rem; color: #0f172a; margin: 0; padding: 0; line-height: 1.25rem; }
    .ts-dropdown { border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); border-color: #e2e8f0; margin-top: 4px; z-index: 9999; }
    .ts-dropdown .ts-dropdown-content { padding: 0.5rem; }
    .ts-dropdown .option { padding: 0.5rem 0.75rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; }
    .ts-dropdown .option.active { background-color: #eff6ff; color: #1d4ed8; }
</style>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">จัดการผู้ใช้งาน</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">เพิ่มผู้ใช้งานใหม่</span>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 overflow-hidden border border-gray-100">
        <div class="px-8 py-6 border-b border-gray-50">
            <h3 class="font-bold text-gray-800 text-lg">กรอกข้อมูลผู้ใช้งาน</h3>
            <p class="text-sm text-gray-500 mt-1">สร้างบัญชีผู้ใช้ใหม่สำหรับแผนก หรือแอดมิน</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="p-8 space-y-6">
            @csrf



            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" 
                        class="w-full h-[46px] bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-4 focus:bg-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm text-sm" 
                        placeholder="เช่น IT_Dept" required>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" value="{{ old('password') }}" 
                        class="w-full h-[46px] bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-4 focus:bg-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm text-sm" 
                        placeholder="ตั้งรหัสผ่านที่จำง่าย" required>
                    <input type="checkbox" id="show-password" class="mt-2">
                    <label for="show-password" class="text-sm text-gray-500 ml-2">แสดงรหัสผ่าน</label>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ใช้งาน/ชื่อแผนก <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                        class="w-full h-[46px] bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-4 focus:bg-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm text-sm" 
                        placeholder="เช่น แผนกเทคโนโลยีสารสนเทศ" required>
                </div>

                <!-- Department -->
                <div>
                    <label for="dept_name" class="block text-sm font-medium text-gray-700 mb-2">แผนกที่สังกัด <span class="text-red-500">*</span></label>
                    <select name="dept_name" id="dept_name" class="w-full text-sm" required>
                        <option value="">-- เลือกแผนก --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ old('dept_name') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        พิมพ์ค้นหา หรือเลือกจากรายชื่อแผนก
                    </p>
                </div>

                
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-50">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-medium text-sm transition">ยกเลิก</a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium text-sm transition">สร้างบัญชีผู้ใช้</button>
            </div>
        </form>
    </div>
</div>

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#dept_name', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: "-- เลือกหรือค้นหาแผนก --",
            dropdownParent: 'body'
        });
        //แสดงรหัสผ่าน
        $('#show-password').on('change', function() {
            $('#password').attr('type', $(this).is(':checked') ? 'text' : 'password');
        }); 
        
    });
</script>
@endsection
