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
    <title>Admin - Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Font -->
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
    </style>
</head>

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
                        <i data-lucide="list-checks" class="w-6 h-6" style="color:var(--accent)"></i>
                        <h2 class="text-xl font-semibold text-white">Manage Requests</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input id="searchInput" type="text" placeholder="Search by teacher name..."
                                class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[--accent] w-56 sm:w-64 transition" />
                        </div>

                        <!-- Status Filter -->
                        <select id="statusFilter"
                            class="py-2 px-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[--accent]">
                            <option value="All" selected>All</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Table Section -->
                <section class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-[--accent] text-white">
                                <tr>
                                    <th class="p-3">Teacher</th>
                                    <th class="p-3">Item</th>
                                    <th class="p-3">Unit</th>
                                    <th class="p-3">Variant</th>
                                    <th class="p-3">Quantity</th>
                                    <th class="p-3">Date</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestTableBody" class="divide-y divide-gray-100 text-center"></tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div id="mobileCards" class="md:hidden p-4 grid gap-4"></div>

                    <!-- No Results -->
                    <div id="noResultsContainer" class="hidden py-6 text-center text-gray-400 italic text-xs">
                        No requests found.
                    </div>


                </section>
            </main>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();

        let allRequests = [];

        const escapeHTML = str =>
            str ? String(str).replace(/[&<>"']/g, tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [tag])) : '';

        const statusBadge = status => {
            const base = "inline-block px-2 py-1 text-xs font-semibold rounded";
            switch (status) {
                case "Pending":
                    return `<span class="${base} bg-yellow-100 text-yellow-800">Pending</span>`;
                case "Approved":
                    return `<span class="${base} bg-green-100 text-green-800">Approved</span>`;
                case "Rejected":
                    return `<span class="${base} bg-red-100 text-red-800">Rejected</span>`;
                default:
                    return `<span class="${base} bg-gray-100 text-gray-800">${escapeHTML(status)}</span>`;
            }
        };

        async function loadRequests() {
            try {
                const res = await fetch('requests/list.php', {
                    cache: "no-store"
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const text = await res.text();
                try {
                    allRequests = JSON.parse(text);
                } catch {
                    console.error("Invalid JSON from list.php:", text);
                    allRequests = [];
                }
            } catch (err) {
                console.error("Error loading requests:", err);
                allRequests = [];
            }
            renderRequests();
        }

        function renderRequests() {
            const tbody = document.getElementById('requestTableBody');
            const mobileGrid = document.getElementById('mobileCards');
            const noResultsContainer = document.getElementById('noResultsContainer');
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filterEl = document.getElementById('statusFilter');
            const filter = filterEl ? filterEl.value : "All";

            // clear previous content
            tbody.innerHTML = '';
            mobileGrid.innerHTML = '';
            noResultsContainer.classList.add('hidden');

            const filtered = allRequests.filter(r => {
                const matchesName = (r.teacher_name || '').toLowerCase().includes(search);
                const matchesStatus = filter === "All" || r.status === filter;
                return matchesName && matchesStatus;
            });

            // ✅ Show only one "No results" message
            if (filtered.length === 0) {
                noResultsContainer.classList.remove('hidden');
                return; // stop here — don’t add a row in tbody
            }

            noResultsContainer.classList.add('hidden');

            // ✅ Render table rows
            filtered.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td class="p-3">${escapeHTML(r.teacher_name)}</td>
            <td class="p-3">${escapeHTML(r.items)}</td>
            <td class="p-3">${escapeHTML(r.unit || '-')}</td>
            <td class="p-3">${escapeHTML(r.variant_requested || '-')}</td>
            <td class="p-3">${escapeHTML(r.quantity_requested ?? '0')}</td>
            <td class="p-3">${escapeHTML(r.request_date)}</td>
            <td class="p-3">${statusBadge(r.status)}</td>
            <td class="p-3 space-x-2">
                ${r.status === "Pending" ? `
                    <button data-id="${r.request_id}" class="approve text-green-600 hover:underline">Approve</button>
                    <button data-id="${r.request_id}" class="reject text-red-500 hover:underline">Reject</button>
                    <button data-id="${r.request_id}" data-qty="${r.quantity_requested || 0}" class="edit text-[--accent] hover:underline">Edit</button>
                ` : `<span class="text-gray-400 italic">No actions</span>`}
            </td>`;
                tbody.appendChild(tr);

                // Mobile card
                const card = document.createElement('div');
                card.className =
                    'bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1';
                card.innerHTML = `
            <p class="text-sm font-medium text-gray-800">${escapeHTML(r.teacher_name)}</p>
            <p class="text-sm text-gray-700">${escapeHTML(r.items)} (${escapeHTML(r.unit || '-')})</p>
            <p class="text-xs text-gray-600">Variant: ${escapeHTML(r.variant_requested || '-')}</p>
            <p class="text-xs text-gray-600">Quantity: ${escapeHTML(r.quantity_requested ?? '0')}</p>
            <p class="text-xs text-gray-600">Date: ${escapeHTML(r.request_date)}</p>
            <div class="mt-2">${statusBadge(r.status)}</div>
            ${r.status === "Pending" ? `
                <div class="mt-2 flex gap-3 text-sm">
                    <button data-id="${r.request_id}" class="approve text-green-600 hover:underline">Approve</button>
                    <button data-id="${r.request_id}" class="reject text-red-500 hover:underline">Reject</button>
                    <button data-id="${r.request_id}" data-qty="${r.quantity_requested || 0}" class="edit text-[--accent] hover:underline">Edit</button>
                </div>
            ` : `<span class="text-gray-400 italic text-sm mt-1">No actions</span>`}
        `;
                mobileGrid.appendChild(card);
            });

            attachActions();
        }



        function attachActions() {
            document.querySelectorAll('button.approve').forEach(btn =>
                btn.addEventListener('click', async e => {
                    const id = e.currentTarget.dataset.id;
                    if (confirm('Approve this request?')) {
                        await fetch('requests/approve.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `request_id=${encodeURIComponent(id)}`
                        });
                        await loadRequests();
                    }
                })
            );

            document.querySelectorAll('button.reject').forEach(btn =>
                btn.addEventListener('click', async e => {
                    const id = e.currentTarget.dataset.id;
                    if (confirm('Reject this request?')) {
                        await fetch('requests/reject.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `request_id=${encodeURIComponent(id)}`
                        });
                        await loadRequests();
                    }
                })
            );

            document.querySelectorAll('button.edit').forEach(btn =>
                btn.addEventListener('click', async e => {
                    const id = e.currentTarget.dataset.id;
                    const qty = e.currentTarget.dataset.qty ?? 0;
                    const newQty = prompt('Enter new quantity:', qty);
                    if (newQty && Number(newQty) > 0) {
                        await fetch('requests/update_quantity.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `request_id=${encodeURIComponent(id)}&quantity=${encodeURIComponent(newQty)}`
                        });
                        await loadRequests();
                    } else if (newQty) alert('Quantity must be greater than 0.');
                })
            );
        }

        document.getElementById('searchInput').addEventListener('input', renderRequests);
        document.getElementById('statusFilter').addEventListener('change', renderRequests);

        loadRequests();
        setInterval(loadRequests, 10000); // refresh every 10s
    });
    </script>
</body>

</html>