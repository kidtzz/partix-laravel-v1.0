<link href="/css/modules/penjualan.css?v={{ time() }}" rel="stylesheet">
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

    
<script src="/js/modules/penjualan.js?v={{ time() }}"></script>

