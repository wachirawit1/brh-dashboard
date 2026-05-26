@extends('layouts.app')

@section('page_title', 'จัดการผู้ใช้งาน (Admin)')

@section('content')
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">จัดการผู้ใช้งาน</h2>
            <p class="text-gray-500 mt-1">ตั้งค่าสิทธิ์การเข้าถึงระบบสำหรับผู้ใช้ในองค์กร</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            เพิ่มผู้ใช้งานใหม่
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 overflow-hidden border border-gray-100">
        <!-- ช่องค้นหา (Search Panel) -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="user-search-input" placeholder="ค้นหาชื่อหน่วยงาน, แผนก หรือ Username..."
                    class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-gray-400 shadow-sm">
            </div>
            <div class="text-sm font-semibold text-blue-600 bg-blue-50 px-4 py-2 rounded-xl" id="search-count-status">
                กำลังดาวน์โหลดข้อมูลผู้ใช้งาน...
            </div>
        </div>

        <table class="w-full text-left" id="users-table">
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
                    <tr class="hover:bg-blue-50/30 transition user-row"
                        data-name="{{ strtolower($user->name) }}"
                        data-username="{{ strtolower($user->username) }}"
                        data-dept="{{ strtolower($user->dept_name ?? '') }}">
                        <td class="px-8 py-4 font-semibold text-gray-800">{{ $user->name }}</td>
                        <td class="px-8 py-4 text-sm text-gray-500">{{ $user->username }}</td>
                        <td class="px-8 py-4 text-sm text-gray-500">{{ $user->dept_name ?? '-' }}</td>
                        <td class="px-8 py-4">
                            @if ($user->role === 'admin')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    แอดมิน
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    ผู้ใช้งาน
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-4 text-right">
                            @if ($user->username !== Auth::user()->username)
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-user-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="text-red-500 hover:text-white hover:bg-red-500 px-3 py-1 rounded-lg text-sm font-bold transition btn-delete-user">ลบผู้ใช้</button>
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

                <!-- แถวกรณีหาไม่พบข้อมูลค้นหา -->
                <tr id="no-search-results" class="hidden">
                    <td colspan="5" class="px-8 py-20 text-center text-gray-400 font-medium">ไม่พบข้อมูลผู้ใช้งานที่ตรงตามคำค้นหา</td>
                </tr>
            </tbody>
        </table>

        <!-- แถบควบคุมการแบ่งหน้า (Pagination Footer) -->
        <div class="p-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/30" id="pagination-footer">
            <div class="text-sm text-gray-500 font-medium" id="pagination-info">
                กำลังคำนวณหน้า...
            </div>
            <div class="flex items-center gap-1.5" id="pagination-buttons">
                <!-- ควบคุมปุ่มผ่าน JavaScript -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. ฟังก์ชันการลบผู้ใช้ร่วมกับ SweetAlert2
            const deleteButtons = document.querySelectorAll('.btn-delete-user');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('.delete-user-form');
                    Swal.fire({
                        title: 'ยืนยันการลบผู้ใช้งาน?',
                        text: "ข้อมูลผู้ใช้รายนี้จะถูกลบออกจากระบบและไม่สามารถกู้คืนได้",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'ใช่, ต้องการลบ',
                        cancelButtonText: 'ยกเลิก',
                        borderRadius: '1.5rem',
                        customClass: {
                            popup: 'rounded-3xl border border-gray-100 shadow-xl'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (form) {
                                form.submit();
                            }
                        }
                    });
                });
            });

            // 2. ระบบค้นหาและจัดระเบียบหน้า (Client-Side Pagination & Search)
            const searchInput = document.getElementById('user-search-input');
            const searchCountStatus = document.getElementById('search-count-status');
            const tableRows = document.querySelectorAll('.user-row');
            const noResultsRow = document.getElementById('no-search-results');
            const paginationFooter = document.getElementById('pagination-footer');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationButtons = document.getElementById('pagination-buttons');

            const itemsPerPage = 10; // แสดงหน้าละ 10 คน
            let currentPage = 1;
            let filteredRows = Array.from(tableRows);

            function updateTable() {
                const totalItems = filteredRows.length;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                if (totalItems === 0) {
                    noResultsRow.classList.remove('hidden');
                    paginationFooter.classList.add('hidden');
                    searchCountStatus.textContent = 'ไม่พบผลลัพธ์';
                    tableRows.forEach(row => row.classList.add('hidden'));
                    return;
                }

                noResultsRow.classList.add('hidden');
                paginationFooter.classList.remove('hidden');
                searchCountStatus.textContent = `ทั้งหมด ${totalItems} รายชื่อ`;

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                // ซ่อน/แสดงข้อมูลแต่ละแถวตามการกรอง
                tableRows.forEach(row => row.classList.add('hidden'));
                filteredRows.slice(startIndex, endIndex).forEach(row => row.classList.remove('hidden'));

                // ปรับปรุงข้อมูลคำแนะนำจำนวนหน้า
                const displayStart = startIndex + 1;
                const displayEnd = Math.min(endIndex, totalItems);
                paginationInfo.textContent = `แสดง ${displayStart} - ${displayEnd} จากทั้งหมด ${totalItems} รายชื่อ`;

                // สร้างปุ่มนำทาง (Pagination Buttons)
                paginationButtons.innerHTML = '';

                // ปุ่มย้อนกลับ (Previous Button)
                const prevBtn = document.createElement('button');
                prevBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                `;
                prevBtn.className = `p-2 rounded-xl border transition duration-150 ${currentPage === 1 ? 'text-gray-300 border-gray-100 cursor-not-allowed bg-gray-50/50' : 'text-gray-600 border-gray-200 hover:bg-gray-50'}`;
                if (currentPage > 1) {
                    prevBtn.addEventListener('click', () => {
                        currentPage--;
                        updateTable();
                    });
                }
                paginationButtons.appendChild(prevBtn);

                // ปุ่มตัวเลขหน้า (จำกัดการแสดงผลตัวเลขเพื่อไม่ให้ยาวเกินไป)
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.className = `w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold border transition duration-150 ${currentPage === i ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-100' : 'text-gray-600 border-gray-200 hover:bg-gray-50'}`;
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        updateTable();
                    });
                    paginationButtons.appendChild(pageBtn);
                }

                // ปุ่มถัดไป (Next Button)
                const nextBtn = document.createElement('button');
                nextBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                `;
                nextBtn.className = `p-2 rounded-xl border transition duration-150 ${currentPage === totalPages ? 'text-gray-300 border-gray-100 cursor-not-allowed bg-gray-50/50' : 'text-gray-600 border-gray-200 hover:bg-gray-50'}`;
                if (currentPage < totalPages) {
                    nextBtn.addEventListener('click', () => {
                        currentPage++;
                        updateTable();
                    });
                }
                paginationButtons.appendChild(nextBtn);
            }

            // ค้นหาข้อมูลแบบเรียลไทม์ (Instant Search Input Event)
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();

                if (term === '') {
                    filteredRows = Array.from(tableRows);
                } else {
                    filteredRows = Array.from(tableRows).filter(row => {
                        const name = row.getAttribute('data-name');
                        const username = row.getAttribute('data-username');
                        const dept = row.getAttribute('data-dept');
                        return name.includes(term) || username.includes(term) || dept.includes(term);
                    });
                }

                currentPage = 1; // รีเซ็ตกลับไปหน้า 1 ทุกครั้งที่พิมพ์ค้นหาใหม่
                updateTable();
            });

            // สั่งรันโปรแกรมในตอนเริ่มต้น
            updateTable();
        });
    </script>
@endsection
