<link href="/css/modules/penjualan.css?v={{ time() }}" rel="stylesheet">

<!-- BUNGKUSAN UTAMA -->
<div class="pos-master-container">

    <!-- ========================================== -->
    <!-- 1. ADMIN POS VIEW (Tampilan Lama)         -->
    <!-- ========================================== -->
    <div class="admin-pos-view admin-only-inputs">
        <div class="pos-container">
            <!-- Left Side: Product Grid & Search -->
            <div class="pos-products">
                <div class="pos-header">
                    <h2 class="view-title">
                        <i class="bx bx-cart"></i> Point of Sale
                    </h2>
                    <div class="search-bar">
                        <i class="bx bx-barcode-reader"></i>
                        <input type="text" id="posSearch" placeholder="Scan Barcode atau Cari Barang..." autofocus>
                    </div>
                </div>

                <div class="product-grid" id="posProductGrid">
                    <!-- Products will be injected via JS -->
                </div>
            </div>

            <!-- Right Side: Cart / Checkout -->
            <div class="pos-cart glass-effect">
                <div class="cart-header">
                    <h3>Keranjang</h3>
                    <span class="cart-count badge badge-primary" id="cartItemCount">0 Item</span>
                </div>

                <div class="cart-items" id="cartItemsContainer">
                    <div class="empty-cart-state">
                        <i class="bx bx-cart"></i>
                        <p>Keranjang masih kosong</p>
                    </div>
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
                        <span id="cartSubtotal2">Rp 0</span>
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
                        <i class="bx bx-check-circle"></i> Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- 2. KASIR POS VIEW (Tampilan Replica)      -->
    <!-- ========================================== -->
    <div class="kasir-pos-view role-kasir-only">
        
        <!-- Kasir Main Body -->
        <div class="kasir-main">
            
            <!-- Left: Catalog -->
            <div class="kasir-catalog">
                <div class="kasir-categories">
                    <div class="kasir-cat-list" id="kasirCategoryList">
                        <button class="kasir-cat-chip active" onclick="filterCategoryKasir('Semua')">Semua</button>
                        <button class="kasir-cat-chip" onclick="filterCategoryKasir('Pelumas')">Pelumas</button>
                        <button class="kasir-cat-chip" onclick="filterCategoryKasir('Ban & Velg')">Ban & Velg</button>
                        <button class="kasir-cat-chip" onclick="filterCategoryKasir('Kelistrikan')">Kelistrikan</button>
                        <button class="kasir-cat-chip" onclick="filterCategoryKasir('Mesin')">Mesin</button>
                        <button class="kasir-cat-chip" onclick="filterCategoryKasir('Body Part')">Body Part</button>
                    </div>
                    <div class="kasir-cat-count">
                        <i class="bx bx-cart"></i> <span id="kasirItemTerpilih">0 Item Terpilih</span>
                    </div>
                </div>

                <div class="kasir-product-grid" id="kasirProductGrid">
                    <!-- Injected via JS -->
                </div>
            </div>

            <!-- Right: Cart Sidebar -->
            <div class="kasir-sidebar">
                <div class="sidebar-header">
                    <h3><i class="bx bx-cart"></i> Ringkasan Keranjang</h3>
                    <button class="btn-clear-cart" onclick="clearCartKasir()">Bersihkan</button>
                </div>

                <div class="kasir-cart-items" id="kasirCartItemsContainer">
                    <div class="empty-cart-state" style="height: 100%; display: flex; align-items:center; justify-content:center; color:#9ca3af;">
                        <p>Keranjang kosong</p>
                    </div>
                </div>

                <div class="kasir-checkout-form">
                    <div class="form-group">
                        <label><i class="bx bx-user"></i> Pilih Pelanggan</label>
                        <select class="kasir-input-gray" id="tipeHargaKasir" onchange="updateCartUIKasir()">
                            <option value="Regular" selected>Reguler (Umum)</option>
                            <option id="optHrgMemberKasir" value="Member">Member</option>
                            <option id="optHrgLanggananKasir" value="Langganan">Langganan</option>
                            <option id="optHrgBengkelKasir" value="Bengkel">Bengkel / Reseller</option>
                            <option id="optHrgTemanKasir" value="Teman">Teman / Kenalan</option>
                            <option id="optHrgGrosirKasir" value="Grosir">Grosir / VIP</option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label><i class="bx bx-tag"></i> Potongan Manual (Rp)</label>
                        <div class="kasir-input-group">
                            <span class="prefix">Rp</span>
                            <input type="number" class="kasir-input-gray pl-8" id="potonganKasir" placeholder="0" oninput="updateCartUIKasir()">
                        </div>
                    </div>
                </div>

                <div class="kasir-summary-box">
                    <div class="sum-row">
                        <span>Subtotal</span>
                        <strong id="kSumSubtotal">Rp 0</strong>
                    </div>
                    <div class="sum-row">
                        <span>Diskon (Promo)</span>
                        <strong class="text-danger" id="kSumDiskon">- Rp 0</strong>
                    </div>
                    <div class="sum-row">
                        <span>Potongan Manual</span>
                        <strong class="text-danger" id="kSumPotongan">- Rp 0</strong>
                    </div>
                    <div class="dashed-divider"></div>
                    <div class="sum-row grand-total">
                        <span>GRAND TOTAL</span>
                        <strong class="text-primary-huge" id="kSumGrandTotal">Rp 0</strong>
                    </div>
                </div>

                <div class="kasir-payment-methods">
                    <label>Metode Pembayaran</label>
                    <div class="payment-grid">
                        <button class="pay-btn active" data-method="Cash" onclick="selectPaymentKasir('Cash')"><i class="bx bx-money"></i> Cash</button>
                        <button class="pay-btn" data-method="QRIS" onclick="selectPaymentKasir('QRIS')"><i class="bx bx-qr-scan"></i> QRIS</button>
                        <button class="pay-btn" data-method="Transfer" onclick="selectPaymentKasir('Transfer')"><i class="bx bxs-bank"></i> Transfer</button>
                        <button class="pay-btn" data-method="Mixed" onclick="selectPaymentKasir('Mixed')"><i class="bx bx-wallet"></i> Mixed</button>
                    </div>
                    <input type="hidden" id="metodeBayarKasir" value="Cash">
                    
                    <button class="btn-kasir-selesaikan" onclick="processSelesaikanTransaksi()" style="margin-top: 12px;">
                        BAYAR SEKARANG
                    </button>
                    <div style="display: flex; gap: 12px; margin-top: 12px;">
                        <button class="btn-kasir-action btn-tahan" onclick="clearCartKasir()"><i class='bx bx-pause'></i> Tahan</button>
                        <button class="btn-kasir-action btn-batal" onclick="clearCartKasir()"><i class='bx bx-x-circle'></i> Batal</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cash Popup Modal -->
        <div class="kasir-cash-modal-overlay" id="kasirCashModal">
            <div class="kasir-cash-modal">
                <div class="cash-modal-header">
                    <h3>Penerimaan Tunai</h3>
                    <button onclick="closeCashModal()" class="close-btn"><i class="bx bx-x"></i></button>
                </div>
                <div class="cash-modal-body">
                    <p class="tagihan-label">Total Tagihan:</p>
                    <h2 class="tagihan-amount" id="cashModalTotalTagihan">Rp 0</h2>
                    
                    <label style="margin-top:20px;display:block;font-size:14px;color:#6b7280;margin-bottom:8px;">Uang Diterima (Rp)</label>
                    <input type="number" id="kasirUangDiterima" class="input-uang-diterima" placeholder="0" oninput="calcCashKembalian()">
                    
                    <div class="quick-cash-grid" id="kasirQuickCashGrid">
                        <!-- injected by JS -->
                    </div>
                    
                    <div class="kembalian-box mt-3">
                        <span>Kembalian</span>
                        <h3 id="kasirKembalianStr">Rp 0</h3>
                    </div>
                </div>
                <div class="cash-modal-footer">
                    <button class="btn-cancel" onclick="closeCashModal()">Batal</button>
                    <button class="btn-confirm" onclick="submitKasirCheckout()">Konfirmasi & Cetak</button>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="/js/modules/penjualan.js?v={{ time() }}"></script>

