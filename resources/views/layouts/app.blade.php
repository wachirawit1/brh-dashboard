<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRH Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; transition: padding 0.3s; }
        .sidebar-link:hover { background-color: #eff6ff; color: #1d4ed8; }
        .sidebar-link.active { background-color: #eff6ff; color: #1d4ed8; border-right: 4px solid #1d4ed8; }
        
        /* สไตล์สำหรับแถบข้างที่ย่อ */
        .sidebar-collapsed aside { width: 4.5rem; }
        .sidebar-collapsed .sidebar-text { display: none; }
        .sidebar-collapsed aside h1 { display: none; }
        .sidebar-collapsed aside p { display: none; }
        .sidebar-collapsed aside .p-6 { padding: 1rem; text-align: center; }
        .sidebar-collapsed aside .p-4 { padding: 1rem; }
        .sidebar-collapsed aside .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-collapsed aside .sidebar-link svg { margin-right: 0; }
        .sidebar-collapsed aside .logged-in-box { display: none; }
        
        /* เพิ่ม Animation ให้ดูนุ่มนวล */
        aside { transition: width 0.3s ease; }
    </style>
</head>
<body class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col shadow-sm">
        <div class="p-6">
            <h1 class="text-2xl font-extrabold text-blue-600 tracking-tight">BRH</h1>
            <p class="text-xs text-gray-400 mt-1 uppercase tracking-widest font-semibold">Dashboard</p>
        </div>

        <nav class="flex-1 px-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center px-4 py-3 rounded-lg text-gray-600 transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="sidebar-text">Dashboard</span>
            </a>

            @if(in_array(Auth::user()->role, ['admin', 'manager']))
            <a href="{{ route('admin.users.index') }}" class="sidebar-link flex items-center px-4 py-3 rounded-lg text-gray-600 transition {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="sidebar-text">จัดการผู้ใช้งาน</span>
            </a>
            @endif

            <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg text-gray-600 transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="sidebar-text">รายงานแผนก</span>
            </a>
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
    <main class="flex-1 overflow-y-auto">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 px-8 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button id="toggle-sidebar" class="text-gray-500 hover:text-blue-600 focus:outline-none transition p-1 hover:bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
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

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                Toast.fire({
                    icon: 'error',
                    title: "{{ $error }}"
                });
            @endforeach
        @endif
    </script>
</body>
</html>
