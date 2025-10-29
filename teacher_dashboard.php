<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: auth.html');
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

    .cart-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background-color: #2a16adff;
        color: white;
        font-size: 0.7rem;
        padding: 1px 5px;
        border-radius: 9999px;
        font-weight: bold;
    }

    #cartPanel {
        transition: transform 0.3s ease-in-out;
    }

    #cartOverlay {
        background-color: rgba(0, 0, 0, 0.5);
        transition: opacity 0.3s ease-in-out;
    }

    #toastContainer {
        position: fixed;
        top: 1rem;
        left: 70%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        z-index: 50;
        pointer-events: none;
    }

    @keyframes slide-down {
        0% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }

        100% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slide-down 0.3s ease forwards;
    }

    @media (max-width: 768px) {
        #cartPanel {
            width: 100%;
            bottom: 0;
            top: auto;
            height: 80vh;
            border-radius: 1.5rem 1.5rem 0 0;
        }
    }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex font-[Plus Jakarta Sans] text-[0.9rem]">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Main Content -->
    <main class="flex-1 p-4 sm:p-6 overflow-x-auto">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-orange-600"></i> Available Supplies
            </h2>
            <button onclick="toggleCart()"
                class="relative bg-orange-500 text-white px-3 py-1.5 rounded-full hover:bg-orange-600 transition flex items-center gap-1.5 text-sm shadow-sm">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> My Cart
                <span id="cartBadge" class="cart-badge hidden">0</span>
            </button>
        </div>

        <div class="mb-6">
            <input id="searchInput" type="text" placeholder="Search supplies..."
                class="w-full max-w-md px-3 py-1.5 border border-orange-300 rounded-full text-sm focus:ring-2 focus:ring-orange-200 focus:outline-none"
                oninput="filterSupplies()" />
        </div>

        <div id="supplyGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5"></div>
    </main>

    <!-- Cart Overlay -->
    <div id="cartOverlay" class="hidden fixed inset-0 z-10 opacity-0" onclick="toggleCart()"></div>

    <!-- Cart Panel -->
    <aside id="cartPanel"
        class="fixed right-0 top-0 bottom-0 w-80 bg-white border-l border-gray-200 shadow-2xl flex flex-col transform translate-x-full z-20 text-sm">
        <div class="flex items-center justify-between px-5 py-3 border-b">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4 text-orange-600"></i> My Cart
            </h3>
            <button onclick="toggleCart()" class="text-gray-500 hover:text-gray-700"><i data-lucide="x"
                    class="w-4 h-4"></i></button>
        </div>
        <ul id="cartItems" class="flex-1 overflow-y-auto px-5 py-3 space-y-2"></ul>
        <div class="p-3 border-t bg-white sticky bottom-0 left-0">
            <button onclick="openModal('confirmModal')"
                class="w-full bg-orange-500 text-white py-2 rounded-full hover:bg-orange-600 transition font-semibold text-sm shadow">Submit
                Requests</button>
        </div>
    </aside>

    <!-- Confirm Modal -->
    <div id="confirmModal"
        class="fixed inset-0 hidden z-50 bg-black/50 flex items-center justify-center transition-all duration-300">
        <div class="bg-white rounded-xl p-6 w-80 flex flex-col gap-4 shadow-2xl transform scale-100 animate-fadeIn">
            <h3 class="text-lg font-semibold text-gray-800 text-center">Confirm Submission</h3>
            <p class="text-sm text-gray-600 text-center">Are you sure you want to submit your cart?</p>

            <div class="flex justify-center gap-3 mt-2">
                <button onclick="closeModal('confirmModal')"
                    class="px-4 py-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">Cancel</button>
                <button onclick="submitCart()"
                    class="px-4 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white shadow-md transition">Yes,
                    Submit</button>
            </div>
        </div>
    </div>

    <!-- Single Request Modal -->
    <div id="singleRequestModal"
        class="fixed inset-0 hidden z-50 bg-black/50 flex items-center justify-center transition-all duration-300">
        <div class="bg-white rounded-xl p-6 w-80 flex flex-col gap-4 shadow-2xl transform scale-100 animate-fadeIn">
            <h3 class="text-lg font-semibold text-gray-800 text-center">Confirm Request</h3>
            <p id="singleRequestText" class="text-sm text-gray-600 text-center"></p>

            <div class="flex justify-center gap-3 mt-2">
                <button onclick="closeModal('singleRequestModal')"
                    class="px-4 py-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
                    Cancel
                </button>
                <button id="submitSingleRequestBtn"
                    class="px-4 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white shadow-md transition">
                    Submit Request
                </button>
            </div>
        </div>
    </div>

    <script>
    let cart = {}; // key = supplyId__variant
    let allSupplies = [];
    let pendingSingleRequest = null;

    // --------------------- Toast ---------------------
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const colors = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            error: 'bg-red-500'
        };
        const icons = {
            info: 'info',
            success: 'check-circle',
            error: 'alert-circle'
        };
        const toast = document.createElement('div');
        toast.className =
            `flex items-center gap-3 px-6 py-3 text-white rounded-lg shadow-lg animate-slide-in ${colors[type]} text-base`;
        toast.innerHTML = `<i data-lucide="${icons[type]}" class="w-6 h-6"></i><span>${message}</span>`;
        container.appendChild(toast);
        lucide.createIcons();
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition', 'duration-500');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // --------------------- Modals ---------------------
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // --------------------- Load Supplies ---------------------
    async function loadSupplies() {
        try {
            const res = await fetch('/supplies/list.php');
            if (!res.ok) throw new Error('Failed to load supplies');
            allSupplies = await res.json();
            renderSupplies(allSupplies);
            await loadCart();
        } catch (err) {
            document.getElementById('supplyGrid').innerHTML =
                `<p class="text-red-600 col-span-full text-center py-10">${err.message}</p>`;
        }
    }

    // --------------------- Render Supplies ---------------------
    function renderSupplies(supplies) {
        const grid = document.getElementById('supplyGrid');
        grid.innerHTML = '';
        if (!supplies.length) {
            grid.innerHTML = `<p class="text-gray-500 col-span-full text-center py-10">No results found</p>`;
            return;
        }

        supplies.forEach(supply => {
            const firstVariantAvailable = (supply.variants && supply.variants.length) ?
                supply.variants[0].quantity_available :
                (supply.quantity_available || 0);
            const displayAvailability = firstVariantAvailable === 0 ? 'Out of Stock' : firstVariantAvailable;
            const isRequestDisabled = firstVariantAvailable === 0;

            const imageTag = supply.image ?
                `<div class="w-full h-44 bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                <img src="/${supply.image}" alt="${supply.name}" class="max-h-full max-w-full object-contain transition-transform duration-300 hover:scale-105"/>
            </div>` :
                `<div class="w-full h-44 bg-gray-100 rounded-lg mb-3 flex items-center justify-center text-gray-400 text-xs">No Image</div>`;

            const variantOptions = (supply.variants && supply.variants.length > 0) ?
                supply.variants.map(v =>
                    `<option value="${v.variant_name}" data-available="${v.quantity_available}">${v.variant_name}</option>`
                ).join('') :
                '';
            const variantSelect = variantOptions ?
                `<div class="mt-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Variant:</label>
                <select id="variant-${supply.supply_id}" onchange="updateVariantAvailability('${supply.supply_id}')">${variantOptions}</select>
            </div>` :
                '';

            const card = document.createElement('div');
            card.className =
                "bg-white rounded-2xl shadow-sm border border-orange-500 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col p-4 group";
            card.innerHTML = `
            ${imageTag}
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 group-hover:text-orange-600 transition">${supply.name}</h3>
                    <p class="text-xs text-gray-500 mt-1">Available: <span id="avail-${supply.supply_id}" class="font-medium text-orange-600">${displayAvailability}</span></p>
                </div>
                ${variantSelect}
            </div>
            <button data-supply-id="${supply.supply_id}" onclick="addToCart('${supply.supply_id}','${supply.name}')" class="mt-4 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold py-2 rounded-full flex items-center justify-center gap-1 transition active:scale-95">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
            </button>
            <button data-supply-id="${supply.supply_id}" onclick="prepareSingleRequest('${supply.supply_id}','${supply.name}')" class="mt-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2 rounded-full flex items-center justify-center gap-1 transition active:scale-95" ${isRequestDisabled ? 'disabled opacity-50 cursor-not-allowed' : ''}>
                <i data-lucide="plus" class="w-4 h-4"></i> Request
            </button>
        `;
            grid.appendChild(card);
        });

        lucide.createIcons();
    }

    // --------------------- Update Variant Availability ---------------------
    function updateVariantAvailability(id) {
        const select = document.getElementById(`variant-${id}`);
        if (!select) return;
        const available = parseInt(select.selectedOptions[0].dataset.available) || 0;
        document.getElementById(`avail-${id}`).textContent = available === 0 ? 'Out of Stock' : available;

        const requestBtn = select.parentElement.parentElement.querySelector('button.bg-green-500');
        if (available === 0) {
            requestBtn.disabled = true;
            requestBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            requestBtn.disabled = false;
            requestBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // --------------------- Search ---------------------
    function filterSupplies() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        renderSupplies(allSupplies.filter(s => s.name.toLowerCase().includes(query)));
    }

    // --------------------- Cart Functions ---------------------
    async function loadCart() {
        try {
            const res = await fetch('/teachers/cart_handler.php?action=fetch');
            if (!res.ok) throw new Error('Failed to fetch cart');
            const items = await res.json();
            cart = {};
            items.forEach(item => {
                const key = item.supply_id + '__' + (item.variant || '-');
                cart[key] = {
                    supply_id: item.supply_id,
                    name: item.name,
                    variant: item.variant || '-',
                    quantity: parseInt(item.quantity),
                    available: parseInt(item.quantity_available) || 0
                };
            });
            renderCart();
            updateBadge();
        } catch (err) {
            console.error(err);
        }
    }

    async function addToCart(id, name) {
        const variantSelect = document.getElementById(`variant-${id}`);
        const variant = variantSelect ? variantSelect.value : '-';
        const available = variantSelect ? parseInt(variantSelect.selectedOptions[0].dataset.available) : 0;
        const key = id + '__' + variant;

        if (cart[key]) {
            return showToast(`${name}${variant!=='-'?' ('+variant+')':''} is already in your cart.`, 'error');
        }

        const formData = new URLSearchParams();
        formData.append('action', 'add');
        formData.append('supply_id', id);
        formData.append('variant', variant);
        formData.append('quantity', 1);

        try {
            const res = await fetch('/teachers/cart_handler.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                await loadCart();
                showToast(`${name}${variant!=='-'?' ('+variant+')':''} added to cart!`, 'success');
            } else showToast(data.error || 'Failed to add item.', 'error');
        } catch (err) {
            showToast('Failed to add item: ' + err.message, 'error');
        }
    }



    async function removeFromCart(id, variant = '-') {
        const key = id + '__' + variant;
        if (!cart[key]) return;

        const formData = new URLSearchParams();
        formData.append('action', 'remove');
        formData.append('supply_id', id);
        formData.append('variant', variant);

        try {
            const res = await fetch('/teachers/cart_handler.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                delete cart[key];
                renderCart();
                updateBadge();
                showToast('Item removed from cart', 'info');
            } else showToast(data.error || 'Failed to remove item.', 'error');
        } catch (err) {
            showToast('Failed to remove item: ' + err.message, 'error');
        }
    }

    function renderCart() {
        const list = document.getElementById('cartItems');
        list.innerHTML = '';
        const entries = Object.values(cart);
        if (!entries.length) {
            list.innerHTML = `<p class="text-gray-500 text-center py-10">Your cart is empty 🛒</p>`;
            return;
        }
        entries.forEach(item => {
            const li = document.createElement('li');
            li.className = "border-b pb-3 flex flex-col gap-3";
            li.innerHTML = `
            <div class="flex flex-col w-full gap-2">
                <span class="font-medium text-sm">${item.name}</span>
                ${item.variant!=='-'?`<div class="flex items-center gap-2"><label class="text-xs text-gray-500 w-16">Variant:</label><span class="text-xs text-gray-700 flex-1">${item.variant}</span></div>`:''}
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-16">Quantity:</label>
                    <span class="text-xs text-gray-700">${item.quantity}</span>
                    <button class="text-red-500 text-xs hover:underline remove-btn">Remove</button>
                </div>
            </div>`;
            li.querySelector('.remove-btn').addEventListener('click', () => removeFromCart(item.supply_id, item
                .variant));
            list.appendChild(li);
        });
    }

    function updateBadge() {
        const badge = document.getElementById('cartBadge');
        const count = Object.keys(cart).length;
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else badge.classList.add('hidden');
    }

    // --------------------- Single Request ---------------------
    function prepareSingleRequest(supply_id, name) {
        const variantSelect = document.getElementById(`variant-${supply_id}`);
        const variant = variantSelect ? variantSelect.value : '-';
        const available = variantSelect ? parseInt(variantSelect.selectedOptions[0].dataset.available) : 0;
        if (available === 0) return showToast(`Cannot request "${name}" because it is out of stock.`, 'error');
        pendingSingleRequest = {
            supply_id,
            name,
            variant,
            quantity: 1
        };
        document.getElementById('singleRequestText').textContent =
            `Requesting 1 of ${name}${variant!=='-'?' ('+variant+')':''}?`;
        openModal('singleRequestModal');
    }

    document.getElementById('submitSingleRequestBtn').addEventListener('click', async () => {
        closeModal('singleRequestModal');
        if (!pendingSingleRequest) return;
        const item = pendingSingleRequest;
        const formData = new URLSearchParams();
        formData.append('items[]', item.supply_id);
        formData.append(`quantity[${item.supply_id}]`, item.quantity);
        formData.append(`variant[${item.supply_id}]`, item.variant);

        try {
            const res = await fetch('/requests/quick_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            });
            const data = await res.json();
            showToast(data.status === 'success' ? data.message : data.message || 'Failed to submit request',
                data.status === 'success' ? 'success' : 'error');
            pendingSingleRequest = null;
        } catch (err) {
            showToast("Failed to submit request: " + err.message, 'error');
        }
    });

    // --------------------- Toggle Cart Panel ---------------------
    function toggleCart() {
        const panel = document.getElementById('cartPanel');
        const overlay = document.getElementById('cartOverlay');
        const isOpen = !panel.classList.contains('translate-x-full');
        if (isOpen) {
            panel.classList.add('translate-x-full');
            overlay.classList.add('hidden', 'opacity-0');
        } else {
            panel.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        }
    }

    async function submitCart() {
        if (!Object.keys(cart).length) {
            showToast('Your cart is empty!', 'error');
            closeModal('confirmModal');
            return;
        }

        const availableItems = [];
        const unavailableItems = [];

        // Check each item for availability
        Object.entries(cart).forEach(([key, item]) => {
            if (item.available > 0 && item.quantity <= item.available) {
                availableItems.push({
                    key,
                    ...item
                });
            } else {
                unavailableItems.push({
                    key,
                    ...item
                });
            }
        });

        if (!availableItems.length) {
            showToast('None of the items in your cart are available.', 'error');
            closeModal('confirmModal');
            return;
        }

        const formData = new URLSearchParams();
        formData.append('action', 'bulk_request'); // PHP knows this is a bulk request

        availableItems.forEach(item => {
            formData.append('items[]', item.supply_id);
            const variant = item.variant || 'default';
            formData.append(`quantity[${item.supply_id}][${variant}]`, item.quantity);
            formData.append(`variant[${item.supply_id}][]`, variant);
        });

        try {
            // Submit bulk request
            const res = await fetch('/requests/bulk_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            });
            const data = await res.json();

            if (data.status === 'success') {
                showToast(data.message, 'success');

                // Remove submitted items from server cart
                try {
                    const removeForm = new URLSearchParams();
                    removeForm.append('action', 'clear');
                    availableItems.forEach(item => removeForm.append('items[]', item.supply_id));

                    const removeRes = await fetch('/teachers/cart_handler.php', {
                        method: 'POST',
                        body: removeForm
                    });
                    const removeData = await removeRes.json();
                    if (!removeData.success) console.warn('Server cart not fully cleared');
                } catch (err) {
                    console.error('Failed to update server cart:', err);
                }

                // Remove from frontend cart
                availableItems.forEach(item => delete cart[item.key]);
                renderCart();
                updateBadge();

                if (unavailableItems.length) {
                    showToast('Some items are unavailable and remain in your cart.', 'error');
                }
            } else {
                showToast(data.message || 'Failed to submit cart', 'error');
            }
        } catch (err) {
            showToast('Failed to submit cart: ' + err.message, 'error');
        } finally {
            closeModal('confirmModal');
        }
    }




    // --------------------- Init ---------------------
    loadSupplies();
    </script>


</body>

</html>