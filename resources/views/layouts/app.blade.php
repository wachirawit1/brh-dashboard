<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปภาวะสุขภาพประจำปี รายหน่วยงาน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top left, #f8fafc 0%, #f1f5f9 100%);
            transition: padding 0.3s;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .sidebar-link {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-link:hover {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.8) 0%, rgba(219, 234, 254, 0.5) 100%);
            color: #1d4ed8;
            transform: translateX(4px);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            border-right: 4px solid #2563eb;
            font-weight: 600;
            box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.08);
        }

        /* สไตล์สำหรับแถบข้างที่ย่อ */
        .sidebar-collapsed aside {
            width: 4.5rem;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed aside h1 {
            display: none;
        }

        .sidebar-collapsed aside p {
            display: none;
        }

        .sidebar-collapsed aside .p-6 {
            padding: 1rem;
            text-align: center;
        }

        .sidebar-collapsed aside .p-4 {
            padding: 1rem;
        }

        .sidebar-collapsed aside .sidebar-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar-collapsed aside .sidebar-link svg {
            margin-right: 0;
        }

        .sidebar-collapsed aside .logged-in-box {
            display: none;
        }

        /* เพิ่ม Animation ให้ดูนุ่มนวล */
        aside {
            transition: width 0.3s ease;
        }

        /* Custom scrollbar globally for standard elements (sidebar, dropdowns, etc.) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ปรับแต่งแถบสกรอลล์ (Scrollbar) สำหรับตารางหลักและตารางพรีวิว Excel ให้เด่นชัดและใช้งานง่าย */
        .excel-preview-container,
        .main-table-container {
            scrollbar-width: auto; /* สำหรับ Firefox */
            scrollbar-color: #94a3b8 #f1f5f9; /* สำหรับ Firefox (สีปุ่ม และ สีราง) */
        }

        /* สำหรับ Chrome, Safari, Edge */
        .excel-preview-container::-webkit-scrollbar,
        .main-table-container::-webkit-scrollbar {
            width: 14px;  /* ความกว้างสำหรับแนวตั้ง */
            height: 14px; /* ความสูงสำหรับแนวนอน */
        }

        .excel-preview-container::-webkit-scrollbar-track,
        .main-table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .excel-preview-container::-webkit-scrollbar-thumb,
        .main-table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
            border: 2px solid #f1f5f9;
            box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.05);
        }

        .excel-preview-container::-webkit-scrollbar-thumb:hover,
        .main-table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .excel-preview-container::-webkit-scrollbar-thumb:active,
        .main-table-container::-webkit-scrollbar-thumb:active {
            background: #64748b;
        }

        /* เพิ่มปุ่มลูกศรควบคุมการเลื่อน (Scrollbar Buttons) เพื่อให้ผู้ใช้ทราบว่าเลื่อนได้ */
        .excel-preview-container::-webkit-scrollbar-button,
        .main-table-container::-webkit-scrollbar-button {
            display: block;
            background-color: #e2e8f0;
            background-repeat: no-repeat;
            background-size: 8px;
            background-position: center;
            width: 14px;
            height: 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        .excel-preview-container::-webkit-scrollbar-button:hover,
        .main-table-container::-webkit-scrollbar-button:hover {
            background-color: #cbd5e1;
        }

        /* ลูกศรเลื่อนขึ้น (Vertical Up) */
        .excel-preview-container::-webkit-scrollbar-button:vertical:decrement,
        .main-table-container::-webkit-scrollbar-button:vertical:decrement {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='%23475569'><polygon points='50,20 15,80 85,80'/></svg>");
        }

        /* ลูกศรเลื่อนลง (Vertical Down) */
        .excel-preview-container::-webkit-scrollbar-button:vertical:increment,
        .main-table-container::-webkit-scrollbar-button:vertical:increment {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='%23475569'><polygon points='15,20 85,20 50,80'/></svg>");
        }

        /* ลูกศรเลื่อนซ้าย (Horizontal Left) */
        .excel-preview-container::-webkit-scrollbar-button:horizontal:decrement,
        .main-table-container::-webkit-scrollbar-button:horizontal:decrement {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='%23475569'><polygon points='80,15 80,85 20,50'/></svg>");
        }

        /* ลูกศรเลื่อนขวา (Horizontal Right) */
        .excel-preview-container::-webkit-scrollbar-button:horizontal:increment,
        .main-table-container::-webkit-scrollbar-button:horizontal:increment {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='%23475569'><polygon points='20,15 20,85 80,50'/></svg>");
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col shadow-sm shrink-0">
        <a href="{{ route('dashboard') }}" class="p-6 flex items-center gap-3 hover:opacity-80 transition group border-b border-gray-50">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto object-contain shrink-0">
            <div class="sidebar-text">
                <h1 class="text-xs font-black text-blue-600 tracking-tight leading-tight group-hover:text-blue-700">
                    รายงานสรุปภาวะสุขภาพประจำปี รายหน่วยงาน
                </h1>
            </div>
        </a>

        <nav class="flex-1 px-3 space-y-1">
            <a href="{{ route('dashboard') }}"
                class="sidebar-link flex items-center px-4 py-3 rounded-lg text-gray-600 transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                <span class="sidebar-text">
                    @if (Auth::user()->role === 'admin')
                        Dashboard
                    @else
                        รายการไฟล์
                    @endif
                </span>
            </a>

            @if (in_array(Auth::user()->role, ['admin', 'manager']))
                <a href="{{ route('admin.users.index') }}"
                    class="sidebar-link flex items-center px-4 py-3 rounded-lg text-gray-600 transition {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    <span class="sidebar-text">จัดการผู้ใช้งาน</span>
                </a>
            @endif

        </nav>

        <div class="p-4 border-t border-gray-50">
            <div class="bg-gray-50 p-4 rounded-xl logged-in-box">
                <p class="text-xs text-gray-500">Logged in as:</p>
                <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-blue-500 uppercase font-bold mt-1">{{ Auth::user()->dept_name }}</p>

                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-red-500 font-bold hover:underline">ออกจากระบบ</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto flex flex-col justify-between">
        <div class="flex-1">
            <header
                class="bg-white/80 backdrop-blur-md sticky top-0 z-10 px-8 py-4 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button id="toggle-sidebar"
                        class="text-gray-500 hover:text-blue-600 focus:outline-none transition p-1 hover:bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-800">@yield('page_title')</h2>
                </div>
                <!-- <div class="flex items-center gap-4">
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-bold">System Online</span>
                </div> -->
            </header>

            <div class="p-8">
                @yield('content')
            </div>
        </div>

        <footer class="py-6 px-8 border-t border-gray-50 bg-white/50 backdrop-blur-sm text-center text-xs text-gray-400 font-semibold flex flex-col sm:flex-row justify-between items-center gap-2">
            <div>
                © {{ date('Y') }} ศูนย์รายงานข้อมูลภาวะสุขภาพประจำปี บุคลากร รพ.บุรีรัมย์รายหน่วยงาน ติดต่อสอบเพิ่มเติม คุณพรวดี กง.อาชีวเวชกรรม โทร. 3538
            </div>
            <div class="flex items-center gap-1.5">
                <span>พัฒนาโดย</span>
                <span class="text-blue-600 font-bold hover:text-blue-700 transition cursor-pointer">กลุ่มงานสุขภาพดิจิทัล</span>
            </div>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const toggleBtn = document.getElementById('toggle-sidebar');
        const body = document.body;

        // โหลดสถานะเดิมจาก LocalStorage
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('sidebar-collapsed');
            // บันทึกสถานะลง LocalStorage
            localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
        });

        // Global Alerts (SweetAlert2)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.fire({
                    icon: 'error',
                    title: "{{ $error }}"
                });
            @endforeach
        @endif
    </script>
</body>

</html>
