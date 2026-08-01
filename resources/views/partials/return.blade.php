<link href="/css/modules/return.css?v={{ time() }}" rel="stylesheet">
<section id="view-return-list" class="view-section">
    <x-view-header title="Daftar Histori Retur" icon="bx bx-list-ul">
        <button class="btn btn-outline" onclick="loadHistoriReturLengkap()">
            <i class='bx bx-refresh'></i> Refresh Data
        </button>
    </x-view-header>

    <x-glass-card padding="24px" display="block">
        <div style="overflow-x: auto;">
            <x-table :headers="['No Return', 'Tanggal', 'Invoice Asal', 'Kasir', 'Jenis Penyelesaian', 'Total Refund/Selisih', 'Aksi']">
                <tbody id="tbodyReturnList">
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Memuat data...</td></tr>
                </tbody>
            </x-table>
        </div>
    </x-glass-card>

    </section>

<section id="view-return" class="view-section">
<x-view-header title="Proses Retur" icon="bx bx-revision"></x-view-header>

<div id="returnFormContainer">
    <div class="return-grid-layout">

        <!-- Left Column: Search Invoice -->
        <x-glass-card padding="24px" display="block" style="height: fit-content;">
            <h3 style="margin-top:0; margin-bottom: 16px; font-size: 16px;">Cari Data Transaksi</h3>

            <x-input-group label="Nomor Invoice / Barcode Transaksi">
                <div style="display:flex; gap:8px;">
                    <input type="text" class="input-control" id="searchInvoice" placeholder="INV-20260726-0001">
                    <button class="btn btn-primary" onclick="cariInvoice()"><i class='bx bx-search'></i> Cari</button>
                </div>
            </x-input-group>

            <div id="invoiceInfoPanel" style="display: none;" class="invoice-receipt">
                <div style="text-align: center; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Nomor Invoice</div>
                    <div style="font-weight: 700; font-size: 16px; color: var(--text-main); margin-top: 4px; margin-bottom: 4px;" id="invInfoNo">INV-000</div>
                    <div id="invInfoStatus"></div>
                </div>

                <div class="flex justify-between mb-3">
                    <span style="color: var(--text-muted); font-size: 12px;"><i class='bx bx-calendar'></i> Tanggal</span>
                    <span style="font-size: 12px; font-weight: 500;" id="invInfoTgl">-</span>
                </div>
                <div class="flex justify-between mb-3">
                    <span style="color: var(--text-muted); font-size: 12px;"><i class='bx bx-user'></i> Kasir</span>
                    <span style="font-size: 12px; font-weight: 500;" id="invInfoKasir">-</span>
                </div>
                <div class="flex justify-between mt-4" style="border-top: 1px dashed #CBD5E1; padding-top: 12px;">
                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 600;">Total Belanja</span>
                    <span style="font-size: 14px; font-weight: 700; color: var(--primary-color);" id="invInfoTotal">-</span>
                </div>
            </div>
        </x-glass-card>

        <!-- Right Column: Return Items -->
        <x-glass-card padding="24px" display="flex">
            <h3 style="margin-top:0; margin-bottom: 16px; font-size: 14px;">Barang yang Dibeli</h3>

            <div id="emptyInvoiceState"
                style="text-align: center; color: var(--text-muted); padding: 40px 0; opacity: 0.7;">
                <i class='bx bx-receipt' style="font-size: 40px; margin-bottom: 12px;"></i>
                <p>Silakan cari nomor invoice terlebih dahulu</p>
            </div>

            <div id="returnItemsContainer" style="display: none; flex: 1; flex-direction: column; gap: 16px;">
                <!-- Mock Item to Return -->
                <div style="border: 1px solid var(--border-solid); border-radius: var(--radius-md); padding: 16px;">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <div style="font-weight: 600;">Kampas Rem Depan Vario</div>
                            <div style="font-size: 11px; color: var(--text-muted);">Dibeli: 2 PCS @ Rp 45.000</div>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" style="width: 18px; height: 18px;"
                                onchange="toggleReturnForm('RTN_B01', this.checked)">
                            <span style="font-size: 12px; font-weight: 500;">Pilih Return</span>
                        </label>
                    </div>

                    <div id="returnForm_RTN_B01"
                        style="display: none; background: #F9FAFB; padding: 16px; border-radius: var(--radius-md); margin-top: 12px;">
                        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                            <x-input-group label="Qty Direturn" marginBottom="0">
                                <input type="number" class="input-control" max="2" min="1" value="1">
                            </x-input-group>
                            <x-input-group label="Alasan Return" marginBottom="0">
                                <select class="input-control">
                                    <option>Cacat Pabrik</option>
                                    <option>Salah Beli</option>
                                    <option>Lainnya</option>
                                </select>
                            </x-input-group>
                        </div>

                        <div class="mt-4">
                            <x-input-group label="Jenis Penyelesaian" marginBottom="0">
                                <select class="input-control">
                                    <option>Tukar Barang Sama (Ganti Stok Baru)</option>
                                    <option>Refund Uang Tunai</option>
                                </select>
                            </x-input-group>
                        </div>
                    </div>
                </div>

                <div
                    style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border-solid); text-align: right;">
                    <button class="btn btn-primary" id="btnProsesReturn" onclick="prosesReturn()" style="min-height: 28px; padding: 4px 12px; font-size: 11px;">
                        <i class='bx bx-check'></i> Proses Return
                    </button>
                </div>
            </div>
        </x-glass-card>

    </div>
