<link href="/css/modules/penjualan.css?v={{ time() }}" rel="stylesheet">

<!-- BUNGKUSAN UTAMA -->
<div class="pos-master-container" style="height: 100%; width: 100%; display: flex; flex-direction: column;">

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

                <div style="flex: 1; display: flex; flex-direction: column;">
                    <x-table id="posProductTable">
                        <!-- Products will be injected via JS into tbody -->
                    </x-table>
                </div>
            </div>

            <!-- Right Side: Cart / Checkout -->
            <div class="pos-cart glass-effect">
                <div class="cart-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #E5E7EB; padding-bottom: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;">Keranjang</h3>
                        <span class="cart-count badge badge-primary" id="cartItemCount">0 Item</span>
                    </div>
                    <button onclick="clearCart()" style="background: transparent; border: none; color: #EF4444; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                        <i class='bx bx-trash'></i> Kosongkan
                    </button>
                </div>

                <div class="cart-items" id="cartItemsContainer">
                    <div class="empty-cart-state">
                        <i class="bx bx-cart"></i>
                        <p>Keranjang masih kosong</p>
                    </div>
                </div>

                <div class="cart-summary" style="padding: 16px; display: flex; flex-direction: column; gap: 8px;">
                    <div class="summary-row" style="color: var(--text-muted); font-size: 14px; display: flex; justify-content: space-between;">
                        <span>Subtotal</span>
                        <span id="cartSubtotal" style="font-weight: 600;">Rp 0</span>
                    </div>
                    <div class="summary-row total" style="display: flex; justify-content: space-between; border-top: 1px dashed #E5E7EB; padding-top: 12px; margin-top: 4px; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">Grand Total</span>
                        <span id="cartSubtotal2" style="font-size: 14px; font-weight: 700; color: #111827;">Rp 0</span>
                    </div>

                    <button class="btn btn-primary btn-checkout" onclick="openCheckoutModal()" style="padding: 10px 14px; font-size: 12px; font-weight: 600; border-radius: 6px; display: flex; justify-content: center; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px; width: 100%;">
                        <span>CHECKOUT</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Checkout Modal (Hidden by default) -->
        <div id="checkoutModalPOS" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
            <div class="modal-content glass-effect" style="background: white; width: 100%; max-width: 500px; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; max-height: 90vh;">
                
                <!-- Modal Header -->
                <div style="padding: 16px 20px; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; flex-shrink: 0;">
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #111827;">Detail Pembayaran</h3>
                    <button onclick="closeCheckoutModal()" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: #6B7280;"><i class="bx bx-x"></i></button>
                </div>
                
                <!-- Modal Body (Scrollable) -->
                <div style="padding: 20px; display: flex; flex-direction: column; gap: 14px; overflow-y: auto; flex-grow: 1;">
                    <x-input-group label="Tipe Harga / Customer">
                        <select class="input-control" id="tipeHarga" onchange="changeTipeHarga()">
                            <option value="Regular" id="optHrgRegular">Regular (0%)</option>
                            <option value="Member" id="optHrgMember">Member (-5%)</option>
                            <option value="Langganan" id="optHrgLangganan">Langganan (-10%)</option>
                            <option value="Bengkel" id="optHrgBengkel">Bengkel / Reseller (-15%)</option>
                            <option value="Teman" id="optHrgTeman">Teman / Kenalan (-20%)</option>
                            <option value="Grosir" id="optHrgGrosir">Grosir / VIP (-25%)</option>
                        </select>
                    </x-input-group>

                    <x-input-group label="Potongan Manual (Diskon Kasir)">
                        <input type="number" class="input-control" id="potonganPenjualan" placeholder="Cth: 5000" min="0" oninput="updateCartUI()">
                    </x-input-group>

                    <x-input-group label="Metode Bayar">
                        <div class="payment-grid" style="margin-top: 6px;">
                            <button type="button" class="pay-btn active" data-admin-method="Cash" onclick="selectPaymentAdmin('Cash')">Cash</button>
                            <button type="button" class="pay-btn" data-admin-method="Transfer" onclick="selectPaymentAdmin('Transfer')">Transfer</button>
                            <button type="button" class="pay-btn" data-admin-method="QRIS" onclick="selectPaymentAdmin('QRIS')">QRIS</button>
                        </div>
                        <input type="hidden" id="metodeBayar" value="Cash">
                    </x-input-group>

                    <div id="cashInputContainer">
                        <x-input-group label="Uang Diterima (Rp)">
                            <input type="number" class="input-control" id="uangDiterima" placeholder="Cth: 100000" style="font-size: 14px; font-weight: 600; color: #111827;" oninput="calcAdminKembalian()">
                        </x-input-group>
                        
                        <div class="quick-cash-grid" id="adminQuickCashGrid" style="margin-top: 8px;">
                            <!-- injected by JS -->
                        </div>
                        
                        <div class="kembalian-box mt-3" id="adminKembalianBox" style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center; background: #F3F4F6; padding: 12px; border-radius: 8px; border: 1px solid #E5E7EB; transition: all 0.3s ease;">
                            <span id="adminKembalianLabel" style="font-size: 13px; font-weight: 600; color: #4B5563;">Kembalian</span>
                            <h3 id="adminKembalianStr" style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;">Rp 0</h3>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div style="background: #F3F4F6; padding: 16px; border-radius: 12px; margin-top: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #4B5563; margin-bottom: 6px;">
                            <span>Subtotal Setelah Diskon:</span>
                            <span id="cartSubtotalModal" style="font-weight: 600;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #DC2626; margin-bottom: 10px;">
                            <span>Potongan Kasir:</span>
                            <span id="cartPotonganModal" style="font-weight: 600;">- Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px dashed #D1D5DB; padding-top: 10px;">
                            <span style="font-size: 14px; font-weight: 700; color: #111827;">Total Tagihan:</span>
                            <span id="cartTotal" style="font-size: 16px; font-weight: 800; color: #2563EB;">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="padding: 16px 20px; border-top: 1px solid #E5E7EB; background: #F9FAFB; display: flex; gap: 12px; flex-shrink: 0;">
                    <button class="btn btn-secondary" style="flex: 1; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; background: white; border: 1px solid #D1D5DB; color: #4B5563;" onclick="closeCheckoutModal()">BATAL</button>
                    <button class="btn btn-primary" id="btnProsesModal" style="flex: 2; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 13px; display: flex; justify-content: center; align-items: center; gap: 8px; text-transform: uppercase;" onclick="processCheckout()">
                        PROSES TRANSAKSI
                    </button>
                </div>
                
            </div>
        </div>
    </div> <!-- CLOSED admin-pos-view -->

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
                    <button class="btn-clear-cart" id="btnBatalKasirTop" onclick="forceClearCartKasir()" disabled>Bersihkan</button>
                </div>

                <div class="kasir-cart-items" id="kasirCartItemsContainer">
                    <div class="empty-cart-state" style="height: 100%; display: flex; align-items:center; justify-content:center; color:#9ca3af;">
                        <p>Keranjang kosong</p>
                    </div>
                </div>

                <div class="kasir-summary-box" style="margin-top: auto; border-radius: 0; border-left: none; border-right: none; border-bottom: none; margin: 0; padding: 12px 16px;">
                    <div class="sum-row grand-total" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0;">
                        <span style="font-size: clamp(12px, 1vw + 4px, 15px); font-weight: 700; color: #4B5563;">TOTAL</span>
                        <strong class="text-primary-huge" id="kSumGrandTotalSidebar" style="font-size: clamp(16px, 1.2vw + 8px, 22px) !important;">Rp 0</strong>
                    </div>
                </div>

                <div class="kasir-payment-methods" style="padding: 0 16px 12px 16px;">
                    <button class="btn-kasir-selesaikan" onclick="processSelesaikanTransaksi()">
                        BAYAR SEKARANG
                    </button>
                    <div style="display: flex; gap: 12px; margin-top: 12px;">
                        <button class="btn-kasir-action btn-tahan" id="btnTahanKasir" onclick="alert('Fitur tahan transaksi akan segera hadir!')" disabled><i class='bx bx-pause'></i> Tahan</button>
                        <button class="btn-kasir-action btn-batal" id="btnBatalKasirBottom" onclick="openModalBatal()" disabled><i class='bx bx-x-circle'></i> Batal</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Checkout Popup Modal (Landscape / Portrait on Mobile) -->
        <div class="kasir-cash-modal-overlay" id="kasirCashModal" style="z-index: 9999999 !important; backdrop-filter: blur(8px); background: rgba(0,0,0,0.65);">
            <div class="kasir-cash-modal" style="width: 850px; max-width: 95vw; display: flex; flex-direction: column; border-radius: 12px;">
                <div class="cash-modal-header" style="padding: 12px 20px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;">
                    <h3 style="font-size: 16px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-credit-card' style="color: #2563EB; font-size: 20px;"></i> Detail Pembayaran</h3>
                    <button onclick="closeCashModal()" class="close-btn" style="font-size: 24px;"><i class="bx bx-x"></i></button>
                </div>
                
                <div class="cash-modal-body kasir-modal-split" style="padding: 20px; display: flex; gap: 24px; max-height: 80vh; overflow-y: auto;">
                    
                    <!-- LEFT COLUMN -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">
                        
                        <!-- Form Pelanggan & Potongan -->
                        <div class="kasir-checkout-form" style="padding: 0; margin: 0;">
                            <div class="form-group">
                                <label style="font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px; display: block;"><i class="bx bx-user"></i> Pilih Pelanggan</label>
                                <select class="kasir-input-gray" id="tipeHargaKasir" onchange="updateCartUIKasir()" style="padding: 8px 10px; font-size: 13px;">
                                    <option value="Regular" selected>Reguler (Umum)</option>
                                    <option id="optHrgMemberKasir" value="Member">Member</option>
                                    <option id="optHrgLanggananKasir" value="Langganan">Langganan</option>
                                    <option id="optHrgBengkelKasir" value="Bengkel">Bengkel / Reseller</option>
                                    <option id="optHrgTemanKasir" value="Teman">Teman / Kenalan</option>
                                    <option id="optHrgGrosirKasir" value="Grosir">Grosir / VIP</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-top: 12px;">
                                <label style="font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px; display: block;"><i class="bx bx-tag"></i> Potongan Manual (Rp)</label>
                                <div class="kasir-input-group">
                                    <span class="prefix" style="font-size: 13px; left: 10px; top: 8px;">Rp</span>
                                    <input type="text" class="kasir-input-gray" id="potonganKasir" placeholder="0" oninput="formatRupiahInput(this); updateCartUIKasir()" style="padding: 8px 10px 8px 36px; font-size: 14px; font-weight: 700;">
                                </div>
                            </div>
                        </div>

                        <!-- Metode Pembayaran -->
                        <div class="kasir-payment-methods" style="padding: 0; margin: 0;">
                            <label style="font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;">Metode Pembayaran</label>
                            <div class="payment-grid" style="grid-template-columns: 1fr 1fr; gap: 8px;">
                                <button class="pay-btn active" data-method="Cash" onclick="selectPaymentKasir('Cash')" style="padding: 10px; font-size: 13px;"><i class="bx bx-money" style="font-size: 16px;"></i> Cash</button>
                                <button class="pay-btn" data-method="QRIS" onclick="selectPaymentKasir('QRIS')" style="padding: 10px; font-size: 13px;"><i class="bx bx-qr-scan" style="font-size: 16px;"></i> QRIS</button>
                                <button class="pay-btn" data-method="Transfer" onclick="selectPaymentKasir('Transfer')" style="padding: 10px; font-size: 13px;"><i class="bx bxs-bank" style="font-size: 16px;"></i> Transfer</button>
                                <button class="pay-btn" data-method="Mixed" onclick="selectPaymentKasir('Mixed')" style="padding: 10px; font-size: 13px;"><i class="bx bx-wallet" style="font-size: 16px;"></i> Mixed</button>
                            </div>
                            <input type="hidden" id="metodeBayarKasir" value="Cash">
                        </div>

                    </div>

                    <!-- DIVIDER -->
                    <div class="kasir-modal-divider" style="width: 1px; background: #E5E7EB;"></div>

                    <!-- RIGHT COLUMN -->
                    <div style="flex: 1.2; display: flex; flex-direction: column;">
                        
                        <!-- Ringkasan -->
                        <div class="kasir-summary-box" style="margin: 0 0 16px 0; background: #F9FAFB; padding: 16px; border-radius: 10px;">
                            <div class="sum-row" style="font-size: 13px;">
                                <span>Subtotal</span>
                                <strong id="kSumSubtotal">Rp 0</strong>
                            </div>
                            <div class="sum-row" style="font-size: 13px;">
                                <span>Diskon (Promo)</span>
                                <strong class="text-danger" id="kSumDiskon">- Rp 0</strong>
                            </div>
                            <div class="sum-row" style="font-size: 13px;">
                                <span>Potongan Manual</span>
                                <strong class="text-danger" id="kSumPotongan">- Rp 0</strong>
                            </div>
                            <div class="dashed-divider" style="margin: 12px 0;"></div>
                            <div class="sum-row grand-total" style="margin-top: 10px;">
                                <span style="font-size: 13px; color: #6B7280;">TOTAL TAGIHAN</span>
                                <strong class="text-primary-huge" id="kSumGrandTotal" style="font-size: 24px !important;">Rp 0</strong>
                            </div>
                        </div>

                        <!-- Area Input Uang -->
                        <div id="kasirCashInputArea" style="flex: 1; display: flex; flex-direction: column;">
                            <label style="display:block;font-size:13px;color:#374151;margin-bottom:6px;font-weight:700;">Uang Diterima (Rp)</label>
                            <input type="text" id="kasirUangDiterima" class="input-uang-diterima" placeholder="0" oninput="formatRupiahInput(this); calcCashKembalian()" style="font-size: 20px; padding: 10px 12px; height: auto;">
                            
                            <div class="quick-cash-grid" id="kasirQuickCashGrid" style="grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px; margin-bottom: 12px;">
                                <!-- injected by JS -->
                            </div>
                            
                            <div class="kembalian-box mt-3" style="margin-top: auto; padding: 16px; background: #ECFDF5; border-color: #34D399; border-width: 2px;">
                                <span style="font-size: 14px;">Kembalian</span>
                                <h3 id="kasirKembalianStr" style="font-size: 24px;">Rp 0</h3>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="cash-modal-footer" style="padding: 16px 20px; gap: 12px; background: #F9FAFB; border-top: 1px solid #E5E7EB;">
                    <button class="btn-cancel" onclick="closeCashModal()" style="padding: 10px; font-size: 14px; flex: 1;">Kembali</button>
                    <button class="btn-confirm" onclick="submitKasirCheckout()" style="padding: 10px; font-size: 14px; flex: 2; display: flex; align-items: center; justify-content: center; gap: 8px;"> Konfirmasi & Cetak</button>
                </div>
            </div>
        </div>

        <!-- Batal Popup Modal -->
        <div class="kasir-cash-modal-overlay" id="kasirBatalModal" style="z-index: 9999999 !important; backdrop-filter: blur(8px); background: rgba(0,0,0,0.65);">
            <div class="kasir-cash-modal">
                <div class="cash-modal-header">
                    <h3>Konfirmasi Batal</h3>
                    <button onclick="closeModalBatal()" class="close-btn"><i class="bx bx-x"></i></button>
                </div>
                <div class="cash-modal-body" style="padding: 24px; text-align: center;">
                    <i class='bx bx-error-circle' style="font-size: 56px; color: #EF4444; margin-bottom: 12px;"></i>
                    <p style="font-size: 15px; color: #4B5563;">Apakah Anda yakin ingin membatalkan transaksi ini dan mengosongkan keranjang?</p>
                </div>
                <div class="cash-modal-footer">
                    <button class="btn-cancel" onclick="closeModalBatal()">Kembali</button>
                    <button class="btn-confirm" style="background-color: #EF4444;" onclick="executeBatalKasir()">Ya, Batalkan</button>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="/js/modules/penjualan.js?v={{ time() }}"></script>

