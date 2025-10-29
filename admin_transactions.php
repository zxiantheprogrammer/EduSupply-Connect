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
    <title>Admin - Transactions</title>
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
                        <i data-lucide="file-text" class="w-6 h-6" style="color:var(--accent)"></i>
                        <h2 class="text-xl font-semibold text-white">Transactions History</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input id="searchInput" type="text" placeholder="Search by teacher name..."
                                class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[--accent] w-56 sm:w-64 transition" />
                        </div>

                        <select id="statusFilter"
                            class="border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[--accent] px-3 py-2 transition">
                            <option value="All">All Status</option>
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
                                    <th class="p-3">Variant</th>
                                    <th class="p-3">Quantity</th>
                                    <th class="p-3">Date</th>
                                    <th class="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody id="transactionTableBody"
                                class="divide-y divide-gray-100 text-center transition-opacity duration-500"></tbody>

                            <tr id="noResultsRow" class="hidden">
                                <td colspan="6" class="py-6 text-gray-400 italic">No transactions found.</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div id="mobileCards" class="md:hidden p-4 grid gap-4"></div>
                    <p id="noResultsCard" class="hidden text-center py-8 text-gray-400 italic">No transactions found.
                    </p>
                </section>
            </main>
        </div>
    </div>
    <script>
    lucide.createIcons();
    let allTransactions = [];

    function escapeHTML(str) {
        return str ? str.replace(/[&<>"']/g, tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [tag])) : '';
    }

    function statusBadge(status) {
        const base = "inline-block px-2 py-1 text-xs font-semibold rounded";
        switch (status) {
            case "Approved":
                return `<span class="${base} bg-green-100 text-green-800">Approved</span>`;
            case "Rejected":
                return `<span class="${base} bg-red-100 text-red-800">Rejected</span>`;
            default:
                return `<span class="${base} bg-gray-100 text-gray-800">${status}</span>`;
        }
    }

    async function loadTransactions() {
        try {
            const res = await fetch('../requests/transactions.php');
            const data = await res.json();
            allTransactions = data;
            renderTransactions();
        } catch (err) {
            console.error(err);
        }
    }

    function renderTransactions() {
        const tbody = document.getElementById('transactionTableBody');
        const mobileGrid = document.getElementById('mobileCards');
        const search = document.getElementById('searchInput').value.toLowerCase();
        const filter = document.getElementById('statusFilter').value;

        const filtered = allTransactions.filter(r => {
            const matchesName = r.teacher_name.toLowerCase().includes(search);
            const matchesStatus = filter === "All" || r.status === filter;
            return matchesName && matchesStatus;
        });

        tbody.innerHTML = '';
        mobileGrid.innerHTML = '';

        if (filtered.length === 0) {
            document.getElementById('noResultsRow').classList.remove('hidden');
            document.getElementById('noResultsCard').classList.remove('hidden');
            return;
        }

        document.getElementById('noResultsRow').classList.add('hidden');
        document.getElementById('noResultsCard').classList.add('hidden');

        filtered.forEach(r => {
            // Desktop Row
            const tr = document.createElement('tr');
            tr.innerHTML = `
                    <td class="p-3">${escapeHTML(r.teacher_name)}</td>
                    <td class="p-3">${escapeHTML(r.supply_name)}</td>
                    <td class="p-3">${escapeHTML(r.variant_requested || '-')}</td>
                    <td class="p-3">${escapeHTML(r.quantity_requested || '0')}</td>
                    <td class="p-3">${escapeHTML(r.request_date)}</td>
                    <td class="p-3">${statusBadge(r.status)}</td>
                `;
            tbody.appendChild(tr);

            // Mobile Card
            const card = document.createElement('div');
            card.className =
                'bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1';
            card.innerHTML = `
                    <p class="text-sm font-medium text-gray-800">${escapeHTML(r.teacher_name)}</p>
                    <p class="text-sm text-gray-700">${escapeHTML(r.supply_name)} (${escapeHTML(r.variant_requested || '-')})</p>
                    <p class="text-xs text-gray-600">Quantity: ${escapeHTML(r.quantity_requested || '0')}</p>
                    <p class="text-xs text-gray-600">Date: ${escapeHTML(r.request_date)}</p>
                    <div class="mt-2">${statusBadge(r.status)}</div>
                `;
            mobileGrid.appendChild(card);
        });
    }

    function renderTransactions() {
        const tbody = document.getElementById('transactionTableBody');
        const mobileGrid = document.getElementById('mobileCards');
        const search = document.getElementById('searchInput').value.toLowerCase();
        const filter = document.getElementById('statusFilter').value;
        const noRow = document.getElementById('noResultsRow');
        const noCard = document.getElementById('noResultsCard');

        // clear content
        tbody.innerHTML = '';
        mobileGrid.innerHTML = '';

        const filtered = allTransactions.filter(r => {
            const matchesName = (r.teacher_name || '').toLowerCase().includes(search);
            const matchesStatus = filter === "All" || (r.status || '') === filter;
            return matchesName && matchesStatus;
        });

        // ✅ Handle "no results" once, with proper visibility rules
        if (filtered.length === 0) {
            // Show desktop-only row
            noRow.classList.remove('hidden');
            noRow.classList.add('hidden', 'md:table-row'); // only visible on desktop table

            // Show mobile-only text
            noCard.classList.remove('hidden');
            noCard.classList.add('md:hidden'); // visible only on mobile

            return;
        }

        // hide "no results"
        noRow.classList.add('hidden');
        noCard.classList.add('hidden');

        filtered.forEach(r => {
            // Desktop Row
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="p-3">${escapeHTML(r.teacher_name)}</td>
            <td class="p-3">${escapeHTML(r.supply_name)}</td>
            <td class="p-3">${escapeHTML(r.variant_requested || '-')}</td>
            <td class="p-3">${escapeHTML(r.quantity_requested || '0')}</td>
            <td class="p-3">${escapeHTML(r.request_date)}</td>
            <td class="p-3">${statusBadge(r.status)}</td>
        `;
            tbody.appendChild(tr);

            // Mobile Card
            const card = document.createElement('div');
            card.className =
                'bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1';
            card.innerHTML = `
            <p class="text-sm font-medium text-gray-800">${escapeHTML(r.teacher_name)}</p>
            <p class="text-sm text-gray-700">${escapeHTML(r.supply_name)} (${escapeHTML(r.variant_requested || '-')})</p>
            <p class="text-xs text-gray-600">Quantity: ${escapeHTML(r.quantity_requested || '0')}</p>
            <p class="text-xs text-gray-600">Date: ${escapeHTML(r.request_date)}</p>
            <div class="mt-2">${statusBadge(r.status)}</div>
        `;
            mobileGrid.appendChild(card);
        });
    }


    document.getElementById('searchInput').addEventListener('input', renderTransactions);
    document.getElementById('statusFilter').addEventListener('change', renderTransactions);

    loadTransactions();
    </script>
</body>

</html>