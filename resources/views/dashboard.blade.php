@extends('layouts.app')

@section('title', 'หน้าแรก - ระบบจัดการไฟล์สุขภาพ')

@section('content')
    <style>
        /* สไตล์คัสตอมสำหรับตารางพรีวิว Excel */
        .excel-preview-container {
            max-height: 60vh;
            overflow: auto;
            border-radius: 16px;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
        }

        .excel-preview-container table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-family: inherit;
            background-color: #ffffff;
            color: #374151;
        }

        .excel-preview-container th,
        .excel-preview-container tr:first-child td {
            position: sticky;
            top: 0;
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-right: 1px solid #e2e8f0 !important;
            padding: 10px 14px !important;
            white-space: nowrap;
            font-size: 0.75rem;
            z-index: 20;
        }

        .excel-preview-container td {
            border-bottom: 1px solid #e2e8f0 !important;
            border-right: 1px solid #e2e8f0 !important;
            padding: 10px 14px !important;
            white-space: nowrap;
            font-size: 0.8rem;
            transition: background-color 0.15s;
        }

        .excel-preview-container tr:nth-child(even) {
            background-color: rgba(248, 250, 252, 0.5);
        }

        .excel-preview-container tr:hover td {
            background-color: #f1f5f9 !important;
        }

        /* สไตล์อนิเมชั่นและคลาสสำหรับ Dropdown คัสตอม */
        @keyframes fadeScaleIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeScaleIn 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ปรับแต่งแถบสกรอล (Scrollbar) สำหรับเมนูดร็อปดาวน์คัสตอม */
        #dept-options-list::-webkit-scrollbar,
        #year-options-list::-webkit-scrollbar,
        #status-options-list::-webkit-scrollbar {
            width: 6px;
        }

        #dept-options-list::-webkit-scrollbar-track,
        #year-options-list::-webkit-scrollbar-track,
        #status-options-list::-webkit-scrollbar-track {
            background: transparent;
        }

        #dept-options-list::-webkit-scrollbar-thumb,
        #year-options-list::-webkit-scrollbar-thumb,
        #status-options-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }

        #dept-options-list::-webkit-scrollbar-thumb:hover,
        #year-options-list::-webkit-scrollbar-thumb:hover,
        #status-options-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }


    </style>

    <div class="mb-8 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">

        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">ศูนย์รายงานข้อมูลภาวะสุขภาพประจำปีบุคลากร
                รพ.บุรีรัมย์รายหน่วยงาน</h2>
            <p class="text-gray-500 mt-1">ดาวน์โหลดรายงานสรุปภาวะสุขภาพประจำปีแยกตามแผนกและปีงบประมาณ</p>
        </div>


        @if (Auth::user()->role === 'admin')
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 w-full xl:w-auto">
                <form action="{{ route('admin.health-files.upload') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col sm:flex-row items-center gap-4">
                    @csrf
                    <div class="w-full sm:w-auto">
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1.5">เลือกไฟล์เอกสาร
                            (.xlsx, .xls)</label>
                        <input type="file" name="file" accept=".xlsx,.xls"
                            class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            required>
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto sm:mt-5 shrink-0 whitespace-nowrap bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        อัปโหลดไฟล์สุขภาพ
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- ส่วนตัวกรองข้อมูล (Filter Panel) -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
                <span class="text-sm font-bold text-gray-700">ตัวกรองเอกสาร:</span>
            </div>

            @if (Auth::user()->role === 'admin')
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto items-center">
                    <!-- Custom Searchable Dropdown for Departments -->
                    <div class="relative inline-block text-left w-full sm:w-64" id="searchable-dept-container">
                        <!-- Hidden select to keep backend form submit fully working -->
                        <select name="department" id="real-dept-select" class="hidden">
                            <option value="">ทุกแผนก</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}</option>
                            @endforeach
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="dept-dropdown-btn"
                            class="flex items-center justify-between w-full text-left text-sm border border-gray-200 rounded-full px-5 py-2 bg-white text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200">
                            <span id="selected-dept-label" class="truncate">
                                {{ request('department') ?: 'ทุกแผนก' }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="dept-dropdown-menu"
                            class="hidden absolute left-0 right-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-3 animate-fade-in flex flex-col gap-2 max-h-72">
                            <!-- Search Input -->
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                                <input type="text" id="dept-search-input" placeholder="พิมพ์ค้นหาแผนกด่วน..."
                                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold">
                            </div>

                            <!-- List of options -->
                            <div id="dept-options-list"
                                class="overflow-y-auto divide-y divide-gray-50 max-h-48 pr-1 flex flex-col gap-1">
                                <button type="button" onclick="selectDepartment('')"
                                    class="dept-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('department') == '' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="">
                                    <span>ทุกแผนก</span>
                                    @if (request('department') == '')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                @foreach ($departments as $dept)
                                    <button type="button" onclick="selectDepartment('{{ $dept }}')"
                                        class="dept-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('department') == $dept ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                        data-value="{{ $dept }}">
                                        <span class="truncate">{{ $dept }}</span>
                                        @if (request('department') == $dept)
                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Custom Year Dropdown for Admin -->
                    <div class="relative inline-block text-left w-full sm:w-48" id="searchable-year-container">
                        <!-- Hidden select to keep backend form submit fully working -->
                        <select name="year" id="real-year-select" class="hidden">
                            <option value="">ทุกปีการตรวจ</option>
                            @foreach ($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>ปี
                                    พ.ศ.
                                    {{ $yr }}</option>
                            @endforeach
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="year-dropdown-btn"
                            class="flex items-center justify-between w-full text-left text-sm border border-gray-200 rounded-full px-5 py-2 bg-white text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200">
                            <span id="selected-year-label" class="truncate">
                                {{ request('year') ? 'ปี พ.ศ. ' . request('year') : 'ทุกปีการตรวจ' }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="year-dropdown-menu"
                            class="hidden absolute left-0 right-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-3 animate-fade-in flex flex-col gap-2 max-h-72">
                            <!-- List of options -->
                            <div id="year-options-list"
                                class="overflow-y-auto divide-y divide-gray-50 max-h-48 pr-1 flex flex-col gap-1">
                                <button type="button" onclick="selectYear('')"
                                    class="year-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('year') == '' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="">
                                    <span>ทุกปีการตรวจ</span>
                                    @if (request('year') == '')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                @foreach ($years as $yr)
                                    <button type="button" onclick="selectYear('{{ $yr }}')"
                                        class="year-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('year') == $yr ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                        data-value="{{ $yr }}">
                                        <span>ปี พ.ศ. {{ $yr }}</span>
                                        @if (request('year') == $yr)
                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Custom Status Dropdown for Admin -->
                    <div class="relative inline-block text-left w-full sm:w-48" id="searchable-status-container">
                        <!-- Hidden select to keep backend form submit fully working -->
                        <select name="status" id="real-status-select" class="hidden">
                            <option value="">ทุกสถานะ</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>เผยแพร่แล้ว
                            </option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>แบบร่าง (รอส่ง)
                            </option>
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="status-dropdown-btn"
                            class="flex items-center justify-between w-full text-left text-sm border border-gray-200 rounded-full px-5 py-2 bg-white text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200">
                            <span id="selected-status-label" class="truncate">
                                @if (request('status') == 'published')
                                    เผยแพร่แล้ว
                                @elseif(request('status') == 'draft')
                                    แบบร่าง (รอส่ง)
                                @else
                                    ทุกสถานะ
                                @endif
                            </span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="status-dropdown-menu"
                            class="hidden absolute left-0 right-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-3 animate-fade-in flex flex-col gap-2 max-h-72">
                            <!-- List of options -->
                            <div id="status-options-list"
                                class="overflow-y-auto divide-y divide-gray-50 max-h-48 pr-1 flex flex-col gap-1">
                                <button type="button" onclick="selectStatus('')"
                                    class="status-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('status') == '' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="">
                                    <span>ทุกสถานะ</span>
                                    @if (request('status') == '')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                <button type="button" onclick="selectStatus('published')"
                                    class="status-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('status') == 'published' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="published">
                                    <span>เผยแพร่แล้ว</span>
                                    @if (request('status') == 'published')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                <button type="button" onclick="selectStatus('draft')"
                                    class="status-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('status') == 'draft' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="draft">
                                    <span>แบบร่าง (รอส่ง)</span>
                                    @if (request('status') == 'draft')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex gap-3">
                    <!-- Custom Year Dropdown for User -->
                    <div class="relative inline-block text-left w-full sm:w-48" id="searchable-year-container">
                        <!-- Hidden select to keep backend form submit fully working -->
                        <select name="year" id="real-year-select" class="hidden">
                            <option value="">ทุกปีการตรวจ</option>
                            @foreach ($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>ปี
                                    พ.ศ.
                                    {{ $yr }}</option>
                            @endforeach
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="year-dropdown-btn"
                            class="flex items-center justify-between w-full text-left text-sm border border-gray-200 rounded-full px-5 py-2 bg-white text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200">
                            <span id="selected-year-label" class="truncate">
                                {{ request('year') ? 'ปี พ.ศ. ' . request('year') : 'ทุกปีการตรวจ' }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="year-dropdown-menu"
                            class="hidden absolute left-0 right-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-3 animate-fade-in flex flex-col gap-2 max-h-72">
                            <!-- List of options -->
                            <div id="year-options-list"
                                class="overflow-y-auto divide-y divide-gray-50 max-h-48 pr-1 flex flex-col gap-1">
                                <button type="button" onclick="selectYear('')"
                                    class="year-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('year') == '' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                    data-value="">
                                    <span>ทุกปีการตรวจ</span>
                                    @if (request('year') == '')
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                @foreach ($years as $yr)
                                    <button type="button" onclick="selectYear('{{ $yr }}')"
                                        class="year-opt-btn w-full flex items-center justify-between px-4 py-2.5 text-sm font-semibold rounded-xl transition duration-150 {{ request('year') == $yr ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-slate-50 hover:text-gray-900' }}"
                                        data-value="{{ $yr }}">
                                        <span>ปี พ.ศ. {{ $yr }}</span>
                                        @if (request('year') == $yr)
                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 ml-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (request('department') || request('year') || request('status'))
                <a href="{{ route('dashboard') }}"
                    class="text-sm text-red-500 hover:text-red-700 font-semibold ml-2 transition">ล้างตัวกรอง</a>
            @endif
        </form>
    </div>

    @if (Auth::user()->role === 'admin')
        <!-- แผงรายงานสถิติย่อย (Sleek Stats Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Stat 1: Total Files -->
            <div
                class="bg-gradient-to-br from-white to-slate-50/50 p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">เอกสารสุขภาพทั้งหมด</p>
                    <h4 class="text-2xl font-black text-gray-800 tracking-tight">{{ $files->total() }} <span
                            class="text-xs font-medium text-gray-400">ไฟล์</span></h4>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Stat 2: Active Departments -->
            <div
                class="bg-gradient-to-br from-white to-slate-50/50 p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">แผนกที่จัดเก็บ</p>
                    <h4 class="text-2xl font-black text-gray-800 tracking-tight">{{ count($departments) }} <span
                            class="text-xs font-medium text-gray-400">แผนก</span></h4>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Stat 3: Last Upload -->
            <div
                class="bg-gradient-to-br from-white to-slate-50/50 p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">อัปโหลดล่าสุด</p>
                    <h4 class="text-sm font-extrabold text-gray-700 leading-tight">
                        @if ($lastUpload)
                            {{ $lastUpload->created_at->diffForHumans() }}
                        @else
                            -
                        @endif
                    </h4>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    <!-- ตารางรายการไฟล์แบบ Google Drive -->
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 overflow-hidden border border-gray-100">
        <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-white">
            <h3 class="font-bold text-gray-800 text-lg">รายงานสรุปภาวะสุขภาพประจำปีรายหน่วยงาน</h3>
            <span class="text-xs bg-blue-50 text-blue-700 font-bold px-3 py-1.5 rounded-full">พบไฟล์ทั้งหมด
                {{ $files->total() }} รายการ</span>
        </div>

        <div class="overflow-x-auto main-table-container">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-center">
                        <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">ชื่อเอกสาร</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">ปีการตรวจ</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">แผนก</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">ขนาดไฟล์</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">วันที่อัปโหลด</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">ผู้รับผิดชอบ</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">สถานะ
                        </th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">
                            ตรวจสอบและดาวน์โหลด
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($files as $file)
                        <tr class="hover:bg-blue-50/20 transition duration-150">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <!-- Excel File Icon (Green) -->
                                    <div
                                        class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-green-600 flex-shrink-0">
                                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span
                                            class="block font-semibold text-gray-800 hover:text-blue-600 cursor-pointer max-w-xs md:max-w-md truncate"
                                            onclick="openPreview({{ $file->id }})">
                                            {{ $file->original_name }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full font-semibold text-xs">พ.ศ.
                                    {{ $file->year }}</span>
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-500 font-semibold">{{ $file->department }}</td>
                            <td class="px-6 py-5 text-sm text-gray-400">{{ number_format($file->file_size / 1024, 1) }} KB
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-400">{{ $file->created_at->format('d/m/Y H:i') }} น.
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-500">{{ $file->uploader->name ?? 'ไม่ระบุ' }}</td>

                            <td class="px-6 py-5 text-center">
                                @if (Auth::user()->role === 'admin')
                                    <form action="{{ route('admin.health-files.publish', $file->id) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 rounded-full text-xs font-bold transition duration-200 {{ $file->is_published ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">
                                            {{ $file->is_published ? 'เผยแพร่แล้ว' : 'แบบร่าง (รอส่ง)' }}
                                        </button>
                                    </form>
                                @else
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">เผยแพร่แล้ว</span>
                                @endif
                            </td>

                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- ปุ่มพรีวิวดูตัวอย่างไฟล์ -->
                                    <button onclick="openPreview({{ $file->id }})"
                                        class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl transition duration-150"
                                        title="ดูข้อมูลตัวอย่างออนไลน์">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>

                                    <!-- ปุ่มดาวน์โหลดไฟล์ -->
                                    <a href="{{ route('dashboard.download', $file->id) }}"
                                        class="p-2 bg-green-50 text-green-600 hover:bg-green-100 rounded-xl transition duration-150"
                                        title="ดาวน์โหลดไฟล์ตัวจริง">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>

                                    <!-- ปุ่มลบไฟล์ (เฉพาะแอดมิน) -->
                                    @if (Auth::user()->role === 'admin')
                                        <button onclick="deleteFile({{ $file->id }}, '{{ $file->original_name }}')"
                                            class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition duration-150"
                                            title="ลบไฟล์ออกจากระบบ">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                        <form id="delete-form-{{ $file->id }}"
                                            action="{{ route('admin.health-files.destroy', $file->id) }}" method="POST"
                                            class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-8 py-24 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                ยังไม่มีไฟล์ข้อมูลสุขภาพจัดเก็บอยู่ในระบบ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($files->hasPages())
            <div class="p-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/30"
                id="pagination-footer">
                <div class="text-sm text-gray-500 font-medium" id="pagination-info">
                    แสดง {{ $files->firstItem() }} - {{ $files->lastItem() }} จากทั้งหมด {{ $files->total() }} รายการ
                </div>
                <div class="flex items-center gap-1.5" id="pagination-buttons">
                    <!-- Previous Page Button -->
                    @if ($files->onFirstPage())
                        <span class="p-2 rounded-xl border text-gray-300 border-gray-100 cursor-not-allowed bg-gray-50/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $files->appends(request()->query())->previousPageUrl() }}"
                            class="p-2 rounded-xl border text-gray-600 border-gray-200 hover:bg-gray-50 transition duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @for ($i = 1; $i <= $files->lastPage(); $i++)
                        @if ($i == $files->currentPage())
                            <span
                                class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold border bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-100">
                                {{ $i }}
                            </span>
                        @else
                            <a href="{{ $files->appends(request()->query())->url($i) }}"
                                class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold border text-gray-600 border-gray-200 hover:bg-gray-50 transition duration-150">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    <!-- Next Page Button -->
                    @if ($files->hasMorePages())
                        <a href="{{ $files->appends(request()->query())->nextPageUrl() }}"
                            class="p-2 rounded-xl border text-gray-600 border-gray-200 hover:bg-gray-50 transition duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    @else
                        <span class="p-2 rounded-xl border text-gray-300 border-gray-100 cursor-not-allowed bg-gray-50/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- WINDOWS PREVIEW MODAL (หน้าต่างจำลองพรีวิวชีท Excel) -->
    <!-- ============================================== -->
    <div id="preview-modal"
        class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="bg-white w-full max-w-6xl h-[85vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-fade-in">

            <!-- ส่วนหัว Modal (Header) -->
            <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h4 id="preview-filename" class="text-lg font-bold text-gray-800 truncate max-w-md">
                        กำลังโหลดตัวอย่างเอกสาร...</h4>
                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-400 font-semibold uppercase">
                        <span id="preview-department">แผนก</span>
                        <span>|</span>
                        <span id="preview-year">ปีการตรวจ</span>
                    </div>
                </div>
                <button onclick="closePreview()"
                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- ตัวเลือกแท็บชีท (Sheet Navigation Tabs) -->
            <div class="bg-gray-100/50 border-b border-gray-100 px-8 py-2 flex items-center">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider mr-4">แผ่นชีทสเปรดชีต:</span>
                <div id="preview-tabs" class="flex flex-wrap gap-2">
                    <!-- แท็บจะถูกสร้างขึ้นด้วย JavaScript -->
                </div>
            </div>

            <!-- ส่วนของตารางพรีวิว (Content) -->
            <div id="preview-content-box" class="flex-1 p-6 overflow-auto bg-gray-50 flex items-center justify-center">
                <!-- ตารางสเปรดชีตพรีวิวจะแสดงตรงนี้ -->
                <div id="preview-loading" class="text-center text-gray-500 font-semibold">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    กำลังประมวลผลและแปลงไฟล์สเปรดชีตเป็น HTML...
                </div>
                <div id="preview-sheet-data"
                    class="hidden w-full h-full overflow-auto excel-preview-container bg-white border border-gray-200 rounded-xl shadow-inner p-4">
                    <!-- แสดงข้อมูลชีทจริง -->
                </div>
            </div>

            <!-- แถบด้านล่าง Modal (Footer) -->
            <div class="px-8 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div class="flex items-center gap-2 text-xs text-blue-700 bg-blue-50/80 px-4 py-2.5 rounded-2xl border border-blue-100 font-medium">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>คำแนะนำ: ตารางสามารถเลื่อน (Scroll) ไปทางขวาและลงด้านล่าง เพื่อดูข้อมูลผลตรวจสุขภาพทั้งหมดได้</span>
                </div>
                <button onclick="closePreview()"
                    class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold px-6 py-2 rounded-full transition">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSheets = [];

        /**
         * ดึงข้อมูลพรีวิวผ่าน AJAX
         */
        function openPreview(id) {
            // เคลียร์ค่าตัวพรีวิวเดิมก่อน
            $('#preview-filename').text('กำลังประมวลผลสเปรดชีตไฟล์...');
            $('#preview-tabs').empty();
            $('#preview-sheet-data').addClass('hidden').empty();
            $('#preview-loading').removeClass('hidden');
            $('#preview-modal').removeClass('hidden').addClass('flex');

            $.ajax({
                url: '/dashboard/files/' + id + '/preview',
                method: 'GET',
                success: function(response) {
                    if (response.sheets && response.sheets.length > 0) {
                        currentSheets = response.sheets;

                        $('#preview-filename').text(response.filename);
                        $('#preview-department').text('แผนก: ' + response.department);
                        $('#preview-year').text('ประจำปี: พ.ศ. ' + response.year);

                        // สร้างปุ่มแท็บสำหรับแต่ละชีท
                        response.sheets.forEach((sheet, idx) => {
                            const activeClass = idx === 0 ? 'bg-blue-600 text-white font-bold' :
                                'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200';
                            const tabBtn =
                                `<button onclick="switchSheet(${idx})" id="sheet-tab-${idx}" class="sheet-tab-btn px-4 py-1.5 rounded-full text-xs transition ${activeClass}">${sheet.name}</button>`;
                            $('#preview-tabs').append(tabBtn);
                        });

                        // แสดงชีทแรกเป็นค่าเริ่มต้น
                        switchSheet(0);
                    } else {
                        $('#preview-loading').addClass('hidden');
                        $('#preview-sheet-data').removeClass('hidden').html(
                            '<div class="text-center py-10 text-red-500 font-semibold">ไม่พบข้อมูลชีทภายในไฟล์ Excel</div>'
                        );
                    }
                },
                error: function(xhr) {
                    closePreview();
                    let errorMessage = 'เกิดข้อผิดพลาดในการโหลดตัวอย่างไฟล์';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: errorMessage,
                        confirmButtonText: 'รับทราบ'
                    });
                }
            });
        }

        /**
         * เปลี่ยนการสลับแสดงแท็บแผ่นชีท
         */
        function switchSheet(idx) {
            // อัปเดตสไตล์สีปุ่มแท็บ
            $('.sheet-tab-btn').removeClass('bg-blue-600 text-white font-bold').addClass(
                'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200');
            $(`#sheet-tab-${idx}`).removeClass(
                'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200').addClass(
                'bg-blue-600 text-white font-bold');

            // อัปเดตเนื้อหาข้อมูล
            $('#preview-loading').addClass('hidden');
            $('#preview-sheet-data').removeClass('hidden').html(currentSheets[idx].html);
        }

        /**
         * ปิดหน้าต่างพรีวิว
         */
        function closePreview() {
            $('#preview-modal').addClass('hidden').removeClass('flex');
        }

        // ปิดหน้าต่างพรีวิวเมื่อกด ESC
        $(document).keydown(function(e) {
            if (e.keyCode === 27) {
                closePreview();
            }
        });

        // ปิดหน้าต่างพรีวิวเมื่อคลิกนอก Modal Card (Backdrop click)
        $(document).ready(function() {
            $('#preview-modal').on('click', function(e) {
                if (e.target === this) {
                    closePreview();
                }
            });
        });

        /**
         * ลบไฟล์พร้อมแจ้งยืนยันด้วย SweetAlert2
         */
        function deleteFile(id, filename) {
            Swal.fire({
                title: 'ต้องการลบไฟล์นี้หรือไม่?',
                text: 'ไฟล์ "' + filename + '" จะถูกลบออกถาวรและไม่สามารถกู้คืนได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ฉันต้องการลบไฟล์',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete-form-' + id).submit();
                }
            });
        }

        /**
         * การจัดการ Custom Dropdowns (แผนก & ปีการตรวจ & สถานะเผยแพร่) เพื่อความลื่นไหลและหรูหราพรีเมียม
         */
        $(document).ready(function() {
            // 1. จัดการตัวเลือก แผนก
            const $deptBtn = $('#dept-dropdown-btn');
            const $deptMenu = $('#dept-dropdown-menu');
            const $deptSearch = $('#dept-search-input');

            if ($deptBtn.length) {
                $deptBtn.on('click', function(e) {
                    e.stopPropagation();
                    $('#year-dropdown-menu').addClass('hidden'); // ปิดช่องปีถ้าเปิดค้างไว้
                    $('#status-dropdown-menu').addClass('hidden'); // ปิดช่องสถานะถ้าเปิดค้างไว้
                    $deptMenu.toggleClass('hidden');
                    if (!$deptMenu.hasClass('hidden')) {
                        $deptSearch.val('').trigger('input').focus();
                    }
                });

                $deptSearch.on('input', function() {
                    const searchVal = $(this).val().toLowerCase().trim();
                    $('.dept-opt-btn').each(function() {
                        const optText = $(this).text().toLowerCase();
                        if (optText.includes(searchVal) || $(this).data('value') === '') {
                            $(this).removeClass('hidden');
                        } else {
                            $(this).addClass('hidden');
                        }
                    });
                });
            }

            // 2. จัดการตัวเลือก ปีการตรวจ
            const $yearBtn = $('#year-dropdown-btn');
            const $yearMenu = $('#year-dropdown-menu');

            if ($yearBtn.length) {
                $yearBtn.on('click', function(e) {
                    e.stopPropagation();
                    $deptMenu.addClass('hidden'); // ปิดช่องแผนกถ้าเปิดค้างไว้
                    $('#status-dropdown-menu').addClass('hidden'); // ปิดช่องสถานะถ้าเปิดค้างไว้
                    $yearMenu.toggleClass('hidden');
                });
            }

            // 3. จัดการตัวเลือก สถานะเผยแพร่ (สำหรับ Admin)
            const $statusBtn = $('#status-dropdown-btn');
            const $statusMenu = $('#status-dropdown-menu');

            if ($statusBtn.length) {
                $statusBtn.on('click', function(e) {
                    e.stopPropagation();
                    $deptMenu.addClass('hidden'); // ปิดช่องแผนกถ้าเปิดค้างไว้
                    $yearMenu.addClass('hidden'); // ปิดช่องปีถ้าเปิดค้างไว้
                    $statusMenu.toggleClass('hidden');
                });
            }

            // 4. ปิดเมนูทั้งหมดเมื่อคลิกนอกพื้นที่ควบคุม
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#searchable-dept-container').length) {
                    $deptMenu.addClass('hidden');
                }
                if (!$(e.target).closest('#searchable-year-container').length) {
                    $yearMenu.addClass('hidden');
                }
                if (!$(e.target).closest('#searchable-status-container').length) {
                    $statusMenu.addClass('hidden');
                }
            });

            // 5. ปิดเมื่อกดปุ่ม ESC
            $(document).keydown(function(e) {
                if (e.keyCode === 27) {
                    $deptMenu.addClass('hidden');
                    $yearMenu.addClass('hidden');
                    $statusMenu.addClass('hidden');
                }
            });
        });

        /**
         * เลือกแผนกจาก Dropdown คัสตอม
         */
        function selectDepartment(value) {
            const $select = $('#real-dept-select');
            $select.val(value);
            $select.closest('form').submit();
        }

        /**
         * เลือกปีจาก Dropdown คัสตอม
         */
        function selectYear(value) {
            const $select = $('#real-year-select');
            $select.val(value);
            $select.closest('form').submit();
        }

        /**
         * เลือกสถานะจาก Dropdown คัสตอม
         */
        function selectStatus(value) {
            const $select = $('#real-status-select');
            $select.val(value);
            $select.closest('form').submit();
        }
    </script>
@endsection
