<section id="view-stock" class="view-section">
<x-view-header title="Manajemen Stok Barang" icon="bx bx-package">
    <div class="flex gap-4 mobile-stack-flex">
        <div class="search-bar">
            <i class='bx bx-search'></i>
            <input type="text" id="stockSearch" placeholder="Cari nama barang atau kode...">
        </div>

        <button class="btn btn-primary" onclick="openStockModal()">
            <i class='bx bx-plus'></i> Tambah
        </button>
    </div>
</x-view-header>

<x-table :headers="['Kode', 'Nama Barang', 'Lokasi Rak', 'Supplier Utama', 'Harga Beli', 'Diskon', 'Stok Aktif', 'Tgl Masuk', 'Status', 'Aksi', 'Histori']">
    <tbody id="stockTableBody">
        <tr>
            <td colspan="11" style="text-align:center; padding:20px;">Memuat data stok...</td>
        </tr>
    </tbody>
</x-table>
</section>

<!-- Modal Input Barang Masuk -->
<x-modal id="modalStockIn" title="Input Barang Masuk">
    <x-input-group label="Scan Barcode / ID Barang">
        <div style="display:flex; gap:8px;">
            <input type="text" class="input-control" id="scanStock" placeholder="Scan Barcode..."
                onkeypress="handleScanStock(event)">
            <button class="btn btn-secondary" onclick="lookupStockBarcode()"><i
                    class='bx bx-search'></i></button>
        </div>
    </x-input-group>

    <x-input-group label="Nama Barang (Auto-fill)">
        <input type="text" class="input-control" id="stkNamaBarang" value="" disabled
            style="background:#F3F4F6;">
        <input type="hidden" id="stkIdBarang">
    </x-input-group>

    <x-glass-card padding="16px" display="block">
        <h4 style="margin-top:0; margin-bottom: 12px; font-size: 12px; color: var(--text-main);"><i
                class='bx bx-link'></i> Tautkan Supplier Awal (Opsional)</h4>
        <div class="grid" style="grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
            <x-input-group label="Pilih Supplier" marginBottom="0">
                <select id="stkIdSupplier" class="input-control">
                    <option value="">-- Lewati / Tidak Ada --</option>
                </select>
            </x-input-group>
            <x-input-group label="Harga Beli" marginBottom="0">
                <input type="number" id="stkHargaBeli" class="input-control" placeholder="Rp">
            </x-input-group>
            <x-input-group label="Diskon (%)" marginBottom="0">
                <input type="number" id="stkDiskonPersen" class="input-control" placeholder="0" min="0" max="100">
            </x-input-group>
        </div>
    </x-glass-card>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
        <x-input-group label="Stok Barang (Box)" marginBottom="0">
            <input type="number" class="input-control" id="stkQtyBox" placeholder="0">
        </x-input-group>
        <x-input-group label="Satuan (PCS)" marginBottom="0">
            <input type="number" class="input-control" id="stkSatuanPcs" placeholder="1" value="1">
        </x-input-group>
    </div>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="closeStockModal()">Batal</button>
        <button class="btn btn-primary" id="btnSimpanStok" onclick="simpanBarangMasuk()">Simpan</button>
    </x-slot>
</x-modal>


<!-- Modal Edit Barang -->
<x-modal id="modalEditStock" title="Edit Barang" maxWidth="600px">
    <input type="hidden" id="editStockId">
    <div class="grid" style="grid-template-columns: 1fr; gap: 12px;">
        <x-input-group label="Nama Barang *" marginBottom="0">
            <input type="text" class="input-control" id="editStockNama" required>
        </x-input-group>
        <x-input-group label="Barcode 1" marginBottom="0">
            <input type="text" class="input-control" id="editStockBarcode1">
        </x-input-group>
        <x-input-group label="Barcode 2" marginBottom="0">
            <input type="text" class="input-control" id="editStockBarcode2">
        </x-input-group>
        <x-input-group label="Status Barang" marginBottom="0">
            <select class="input-control" id="editStockStatus">
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </x-input-group>
        <x-input-group label="Stok Barang (PCS)" marginBottom="0">
            <input type="number" class="input-control" id="editStockJumlahBox">
        </x-input-group>
    </div>

    <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border-color);">
    <div style="margin-bottom: 12px;">
        <h4 style="margin:0; font-size: 12px; color: var(--text-main);"><i class='bx bx-link'></i> Tautan
            Supplier & Harga Beli</h4>
    </div>

    <x-glass-card padding="16px" display="block" style="margin-bottom: 20px; background: rgba(0,0,0,0.02);">
        <div class="grid" style="grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: end;">
            <x-input-group label="Supplier" marginBottom="0">
                <select class="input-control" id="formStkSupplierSelect"></select>
            </x-input-group>
            <x-input-group label="Harga Beli" marginBottom="0">
                <input type="number" class="input-control" id="formStkHargaBeli" placeholder="0">
            </x-input-group>
            <x-input-group label="Diskon (%)" marginBottom="0">
                <input type="number" class="input-control" id="formStkDiskonPersen" placeholder="0" min="0" max="100">
            </x-input-group>
            <button class="btn btn-primary" onclick="tambahStockSupplier()">Tambah</button>
        </div>
    </x-glass-card>

    <x-table :headers="['Supplier', 'Harga Beli', 'Diskon (%)', 'Aksi']">
        <tbody id="stockBarangSupplierTableBody">
            <tr>
                <td colspan="4" style="text-align:center;">Memuat data...</td>
            </tr>
        </tbody>
    </x-table>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="tutupModalEditStock()">Batal</button>
        <button class="btn btn-primary" id="btnSimpanEditStock" onclick="simpanEditStock()">Simpan</button>
    </x-slot>
</x-modal>

<!-- Modal Histori -->
<x-modal id="modalHistoriBarang" title="Histori Barang" titleId="historiBarangTitle" maxWidth="800px">
    <x-table :headers="['Waktu', 'Jenis', 'Deskripsi Perubahan', 'User']">
        <tbody id="historiTableBody">
        </tbody>
    </x-table>
</x-modal>


<script src="/js/modules/stock.js?v={{ time() }}"></script>

