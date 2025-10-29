<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Teachers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <style>
    :root {
        --accent: #f97316;
        --muted: #6b7280;
    }

    body,
    input,
    select,
    button,
    textarea,
    label {
        font-family: 'Parkinsans', sans-serif;
    }

    body {
        background-color: #fafafa;
        color: #1f2937;
        letter-spacing: -0.01em;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    main {
        margin-top: 80px;
    }

    th,
    td {
        font-size: 13px;
        text-align: center;
    }

    /* Responsive layout fix for sidebar */
    @media (min-width: 1024px) {
        main {
            margin-left: 16rem;
        }
    }

    @media (max-width: 1023px) {
        main {
            margin-left: 0;
        }
    }

    /* 🌟 Toast Styles */
    .toast {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: white;
        font-size: 14px;
        padding: 10px 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.4s ease, fadeOut 0.5s ease 2.8s forwards;
    }

    #toastContainer {
        top: 80px;
        /* default around top-20 */
    }

    .toast-success {
        background: rgba(34, 197, 94, 0.25);
        border-left: 4px solid #22c55e;
    }

    .toast-error {
        background: rgba(239, 68, 68, 0.25);
        border-left: 4px solid #ef4444;
    }

    .toast-warning {
        background: rgba(234, 179, 8, 0.25);
        border-left: 4px solid #eab308;
    }

    /* ✨ Animation (from top center) */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
    }
    </style>
</head>

<!-- <body class="bg-gray-50 min-h-screen text-gray-700"> -->

