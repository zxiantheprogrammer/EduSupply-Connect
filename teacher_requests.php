<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
  header('Location: login.html');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

  <style>
    body,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    input,
    select,
    button,
    p,
    label {
      font-family: 'Parkinsans', sans-serif;
    }

    body {
      background-color: #fafafa;
      color: #1f2937;
      letter-spacing: -0.01em;
    }

    .status-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 9999px;
    }

    .status-pending {
      background-color: #fef3c7;
      color: #b45309;
    }

    .status-approved {
      background-color: #dcfce7;
      color: #166534;
    }

    .status-rejected {
      background-color: #fee2e2;
      color: #991b1b;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen flex font-[Plus Jakarta Sans] text-[0.9rem]">
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-4 sm:p-6">
    <h2 class="text-xl font-semibold mb-5 flex items-center gap-2 text-gray-800">
      <i data-lucide="list-checks" class="w-5 h-5 text-orange-600"></i> My Submitted Requests
    </h2>

    <div class="mb-4 flex items-center gap-3">
      <label for="statusFilter" class="text-sm font-medium text-gray-700">Filter by Status:</label>
      <select id="statusFilter" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm" onchange="renderFilteredRequests()">
        <option value="All">All</option>
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
      </select>
    </div>

    <!-- Table for Desktop/Tablet -->
    <div class="hidden md:flex justify-center">
      <div class="overflow-x-auto rounded-2xl shadow-sm border border-gray-100 bg-white w-full max-w-5xl">
        <table class="min-w-full divide-y divide-gray-200 text-center">
          <thead class="bg-orange-500 text-white">
            <tr>
              <th class="p-2">Date</th>
              <th class="p-2">Item</th>
              <th class="p-2">Variant</th>
              <th class="p-2">Quantity</th>
              <th class="p-2">Status</th>
            </tr>
          </thead>
          <tbody id="desktopTableBody" class="transition-opacity duration-500"></tbody>
        </table>
      </div>
    </div>

    <!-- Cards for Mobile -->
    <div id="mobileCards" class="md:hidden flex flex-col items-center gap-4 transition-opacity duration-500"></div>
  </main>

  <script>
    function statusBadge(status) {
      if (status === "Pending") return `<span class="status-badge status-pending">Pending</span>`;
      if (status === "Approved") return `<span class="status-badge status-approved">Approved</span>`;
      if (status === "Rejected") return `<span class="status-badge status-rejected">Rejected</span>`;
      return `<span class="status-badge bg-gray-100 text-gray-800">${status}</span>`;
    }

    function formatDate(dateStr) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-US', options);
    }

    let allRequests = [];

    async function loadRequests() {
      try {
        const res = await fetch('/requests/my_requests.php');
        if (!res.ok) throw new Error('Failed to load requests');
        allRequests = await res.json();
        renderFilteredRequests();
      } catch (err) {
        document.getElementById('desktopTableBody').innerHTML =
          `<tr><td colspan="5" class="p-4 text-red-600 text-center">Error: ${err.message}</td></tr>`;
        document.getElementById('mobileCards').innerHTML =
          `<p class="text-red-600 text-center py-10">Error: ${err.message}</p>`;
      }
      lucide.createIcons();
    }

    function renderFilteredRequests() {
      const filter = document.getElementById('statusFilter').value;
      let filtered = allRequests.filter(r => filter === 'All' || r.status === filter);

      // Sort by date (latest first)
      filtered.sort((a, b) => new Date(b.request_date) - new Date(a.request_date));

      const desktopTbody = document.getElementById('desktopTableBody');
      const mobileGrid = document.getElementById('mobileCards');
      desktopTbody.innerHTML = '';
      mobileGrid.innerHTML = '';

      if (filtered.length === 0) {
        desktopTbody.innerHTML =
          `<tr><td colspan="5" class="p-4 text-center text-gray-500">No requests found.</td></tr>`;
        mobileGrid.innerHTML = `<p class="text-gray-500 text-center py-10">No requests found.</p>`;
        return;
      }

      filtered.forEach(r => {
        const dateFormatted = formatDate(r.request_date);
        const item = r.item_name || r.items;
        const variant = r.variant || '-';
        const quantity = r.quantity || '-';

        // Desktop table row
        desktopTbody.innerHTML += `
          <tr class="hover:bg-gray-50 transition">
            <td class="p-2 text-sm text-gray-700">${dateFormatted}</td>
            <td class="p-2 text-sm text-gray-800">${item}</td>
            <td class="p-2 text-sm text-gray-800">${variant}</td>
            <td class="p-2 text-sm text-gray-800">${quantity}</td>
            <td class="p-2 text-sm">${statusBadge(r.status)}</td>
          </tr>
        `;

        // Mobile card
        mobileGrid.innerHTML += `
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2 hover:shadow-lg transition w-full max-w-sm text-center">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-700 font-medium">${dateFormatted}</span>
              ${statusBadge(r.status)}
            </div>
            <p class="text-sm text-gray-800"><strong>Item:</strong> ${item}</p>
            <p class="text-sm text-gray-800"><strong>Variant:</strong> ${variant}</p>
            <p class="text-sm text-gray-800"><strong>Quantity:</strong> ${quantity}</p>
          </div>
        `;
      });
    }

    // ---- AUTO REFRESH (Smooth) ----
    setInterval(async () => {
      try {
        const res = await fetch('/requests/my_requests.php');
        if (!res.ok) throw new Error('Failed to refresh requests');
        const data = await res.json();

        // Compare old vs new data
        const newData = JSON.stringify(data);
        const oldData = JSON.stringify(allRequests);
        if (newData !== oldData) {
          allRequests = data;
          fadeUpdate(() => renderFilteredRequests());
        }
      } catch (err) {
        console.error('Auto-refresh failed:', err);
      }
    }, 10000); // every 10 seconds

    // Smooth fade animation for updates
    function fadeUpdate(updateFn) {
      const desktop = document.getElementById('desktopTableBody');
      const mobile = document.getElementById('mobileCards');
      desktop.style.opacity = '0';
      mobile.style.opacity = '0';
      setTimeout(() => {
        updateFn();
        desktop.style.opacity = '1';
        mobile.style.opacity = '1';
      }, 400);
    }

    // Initial load
    loadRequests();
  </script>
</body>
</html>
