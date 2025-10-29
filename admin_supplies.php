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
  <title>Admin - Supplies</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-blue-900 text-white flex flex-col min-h-screen">
    <div class="p-6 text-2xl font-bold border-b border-blue-700 flex items-center gap-2">
      <i data-lucide="settings" class="w-6 h-6"></i> Admin Panel
    </div>
    <nav class="flex-1 mt-6 space-y-1 px-4">
      <a href="admin_dashboard.php" class="flex items-center gap-2 px-4 py-2 rounded bg-blue-700">
        <i data-lucide="package" class="w-5 h-5"></i> <span>Supplies</span>
      </a>
      <a href="admin_teachers.php" class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-700">
        <i data-lucide="users" class="w-5 h-5"></i> <span>Teachers</span>
      </a>
      <a href="admin_requests.php" class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-700">
        <i data-lucide="file-text" class="w-5 h-5"></i> <span>Requests</span>
      </a>
      <a href="logout.php" class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-700">
        <i data-lucide="user" class="w-5 h-5"></i> <span>Logout</span>
      </a>
    </nav>
    <div class="p-4 border-t border-blue-700 text-sm text-blue-100">
      Logged in as: <strong>Admin</strong>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8 space-y-10 overflow-x-auto">
    <h1 class="text-3xl font-bold text-blue-800 flex items-center gap-2">
      <i data-lucide="boxes" class="w-6 h-6"></i> Manage Supplies
    </h1>

    <!-- Add Supply Form -->
    <form id="supplyForm" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 bg-white p-6 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="name" type="text" placeholder="Supply Name"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <input name="description" type="text" placeholder="Description"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <input name="quantity" type="number" placeholder="Total Quantity"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <input name="unit" type="text" placeholder="Unit (e.g., pcs)"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <!-- Image Upload -->
      <input name="image" type="file" accept="image/*"
        class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />

      <!-- Variants Section -->
      <div class="border rounded-lg p-4 bg-gray-50">
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-blue-800 font-semibold text-lg flex items-center gap-2">
            <i data-lucide="layers" class="w-5 h-5"></i> Variants
          </h3>
          <button type="button" id="addVariant"
            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Variant
          </button>
        </div>

        <div id="variantContainer" class="space-y-2">
          <div class="variantRow grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
            <input name="variant_name[]" type="text" placeholder="Variant Name"
              class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <input name="variant_quantity[]" type="number" placeholder="Quantity"
              class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button type="button"
              class="removeVariant bg-red-600 hover:bg-red-700 text-white rounded px-3 py-2 text-sm">Remove</button>
          </div>
        </div>
      </div>

      <button type="submit"
        class="bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition w-full mt-4">Add Supply</button>
    </form>

    <!-- Supplies Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full">
        <thead class="bg-blue-100 text-blue-900 text-sm">
          <tr>
            <th class="p-3 text-left">Item</th>
            <th class="p-3 text-left">Description</th>
            <th class="p-3 text-left">Total Qty</th>
            <th class="p-3 text-left">Unit</th>
            <th class="p-3 text-left">Variants</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody id="supplyTableBody" class="divide-y divide-gray-200"></tbody>
      </table>
    </div>
  </main>

  <script>
    async function loadSupplies() {
      const res = await fetch('supplies/list.php');
      const data = await res.json();
      const tbody = document.getElementById('supplyTableBody');
      tbody.innerHTML = '';

      data.forEach(supply => {
        const totalQty = Array.isArray(supply.variants)
          ? supply.variants.reduce((sum, v) => sum + (parseInt(v.quantity_available) || 0), 0)
          : (supply.quantity_available || 0);

        const imageTag = supply.image
          ? `<img src="${supply.image}" alt="${supply.name}" class="w-10 h-10 object-cover rounded-full" />`
          : `<div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-xs text-gray-500">N/A</div>`;

        const variantDetails = (supply.variants && supply.variants.length > 0)
          ? supply.variants.map(v => `
              <div class="text-xs text-gray-700 flex justify-between border-b border-gray-100 py-0.5">
                <span>${v.variant_name}</span>
                <span class="text-gray-600">${v.quantity_available}</span>
              </div>
            `).join('')
          : `<span class="text-gray-400 text-xs">No variants</span>`;

        tbody.innerHTML += `
          <tr class="hover:bg-gray-50 transition">
            <td class="p-3 flex items-center gap-3">${imageTag}<span class="text-sm font-medium text-gray-800">${supply.name}</span></td>
            <td class="p-3 text-sm text-gray-600">${supply.description || '-'}</td>
            <td class="p-3 text-sm text-blue-700 font-semibold">${totalQty}</td>
            <td class="p-3 text-sm text-gray-600">${supply.unit || '-'}</td>
            <td class="p-3">
              <div class="bg-gray-50 rounded-md border border-gray-200 p-2 max-h-24 overflow-y-auto">${variantDetails}</div>
            </td>
            <td class="p-3 text-sm">
              <button onclick="deleteSupply(${supply.supply_id})" class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
        `;
      });

      lucide.createIcons();
    }

    // Add Variant Row
    document.getElementById('addVariant').addEventListener('click', () => {
      const container = document.getElementById('variantContainer');
      const row = document.createElement('div');
      row.className = 'variantRow grid grid-cols-1 md:grid-cols-3 gap-2 items-center';
      row.innerHTML = `
        <input name="variant_name[]" type="text" placeholder="Variant Name"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <input name="variant_quantity[]" type="number" placeholder="Quantity"
          class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="button"
          class="removeVariant bg-red-600 hover:bg-red-700 text-white rounded px-3 py-2 text-sm">Remove</button>
      `;
      container.appendChild(row);
    });

    // Remove Variant Row
    document.addEventListener('click', e => {
      if (e.target.classList.contains('removeVariant')) {
        e.target.closest('.variantRow').remove();
      }
    });

    // Handle Form Submission
    document.getElementById('supplyForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      // Gather variants into JSON
      const names = [...document.querySelectorAll('input[name="variant_name[]"]')].map(i => i.value.trim());
      const qtys = [...document.querySelectorAll('input[name="variant_quantity[]"]')].map(i => parseInt(i.value) || 0);

      const variants = names
        .map((n, i) => n ? { variant_name: n, quantity_available: qtys[i] } : null)
        .filter(v => v !== null);

      formData.append('variants', JSON.stringify(variants));

      const res = await fetch('supplies/add.php', { method: 'POST', body: formData });
      const data = await res.json();

      alert(data.message || 'Operation complete');
      if (data.status === 'success') {
        this.reset();
        document.getElementById('variantContainer').innerHTML = ''; // clear variant rows
        loadSupplies();
      }
    });

    async function deleteSupply(id) {
      if (!confirm('Delete this supply?')) return;
      const res = await fetch('supplies/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `supply_id=${id}`
      });
      const msg = await res.text();
      alert(msg);
      loadSupplies();
    }

    loadSupplies();
    lucide.createIcons();
  </script>

</body>
</html>