<body class="relative min-h-screen text-gray-700 bg-gray-50" style="
          background: url('assets/logo/danhs1.jpg') no-repeat center center fixed;
          background-size: cover;
      ">

    <!-- 🔹 translucent blur overlay -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm">
        <div class="flex">

            <?php include 'includes/navbar.php'; ?>

            <main class="flex-1 p-4 sm:p-6 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user" class="w-6 h-6" style="color:var(--accent)"></i>
                        <h2 class="text-xl font-semibold text-white">Teachers Management</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input id="searchInput" type="text" placeholder="Search teachers..."
                                class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[--accent] w-56 sm:w-64 transition" />
                        </div>

                        <button id="openModalBtn"
                            class="bg-[--accent] hover:brightness-90 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 transition">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Teacher
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <section class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-[--accent] text-white">
                                <tr>
                                    <th class="p-3">Name</th>
                                    <th class="p-3">Department</th>
                                    <th class="p-3">Email</th>
                                    <th class="p-3">Contact</th>
                                    <th class="p-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teacherTableBody"
                                class="divide-y divide-gray-100 text-center transition-opacity duration-500"></tbody>

                            <tr id="noResultsRow" class="hidden">
                                <td colspan="5" class="py-6 text-gray-400 italic">No results found.</td>
                            </tr>
                        </table>
                    </div>

                    <div id="mobileCards" class="md:hidden p-4 grid gap-4"></div>
                    <p id="noResultsCard" class="hidden text-center py-8 text-gray-400 italic">No results found.</p>
                </section>
            </main>
        </div>

        <!-- Modal -->
        <div id="teacherModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-5 relative">
                <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5" style="color:var(--accent)"></i>
                    <span id="modalTitle">Add Teacher</span>
                </h3>

                <form id="teacherForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="teacher_id" id="teacher_id" />

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Full Name</label>
                        <input name="full_name" type="text" placeholder="Full Name" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Department</label>
                        <input name="department" type="text" placeholder="Department"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Email</label>
                        <input name="email" type="email" placeholder="Email"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Contact Number</label>
                        <input name="contact_number" type="text" placeholder="Contact Number"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Username</label>
                        <input name="username" type="text" placeholder="Username" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-gray-600 mb-1">Password</label>
                        <input name="password" type="password" placeholder="Password"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[--accent] transition">
                    </div>

                    <div class="col-span-2 flex justify-end mt-5">
                        <button type="submit"
                            class="bg-[--accent] hover:brightness-90 text-white text-sm font-medium rounded-lg px-6 py-2.5 flex items-center gap-2 transition">
                            <i data-lucide="save" class="w-4 h-4"></i> Save Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 🌟 Toast Notification Container
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 items-end pointer-events-none">
    </div> -->

    <!-- 🌟 Toast Notification Container -->
    <div id="toastContainer"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] flex flex-col gap-3 items-center pointer-events-none">
    </div>

    <script>
    lucide.createIcons();

    // 🌟 Toast Function
    function showToast(message, type = "success") {
        const container = document.getElementById("toastContainer");
        const toast = document.createElement("div");
        toast.className = `toast toast-${type}`;

        const icon =
            type === "success" ?
            "check-circle" :
            type === "error" ?
            "x-circle" :
            "alert-triangle";

        toast.innerHTML = `
        <i data-lucide="${icon}" class="w-5 h-5"></i>
        <span>${message}</span>
    `;

        container.appendChild(toast);
        lucide.createIcons({
            icons: [icon]
        });

        setTimeout(() => toast.remove(), 3500);
    }


    const modal = document.getElementById('teacherModal');
    const modalTitle = document.getElementById('modalTitle');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const teacherForm = document.getElementById('teacherForm');
    let teacherData = [];

    // 🔹 Open modal (Add)
    openModalBtn.onclick = () => {
        modalTitle.textContent = 'Add Teacher';
        teacherForm.reset();
        document.getElementById('teacher_id').value = '';
        modal.classList.remove('hidden');
    };

    // 🔹 Close modal
    closeModalBtn.onclick = () => modal.classList.add('hidden');
    modal.addEventListener('click', e => {
        if (e.target === modal) modal.classList.add('hidden');
    });

    // 🔹 Load teachers from server
    async function loadTeachers() {
        try {
            const res = await fetch('teachers/list.php');
            const data = await res.json();
            teacherData = data;
            renderTeachers();
        } catch (err) {
            console.error(err);
        }
    }

    // 🔹 Render table + mobile cards
    function renderTeachers() {
        const tbody = document.getElementById('teacherTableBody');
        const mobileGrid = document.getElementById('mobileCards');
        const noResultsRow = document.getElementById('noResultsRow');
        const noResultsCard = document.getElementById('noResultsCard');

        // Clear existing rows/cards
        tbody.innerHTML = '';
        mobileGrid.innerHTML = '';

        // Determine current layout (mobile or desktop)
        const isMobile = window.innerWidth < 768;

        if (!teacherData.length) {
            // Show only one "No results" message depending on layout
            if (isMobile) {
                noResultsCard.classList.remove('hidden');
                noResultsRow.classList.add('hidden');
            } else {
                noResultsRow.classList.remove('hidden');
                noResultsCard.classList.add('hidden');
            }
            return;
        }

        // Hide both "no results" messages if data exists
        noResultsRow.classList.add('hidden');
        noResultsCard.classList.add('hidden');

        // Render teachers
        teacherData.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="p-3">${t.full_name || '-'}</td>
            <td class="p-3">${t.department || '-'}</td>
            <td class="p-3">${t.email || '-'}</td>
            <td class="p-3">${t.contact_number || '-'}</td>
            <td class="p-3 flex justify-center gap-2">
                <button class="text-[--accent] text-xs" onclick='editTeacher(${JSON.stringify(t)})'>Edit</button>
                <button class="text-red-600 text-xs" onclick='deleteTeacher(${t.teacher_id})'>Delete</button>
            </td>`;
            tbody.appendChild(tr);

            // Mobile Card
            const card = document.createElement('div');
            card.className = 'bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2';
            card.innerHTML = `
            <p class="text-sm font-medium text-gray-800">${t.full_name}</p>
            <p class="text-sm text-gray-700">${t.department || '—'}</p>
            <p class="text-xs text-gray-600">${t.email || ''}</p>
            <p class="text-xs text-gray-600">${t.contact_number || ''}</p>
            <div class="flex gap-3 justify-center mt-2">
                <button class="text-[--accent] text-sm" onclick='editTeacher(${JSON.stringify(t)})'>Edit</button>
                <button class="text-red-600 text-sm" onclick='deleteTeacher(${t.teacher_id})'>Delete</button>
            </div>
        `;
            mobileGrid.appendChild(card);
        });
    }


    // 🔹 Edit Teacher
    function editTeacher(t) {
        modalTitle.textContent = 'Edit Teacher';
        modal.classList.remove('hidden');
        document.getElementById('teacher_id').value = t.teacher_id;
        teacherForm.full_name.value = t.full_name;
        teacherForm.department.value = t.department;
        teacherForm.email.value = t.email;
        teacherForm.contact_number.value = t.contact_number;
        teacherForm.username.value = t.username;
    }

    // 🔹 Delete Teacher
    async function deleteTeacher(id) {
        if (!confirm('Delete this teacher?')) return;
        const res = await fetch('teachers/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'teacher_id=' + id
        });
        const text = await res.text();

        if (text.toLowerCase().includes('success')) {
            showToast('Teacher deleted successfully!', 'success');
        } else {
            showToast('Failed to delete teacher.', 'error');
        }

        loadTeachers();
    }


    // 🔹 Form Submit (Add / Edit)
    teacherForm.addEventListener('submit', async e => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const id = formData.get('teacher_id');
        const url = id ? 'teachers/edit.php' : 'teachers/add.php';

        // Show saving notification
        const btn = teacherForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Saving...`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            // Success message depending on action
            if (res.ok && data.success) {
                showToast(id ? 'Teacher updated successfully!' : 'Teacher added successfully!', 'success');
            } else {
                showToast(data.message || 'Something went wrong.', 'warning');
            }




            modal.classList.add('hidden');
            teacherForm.reset();
            loadTeachers();

        } catch (err) {
            showToast('Error while saving teacher.', 'error');

        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> Save Teacher`;
            lucide.createIcons(); // re-render icons
        }
    });

    // 🔹 Search Filter (live + "no results" toggle)
    document.getElementById('searchInput').addEventListener('input', e => {
        const filter = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#teacherTableBody tr');
        const cards = document.querySelectorAll('#mobileCards > div');
        const noResultsRow = document.getElementById('noResultsRow');
        const noResultsCard = document.getElementById('noResultsCard');
        const isMobile = window.innerWidth < 768;

        let visibleCount = 0;

        // 🔹 Desktop rows
        rows.forEach(tr => {
            const text = tr.textContent.toLowerCase();
            const match = text.includes(filter);
            tr.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // 🔹 Mobile cards
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const match = text.includes(filter);
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // 🔹 Show/hide "No results found"
        if (visibleCount === 0) {
            if (isMobile) {
                noResultsCard.classList.remove('hidden');
                noResultsRow.classList.add('hidden');
            } else {
                noResultsRow.classList.remove('hidden');
                noResultsCard.classList.add('hidden');
            }
        } else {
            noResultsRow.classList.add('hidden');
            noResultsCard.classList.add('hidden');
        }
    });

    loadTeachers();
    </script>

</body>

</html>