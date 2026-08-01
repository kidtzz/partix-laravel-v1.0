<div class="pos-container">
    <!-- Left Side: Product Grid & Search -->
    <div class="pos-products">
        <div class="pos-header">
            <h2 class="view-title">
                <i class='bx bx-cart'></i> Point of Sale
            </h2>
            <div class="search-bar">
                <i class='bx bx-barcode-reader'></i>
                <input type="text" id="posSearch" placeholder="Scan Barcode atau Cari Barang..." autofocus>
            </div>
        </div>

        <div class="product-grid" id="posProductGrid">
            <!-- Mock Products -->
            <div class="product-card glass-card" onclick="addToCart('B01', 'Kampas Rem Depan Vario', 45000)">
                <div class="product-image">
                    <i class='bx bx-wrench'></i>
                </div>
                <div class="product-info">
                    <h4 class="product-name">Kampas Rem Depan Vario</h4>
                    <span class="product-price">Rp 45.000</span>
                    <span class="product-stock">Stok: 15</span>
                </div>
            </div>

            <div class="product-card glass-card" onclick="addToCart('B02', 'Oli Mesin MPX 2', 55000)">
                <div class="product-image">
                    <i class='bx bxs-color-fill'></i>
                </div>
                <div class="product-info">
                    <h4 class="product-name">Oli Mesin MPX 2</h4>
                    <span class="product-price">Rp 55.000</span>
                    <span class="product-stock">Stok: 30</span>
                </div>
            </div>

            <div class="product-card glass-card" onclick="addToCart('B03', 'Busi NGK', 25000)">
                <div class="product-image">
                    <i class='bx bxs-zap'></i>
                </div>
                <div class="product-info">
                    <h4 class="product-name">Busi NGK Standard</h4>
                    <span class="product-price">Rp 25.000</span>
                    <span class="product-stock">Stok: 50</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Checkout Success Modal via JS -->

    <!-- Right Side: Cart / Checkout -->
    <div class="pos-cart glass-effect">
        <div class="cart-header">
            <h3>Keranjang</h3>
            <span class="cart-count badge badge-primary" id="cartItemCount">0 Item</span>
        </div>

        <div class="cart-items" id="cartItemsContainer">
            <div class="empty-cart-state">
                <i class='bx bx-cart'></i>
                <p>Keranjang masih kosong</p>
            </div>
            <!-- Cart items will be injected here -->
        </div>

        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">Rp 0</span>
            </div>

            <x-input-group label="Tipe Harga / Customer" class="mt-4">
                <select class="input-control" id="tipeHarga" onchange="changeTipeHarga()">
                    <option value="Regular" id="optHrgRegular">Regular (0%)</option>
                    <option value="Member" id="optHrgMember">Member (-5%)</option>
                    <option value="Langganan" id="optHrgLangganan">Langganan (-10%)</option>
                    <option value="Bengkel" id="optHrgBengkel">Bengkel / Reseller (-15%)</option>
                    <option value="Teman" id="optHrgTeman">Teman / Kenalan (-20%)</option>
                    <option value="Grosir" id="optHrgGrosir">Grosir / VIP (-25%)</option>
                </select>
            </x-input-group>

            <x-input-group label="Potongan Manual (Diskon Kasir)" class="mt-3">
                <input type="number" class="input-control" id="potonganPenjualan" placeholder="Cth: 5000" min="0" oninput="updateCartUI()">
            </x-input-group>

            <x-input-group label="Metode Bayar" class="mt-3">
                <select class="input-control" id="metodeBayar" onchange="toggleCashInput()">
                    <option value="Cash">Cash (Tunai)</option>
                    <option value="Transfer">Transfer Bank</option>
                    <option value="QRIS">QRIS</option>
                </select>
            </x-input-group>

            <div id="cashInputContainer">
                <x-input-group label="Uang Diterima" class="mt-3">
                    <input type="number" class="input-control" id="uangDiterima" placeholder="Cth: 100000">
                </x-input-group>
            </div>

            <div class="summary-row" style="color: var(--text-muted); font-size: 12px; margin-bottom: 4px; display: flex; justify-content: space-between;">
                <span>Subtotal</span>
                <span id="cartSubtotal">Rp 0</span>
            </div>
            <div class="summary-row" style="color: var(--danger-color); font-size: 12px; margin-bottom: 8px; display: flex; justify-content: space-between;">
                <span>Potongan</span>
                <span id="cartPotongan">- Rp 0</span>
            </div>
            <div class="summary-row total" style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                <span>Total Tagihan</span>
                <span id="cartTotal">Rp 0</span>
            </div>

            <button class="btn btn-primary btn-checkout" onclick="processCheckout()">
                <i class='bx bx-check-circle'></i> Bayar Sekarang
            </button>
        </div>
        <!-- Mobile Floating Cart Button -->
        <button class="mobile-cart-toggle-btn" id="mobileCartToggleBtn" onclick="scrollToCartMobile()">
            <i class='bx bx-cart-alt'></i>
            <span>Keranjang Belanja</span>
            <span class="cart-badge-count" id="mobileCartCount">0</span>
        </button>
    </div>

    <style>
        .pos-container {
            display: flex;
            gap: 24px;
            height: 100%;
        }

        .pos-products {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .pos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            overflow-y: auto;
            padding-bottom: 24px;
            padding-right: 8px;
            /* For scrollbar */
        }

        .product-card {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .product-image {
            height: 120px;
            background: rgba(79, 70, 229, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }

        .product-info {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .product-name {
            font-size: 12px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            font-weight: 700;
            color: var(--primary-color);
        }

        .product-stock {
            font-size: 10px;
            color: var(--text-muted);
        }

        /* Cart */
        .pos-cart {
            width: 360px;
            border-radius: var(--radius-xl);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .cart-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .empty-cart-state {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            opacity: 0.7;
        }

        .empty-cart-state i {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .cart-item {
            background: white;
            border-radius: var(--radius-md);
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-solid);
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 12px;
        }

        .cart-item-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F3F4F6;
            border-radius: var(--radius-full);
            padding: 4px;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            color: var(--text-main);
        }

        .qty-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .cart-summary {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-top: 1px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .summary-row.total {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 16px;
            margin-bottom: 24px;
        }

        .btn-checkout {
            width: 100%;
            padding: 14px;
            font-size: 14px;
        }
    </style>

    <script>
        let posCart = [];
        let masterBarangPOS = [];

        function formatRupiah(amount) {
            if (isNaN(amount)) return "Rp 0";
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        }

        // Dipanggil dari js.html atau pada load view
        function initPOS() {
            if (AppState.user.role === "Restocker") return; // Restocker tak punya akses

            const grid = document.getElementById('posProductGrid');
            grid.innerHTML = `<div style="padding:20px; text-align:center; grid-column: 1 / -1;"><i class='bx bx-loader-alt bx-spin' style='font-size: 20px;'></i> Memuat Data Barang...</div>`;

            BackendAPI.call('getPengaturanDiskon').then(diskon => {
                const optMem = document.getElementById('optHrgMember');
                if (optMem) optMem.textContent = `Member (-${diskon.DISKON_MEMBER || 5}%)`;
                const optLan = document.getElementById('optHrgLangganan');
                if (optLan) optLan.textContent = `Langganan (-${diskon.DISKON_LANGGANAN || 10}%)`;
                const optBeng = document.getElementById('optHrgBengkel');
                if (optBeng) optBeng.textContent = `Bengkel / Reseller (-${diskon.DISKON_BENGKEL || 15}%)`;
                const optTem = document.getElementById('optHrgTeman');
                if (optTem) optTem.textContent = `Teman / Kenalan (-${diskon.DISKON_TEMAN || 20}%)`;
                const optGro = document.getElementById('optHrgGrosir');
                if (optGro) optGro.textContent = `Grosir / VIP (-${diskon.DISKON_GROSIR || 25}%)`;
            }).catch(e => {});

            BackendAPI.call('getBarangUntukPOS').then(data => {
                masterBarangPOS = data;
                renderPOSGrid();
            }).catch(err => {
                showToast("Gagal memuat barang: " + err.message, "error");
            });
        }

        let currentPosFilter = 'Semua';

        function setPosFilter(btn, category) {
            document.querySelectorAll('.filter-chip').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
            currentPosFilter = category;
            renderPOSGrid();
        }

        function renderPOSGrid() {
            const grid = document.getElementById('posProductGrid');
            const tipeHarga = 'Regular'; 
            const keyword = document.getElementById('posSearch').value.toLowerCase();

            let filtered = masterBarangPOS;

            if (currentPosFilter !== 'Semua') {
                filtered = filtered.filter(b => 
                    b.nama_barang && b.nama_barang.toLowerCase().includes(currentPosFilter.toLowerCase())
                );
            }

            if (keyword) {
                filtered = filtered.filter(b => 
                    (b.nama_barang && b.nama_barang.toLowerCase().includes(keyword)) || 
                    (b.barcode && String(b.barcode).toLowerCase().includes(keyword)) ||
                    (b.id_barang && String(b.id_barang).toLowerCase().includes(keyword))
                );
            }

            if (filtered.length === 0) {
                grid.innerHTML = `<div style="padding:20px; text-align:center; grid-column: 1 / -1; color:var(--text-muted);">Barang tidak ditemukan.</div>`;
                return;
            }

            grid.innerHTML = filtered.map(b => `
            <div class="product-card glass-card" onclick="addToCart('${b.id_barang}')">
                <div class="product-image">
                    ${b.gambar_url ? `<img src="${b.gambar_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class='bx bx-package'></i>`}
                </div>
                <div class="product-info">
                    <h4 class="product-name" style="font-size:14px; font-weight:700;">${b.nama_barang}</h4>
                    <span class="product-price" style="font-size:16px;">${formatRupiah(b.harga[tipeHarga] || 0)}</span>
                    <span class="product-stock" style="font-weight:600; color: ${b.stok_saat_ini <= 5 ? 'var(--danger-color)' : 'var(--text-muted)'}">Sisa Stok: ${b.stok_saat_ini} PCS</span>
                </div>
            </div>
        `).join('');
        }

        // Barcode Listener (Section 9)
        document.getElementById('posSearch').addEventListener('input', function() {
            renderPOSGrid();
        });

        document.getElementById('posSearch').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim().toLowerCase();
                if (!barcode) return;

                // Local exact match for instant response (no API delay)
                const exactMatch = masterBarangPOS.find(b => 
                    (b.barcode && String(b.barcode).toLowerCase() === barcode) ||
                    (b.id_barang && String(b.id_barang).toLowerCase() === barcode)
                );

                if (exactMatch) {
                    addToCartData(exactMatch);
                    this.value = '';
                    renderPOSGrid();
                } else {
                    BackendAPI.call('scanBarcodePenjualan', [barcode]).then(b => {
                        addToCartData(b);
                        this.value = '';
                        this.focus();
                        renderPOSGrid();
                    }).catch(err => {
                        showToast(err.message || "Barang tidak ditemukan", "error");
                        this.select(); 
                    });
                }
            }
        });

        // Global Auto-Focus untuk Scanner Kasir
        document.addEventListener('keydown', function(e) {
            if (document.body.classList.contains('pos-fullscreen-mode')) {
                const searchInput = document.getElementById('posSearch');
                // Abaikan jika user sedang mengetik di input manual seperti Diskon/Uang
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
                    // Jangan focus jika menekan tombol modifier (Ctrl, Alt)
                    if (!e.ctrlKey && !e.altKey && e.key.length === 1) {
                        searchInput.focus();
                    }
                }
            }
        });

        function addToCart(id_barang) {
            const b = masterBarangPOS.find(x => x.id_barang === id_barang);
            if (b) addToCartData(b);
        }

        function addToCartData(b) {
            if (b.stok_saat_ini <= 0) {
                return showToast(`Stok ${b.nama_barang} habis!`, "error");
            }

            const existing = posCart.find(item => item.id_barang === b.id_barang);
            if (existing) {
                if (existing.qty >= b.stok_saat_ini) {
                    return showToast(`Maksimal stok tercapai (${b.stok_saat_ini})`, "error");
                }
                existing.qty += 1;
            } else {
                posCart.push({
                    id_barang: b.id_barang,
                    nama_barang: b.nama_barang,
                    harga: b.harga, // Object harga untuk re-kalkulasi saat tipeHarga berubah
                    qty: 1,
                    stok_maksimal: b.stok_saat_ini
                });
            }
            renderCart();
            showToast(`${b.nama_barang} ditambahkan`, 'success');
        }

        function updateQty(id_barang, delta) {
            const item = posCart.find(i => i.id_barang === id_barang);
            if (item) {
                if (delta > 0 && item.qty >= item.stok_maksimal) {
                    return showToast(`Maksimal stok tercapai (${item.stok_maksimal})`, "error");
                }
                item.qty += delta;
                if (item.qty <= 0) {
                    posCart = posCart.filter(i => i.id_barang !== id_barang);
                }
                renderCart();
            }
        }

        function renderCart() {
            const container = document.getElementById('cartItemsContainer');
            const countBadge = document.getElementById('cartItemCount');
            const mobileBtn = document.getElementById('mobileCartToggleBtn');
            const mobileCount = document.getElementById('mobileCartCount');
            const tipeHarga = document.getElementById('tipeHarga').value;

            const totalItems = posCart.reduce((sum, item) => sum + item.qty, 0);

            if (posCart.length === 0) {
                container.innerHTML = `
                <div class="empty-cart-state">
                    <i class='bx bx-cart'></i>
                    <p>Keranjang masih kosong</p>
                </div>
            `;
                countBadge.textContent = '0 Item';
                if (mobileBtn) mobileBtn.style.display = 'none';
            } else {
                container.innerHTML = posCart.map(item => {
                    const hargaSatuan = item.harga[tipeHarga] || 0;
                    return `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <span class="cart-item-name">${item.nama_barang}</span>
                        <button class="btn-icon" onclick="updateQty('${item.id_barang}', -999)" style="color:var(--danger-color); padding:0; background:transparent; border:none; cursor:pointer;">
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>
                    <div class="cart-item-actions">
                        <span class="cart-item-price">${formatRupiah(hargaSatuan * item.qty)}</span>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateQty('${item.id_barang}', -1)"><i class='bx bx-minus'></i></button>
                            <span style="font-size: 11px; font-weight: 500; min-width: 40px; text-align: center;">${item.qty} PCS</span>
                            <button class="qty-btn" onclick="updateQty('${item.id_barang}', 1)"><i class='bx bx-plus'></i></button>
                        </div>
                    </div>
                </div>
            `}).join('');

                countBadge.textContent = `${totalItems} Item`;
                if (mobileBtn && window.innerWidth <= 1024) {
                    mobileBtn.style.display = 'flex';
                    if (mobileCount) mobileCount.textContent = totalItems;
                }
            }

            updateCartTotal();
        }

        function scrollToCartMobile() {
            const cartEl = document.querySelector('.pos-cart');
            if (cartEl) {
                cartEl.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function updateCartUI() {
            updateCartTotal();
        }

        function updateCartTotal() {
            const tipeHarga = document.getElementById('tipeHarga').value;
            const subtotal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
            const potongan = Number(document.getElementById('potonganPenjualan')?.value) || 0;
            const total = Math.max(0, subtotal - potongan);

            document.getElementById('cartSubtotal').textContent = formatRupiah(subtotal);
            document.getElementById('cartPotongan').textContent = '- ' + formatRupiah(potongan);
            document.getElementById('cartTotal').textContent = formatRupiah(total);
        }

        function changeTipeHarga() {
            renderCart();    // Refresh cart prices
        }

        function toggleCashInput() {
            const mtd = document.getElementById('metodeBayar').value;
            document.getElementById('cashInputContainer').style.display = (mtd === 'Cash') ? 'block' : 'none';
        }

        function showCheckoutSuccessPopup(invoice, kembalianStr) {
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
            overlay.style.backdropFilter = 'blur(4px)';
            overlay.style.zIndex = '999999';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.3s ease';

            overlay.innerHTML = `
                <div style="background: white; width: 100%; max-width: 420px; border-radius: 16px; padding: 32px 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); transform: scale(0.9); transition: transform 0.3s ease;" id="successModalContent">
                    <div style="font-size: 64px; color: #10B981; margin-bottom: 16px; text-align: center;">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <h2 style="margin-bottom: 12px; font-weight: 700; color: #111827; font-size: 24px; text-align: center;">Pembayaran Berhasil!</h2>
                    
                    <div style="background: #F9FAFB; border: 1px dashed #E5E7EB; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                        <p style="color: #6B7280; font-size: 13px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">No. Invoice</p>
                        <strong style="color: #111827; font-size: 16px; display: block; margin-bottom: 16px; text-align: center;">${invoice}</strong>
                        
                        <p style="color: #6B7280; font-size: 13px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Kembalian</p>
                        <strong style="color: #10B981; font-size: 28px; font-weight: 800; display: block; text-align: center;">${kembalianStr}</strong>
                    </div>

                    <button class="btn btn-primary" id="btnCloseSuccessPopup" style="width: 100%; justify-content: center; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 10px;">
                        <i class='bx bx-check'></i> Selesai & Lanjut
                    </button>
                </div>
            `;
            document.body.appendChild(overlay);
            
            // Trigger animation
            setTimeout(() => {
                overlay.style.opacity = '1';
                document.getElementById('successModalContent').style.transform = 'scale(1)';
            }, 10);

            document.getElementById('btnCloseSuccessPopup').addEventListener('click', () => {
                overlay.style.opacity = '0';
                document.getElementById('successModalContent').style.transform = 'scale(0.9)';
                setTimeout(() => overlay.remove(), 300); // Tunggu transisi selesai
            });
        }

        function processCheckout() {
            if (posCart.length === 0) {
                return showToast('Keranjang masih kosong!', 'error');
            }

            const tipeHarga = document.getElementById('tipeHarga').value;
            const metodeBayar = document.getElementById('metodeBayar').value;
            const potongan = Number(document.getElementById('potonganPenjualan')?.value) || 0;
            let uangDiterima = 0;

            if (metodeBayar === "Cash") {
                uangDiterima = Number(document.getElementById('uangDiterima').value) || 0;
                const subtotal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
                const total = Math.max(0, subtotal - potongan);
                if (uangDiterima < total) {
                    return showToast('Uang diterima kurang dari total tagihan!', 'error');
                }
            }

            const btn = document.querySelector('.btn-checkout');
            btn.disabled = true;
            btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Memproses...`;

            // Parameter: cartItems, tipeHarga, metodeBayar, detailBayar, uangDiterima, status, existingInvoiceNo, potonganPenjualan
            BackendAPI.call('simpanTransaksi', [posCart, tipeHarga, metodeBayar, {}, uangDiterima, "Selesai", "", potongan])
                .then(res => {
                    showCheckoutSuccessPopup(res.noInvoice, formatRupiah(res.kembalian));

                    posCart = [];
                    renderCart();
                    document.getElementById('uangDiterima').value = '';
                    if(document.getElementById('potonganPenjualan')) {
                        document.getElementById('potonganPenjualan').value = '';
                    }
                    initPOS(); // Refresh stok barang terbaru dari server
                })
                .catch(err => {
                    showToast("Gagal: " + err.message, "error");
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = `<i class='bx bx-check-circle'></i> Bayar Sekarang`;
                });
        }

        // Panggil init saat DOM Ready (untuk module Penjualan yang merupakan default view)
        document.addEventListener('DOMContentLoaded', () => {
            // Tunda sedikit agar js.html (AppState) ready
            setTimeout(initPOS, 500);

            // Observasi saat tab Penjualan aktif agar otomatis refresh
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.target.id === 'view-penjualan' && mutation.target.classList.contains('active')) {
                        initPOS();
                    }
                });
            });
            const viewPenjualan = document.getElementById('view-penjualan');
            if (viewPenjualan) {
                observer.observe(viewPenjualan, { attributes: true, attributeFilter: ['class'] });
            }
        });
    </script>