</div>

</section>

<x-modal id="returnPaymentModal" title="Penyelesaian Pembayaran Retur">
    <div style="text-align: center; padding: 24px 0 8px 0;">
        <i id="returnPaymentIcon" class='bx bx-money' style="font-size: 64px; margin-bottom: 16px;"></i>
        <h2 id="returnPaymentTitle" style="font-size: var(--text-lg); font-weight: 700; margin-bottom: 8px;">-</h2>
        <p style="color: var(--text-muted); font-size: var(--text-base); margin-bottom: 20px;">Harap selesaikan transaksi tunai berikut dengan pelanggan:</p>
        <div style="background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 16px; border-radius: var(--radius-md); display: inline-block; min-width: 80%;">
            <div id="returnPaymentAmount" style="font-size: var(--text-xxxl); font-weight: 800; letter-spacing: -0.5px;">Rp 0</div>
        </div>
    </div>
    <div class="modal-footer" style="justify-content: center; margin-top: 24px; border-top: none;">
        <button class="btn btn-primary" id="btnTutupPaymentModal" style="width: 100%; min-height: 36px; font-size: var(--text-base);">Saya Sudah Mengerti & Selesai</button>
    </div>
</x-modal>

<section id="view-return-supplier" class="view-section">
    <x-view-header title="Retur ke Supplier" icon="bx bx-archive-out">
        <div class="flex gap-2">
            <button class="btn btn-outline" onclick="loadListBarangReturn()">
                <i class='bx bx-refresh'></i> Refresh
            </button>
        </div>
    </x-view-header>

    <!-- Tabs Navigation -->
    <div class="flex gap-4 mb-4" style="border-bottom: 1px solid var(--border-color);">
        <button id="tabKarantina" class="tab-btn active" onclick="switchReturTab('karantina')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--primary-color); border-bottom: 2px solid var(--primary-color); cursor:pointer;">
            Daftar Barang Karantina
        </button>
        <button id="tabHistoriRetur" class="tab-btn" onclick="switchReturTab('histori')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--text-muted); cursor:pointer;">
            Histori Retur Supplier
        </button>
    </div>

    <!-- Tab 1: Karantina -->
    <x-glass-card id="contentKarantina" padding="24px" display="block">
        <div style="overflow-x: auto;">
            <x-table :headers="['ID Karantina', 'Tanggal Karantina', 'Nama Barang', 'Qty Rusak', 'Alasan / Keterangan', 'Aksi']">
                <tbody id="tbodyBarangReturn">
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Memuat data...</td></tr>
                </tbody>
            </x-table>
        </div>
    </x-glass-card>

    <!-- Tab 2: Histori Retur -->
    <x-glass-card id="contentHistoriRetur" padding="24px" display="none">
        <div style="overflow-x: auto;">
            <x-table :headers="['Tanggal Retur', 'ID Retur', 'Supplier Tujuan', 'Barang Diretur', 'Qty', 'Harga Beli', 'No Invoice Supplier', 'User']">
                <tbody id="tbodyHistoriRetur">
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Memuat histori...</td></tr>
                </tbody>
            </x-table>
        </div>
    </x-glass-card>

    <!-- Modal Detail Return (Cetak) -->
    <x-modal id="modalDetailReturn" title="Detail Retur (Struk)" maxWidth="500px">
        <div id="detailReturnBody" style="padding: 24px;">
            Memuat detail...
        </div>
    </x-modal>

    <!-- Modal Retur ke Supplier -->
    <x-modal id="modalReturSupplier" title="Proses Retur ke Supplier" maxWidth="500px">
        <x-input-group label="Barang yang Diretur">
            <input type="text" id="rsNamaBarang" class="input-control" disabled style="background-color: var(--bg-color);">
            <input type="hidden" id="rsIdBarangReturn">
            <input type="hidden" id="rsMaxQty">
        </x-input-group>
        
        <x-input-group label="Qty Diretur (Maks: <span id='rsLabelMaxQty' style='font-weight: bold; color: var(--danger-color);'>0</span>)">
            <input type="number" id="rsQty" class="input-control" min="1" value="1">
        </x-input-group>
        
        <x-input-group label="Supplier Tujuan">
            <select id="rsSupplier" class="input-control" required>
                <option value="">-- Pilih Supplier --</option>
            </select>
        </x-input-group>
        
        <x-input-group label="Nomor Invoice Pembelian (Dari Supplier)">
            <input type="text" id="rsNoInvoice" class="input-control" placeholder="Contoh: INV-SUP-001" required>
        </x-input-group>
        
        <x-input-group label="Harga Beli Satuan (Modal)" marginBottom="16px">
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 10px; color: var(--text-muted); font-weight: 500;">Rp</span>
                <input type="number" id="rsHargaBeli" class="input-control" placeholder="0" style="padding-left: 36px;" required>
            </div>
        </x-input-group>
        
        <x-slot name="footer">
            <button type="button" class="btn btn-outline" onclick="closeModalRetur()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitReturSupplier(this)"><i class='bx bx-send'></i> Kirim Retur</button>
        </x-slot>
    </x-modal>

    </section>

<script src="/js/modules/return.js?v={{ time() }}"></script>

