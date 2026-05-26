<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - รายงานสรุปภาวะสุขภาพประจำปี รายหน่วยงาน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('/images/login_bg.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        /* Add a soft dark/cool-toned overlay to improve accessibility */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.45) 0%, rgba(30, 41, 59, 0.45) 100%);
            z-index: -1;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <div
        class="bg-white/85 backdrop-blur-xl p-8 rounded-3xl border border-white/40 shadow-2xl w-full max-w-md transition-all duration-300 hover:shadow-blue-500/10">
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-block group transition hover:opacity-85">
                <div class="inline-flex items-center justify-center mb-4 transition">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-20 w-auto object-contain">
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    รายงานสรุปภาวะสุขภาพประจำปี</h1>
                <p class="text-blue-600 font-bold text-xs uppercase tracking-wider mt-1.5">รายหน่วยงาน</p>
            </a>
            <p class="text-gray-500 text-sm mt-3">กรุณาเข้าสู่ระบบด้วยบัญชีของแต่ละหน่วยงาน</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username"
                    class="w-full px-4 py-3 bg-white/60 border border-gray-200/80 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password"
                    class="w-full px-4 py-3 bg-white/60 border border-gray-200/80 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
