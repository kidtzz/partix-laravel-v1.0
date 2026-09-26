<link href="/css/modules/admin.css?v={{ time() }}" rel="stylesheet">
<!-- Tab: Pengaturan Harga -->
<section id="view-admin-harga" class="view-section admin-harga-flex">
    <x-view-header title="Pengaturan Harga Jual" icon="bx bx-dollar-circle"></x-view-header>

    <!-- Tabs Navigation -->
    <div class="flex gap-4 mb-4" style="border-bottom: 1px solid var(--border-color); overflow-x: auto; white-space: nowrap; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; flex-shrink: 0;">
        <button id="tabHargaDaftar" class="tab-btn active" onclick="switchAdminHargaTab('daftar')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--primary-color); border-bottom: 2px solid var(--primary-color); cursor:pointer;">
            Daftar Harga Barang
        </button>
        <button id="tabHargaKonfigurasi" class="tab-btn" onclick="switchAdminHargaTab('konfigurasi')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--text-muted); cursor:pointer;">
            Konfigurasi Harga & Stok
        </button>
    </div>

    <!-- TAB 1: KONFIGURASI -->
    <div id="contentHargaKonfigurasi" style="display: none; flex-shrink: 0;">
    <!-- ═══ SETTINGS BAR ═══ -->
    <div class="glass-card" style="padding: 20px; box-sizing:border-box;">
        <div style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-slider-alt' style="color: var(--primary-color); font-size: 20px;"></i>
            <span style="font-size: 14px; font-weight: 700; color: var(--text-main);">Konfigurasi Harga & Stok</span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
            
            <x-input-group label="Diskon Member (%)">
                <input type="number" class="input-control" id="settingDiskonMember" min="0" max="100" oninput="updateDiskonVisual('Mem',this.value)" style="color:#3b82f6; font-weight: 600;">
            </x-input-group>

            <x-input-group label="Diskon Langganan (%)">
                <input type="number" class="input-control" id="settingDiskonLangganan" min="0" max="100" oninput="updateDiskonVisual('Lan',this.value)" style="color:#6366f1; font-weight: 600;">
            </x-input-group>

            <x-input-group label="Diskon Bengkel (%)">
                <input type="number" class="input-control" id="settingDiskonBengkel" min="0" max="100" oninput="updateDiskonVisual('Beng',this.value)" style="color:#8b5cf6; font-weight: 600;">
            </x-input-group>

            <x-input-group label="Diskon Teman (%)">
                <input type="number" class="input-control" id="settingDiskonTeman" min="0" max="100" oninput="updateDiskonVisual('Tem',this.value)" style="color:#f59e0b; font-weight: 600;">
            </x-input-group>

            <x-input-group label="Diskon Grosir (%)">
                <input type="number" class="input-control" id="settingDiskonGrosir" min="0" max="100" oninput="updateDiskonVisual('Gro',this.value)" style="color:#ec4899; font-weight: 600;">
            </x-input-group>

            <x-input-group label="Min Peringatan Stok (PCS)">
                <input type="number" class="input-control" id="settingMinimumStok" min="0" value="5" style="color:#0f172a; font-weight: 600;">
            </x-input-group>

        </div>

        <div style="margin-top: 8px; display: flex; justify-content: flex-end;">
            <button class="btn btn-primary" onclick="simpanSemuaPengaturanHarga()" id="btnSimpanPengaturanHarga" style="padding: 10px 24px;">
                <i class='bx bx-save'></i> Simpan Pengaturan
            </button>
        </div>

        <!-- hidden bars untuk JS -->
        <div style="display:none;">
            <div id="barDiskonMem"></div>
            <div id="barDiskonLan"></div>
            <div id="barDiskonBeng"></div>
            <div id="barDiskonTem"></div>
            <div id="barDiskonGro"></div>
        </div>
    </div>
    </div> <!-- END TAB 1 -->

    <!-- TAB 2: DAFTAR HARGA BARANG -->
    <div id="contentHargaDaftar" style="display: flex; flex: 1; flex-direction: column;">
    <!-- ═══ TABEL HARGA ═══ -->
    <div class="glass-card" style="padding:20px; display:flex; flex-direction:column; overflow:hidden; width:100%; box-sizing:border-box; flex:1; min-height:500px; margin-bottom:24px;">

        <!-- Search Bar -->
        <div style="margin-bottom: 16px;">
            <div class="search-bar" style="width: 100%; max-width: 350px;">
                <i class='bx bx-search'></i>
                <input type="text" id="adminSearchHarga" placeholder="Cari nama barang atau ID...">
            </div>
        </div>

        <!-- Table -->
        <div style="flex:1; overflow:auto; border-radius:var(--radius-sm); min-height:400px;">
            <x-table :headers="['Barang & ID', 'Modal (Max)', 'H. Regular', 'H. Member', 'H. Langganan', 'H. Bengkel', 'H. Teman', 'H. Grosir', 'Status', 'Aksi']">
                <tbody id="adminHargaTableBody">
                    <tr>
                        <td colspan="10" style="text-align:center;">Memuat data...</td>
                    </tr>
                </tbody>
            </x-table>
        </div>
        <div id="adminHargaPagination"></div>
    </div>
    </div> <!-- END TAB 2 -->

</section>

<!-- Tab: Master Barang -->



<section id="view-admin-barang" class="view-section">
    <x-view-header title="Master Data Barang" icon="bx bx-box">
        <div class="flex gap-4 mobile-stack-flex">
            <div class="search-bar">
                <i class='bx bx-search'></i>
                <input type="text" id="adminSearchBarang" placeholder="Cari barang...">
            </div>
            <button class="btn btn-primary" onclick="bukaModalBarang()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
    </x-view-header>
      <x-glass-card padding="24px" display="flex" flex="true" style="flex-direction: column;">
          <x-table :headers="['ID', 'Gambar', 'Barcode 1', 'Barcode 2', 'Nama Barang', 'Lokasi Rak', 'Status', 'Aksi']">
              <tbody id="adminBarangTableBody">
                  <tr>
                      <td colspan="8" style="text-align:center;">Memuat data...</td>
                </tr>
            </tbody>
        </x-table>
        <div id="adminBarangPagination"></div>
    </x-glass-card>
</section>

<!-- Tab: Master Supplier -->
<section id="view-admin-supplier" class="view-section">
    <x-view-header title="Master Data Supplier" icon="bx bx-buildings">
        <div class="flex gap-4 mobile-stack-flex">
            <div class="search-bar">
                <i class='bx bx-search'></i>
                <input type="text" id="adminSearchSupplier" placeholder="Cari supplier...">
            </div>
            <button class="btn btn-primary" onclick="bukaModalSupplier()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
    </x-view-header>
    <x-glass-card padding="24px" display="flex" flex="true" style="flex-direction: column;">
        <x-table :headers="['ID Supplier', 'Nama Supplier', 'PIC', 'Kontak', 'Status', 'Aksi']">
            <tbody id="adminSupplierTableBody">
                <tr>
                    <td colspan="6" style="text-align:center;">Memuat data...</td>
                </tr>
            </tbody>
        </x-table>
        <div id="adminSupplierPagination"></div>
    </x-glass-card>
</section>

<!-- Tab: Manajemen User -->
<section id="view-admin-user" class="view-section">
    <x-view-header title="Manajemen User & Hak Akses" icon="bx bx-user-circle">
        <div class="flex gap-4 mobile-stack-flex">
            <div class="search-bar">
                <i class='bx bx-search'></i>
                <input type="text" id="adminSearchUser" placeholder="Cari username atau nama...">
            </div>
            <button class="btn btn-primary" onclick="bukaModalUser()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
    </x-view-header>
    <x-glass-card padding="24px" display="flex" flex="true" style="flex-direction: column;">
        <x-table :headers="['Username', 'Nama Lengkap', 'Role','Email', 'Keterangan', 'Status', 'Aksi']">
            <tbody id="adminUserTableBody">
                <tr>
                    <td colspan="7" style="text-align:center;">Memuat data...</td>
                </tr>
            </tbody>
        </x-table>
        <div id="adminUserPagination"></div>
    </x-glass-card>
</section>

<!-- Tab: Histori Transaksi -->
<section id="view-histori-transaksi" class="view-section">
    <x-view-header title="Histori Transaksi" icon="bx bx-history">
        <div class="search-bar">
            <i class='bx bx-search'></i>
            <input type="text" id="adminSearchTransaksi" placeholder="No. Invoice...">
        </div>
    </x-view-header>
    <x-glass-card padding="24px" display="flex" flex="true" style="flex-direction: column;">
        <x-table :headers="['No Invoice', 'Tanggal (WIB)', 'Kasir', 'Pelanggan', 'Status', 'Total', 'Aksi']">
            <tbody id="adminTransaksiTableBody">
                <tr>
                    <td colspan="7" style="text-align:center;">Memuat histori transaksi...</td>
                </tr>
            </tbody>
        </x-table>
        <div id="adminTransaksiPagination"></div>
    </x-glass-card>
</section>

<!-- Tab: Log Aktivitas -->
<section id="view-admin-log" class="view-section">
    <x-view-header title="Log Audit Sistem" icon="bx bx-list-ol">
        <button class="btn btn-secondary" onclick="loadAdminLog()"><i class='bx bx-refresh'></i> Refresh</button>
    </x-view-header>
    
    <!-- Tabs Navigation -->
    <div class="flex gap-4 mb-4" style="border-bottom: 1px solid var(--border-color); overflow-x: auto; white-space: nowrap; flex-wrap: nowrap; -webkit-overflow-scrolling: touch;">
        <button id="tabLogTransaksi" class="tab-btn active" onclick="switchAdminLogTab('transaksi')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--primary-color); border-bottom: 2px solid var(--primary-color); cursor:pointer;">
            Log Transaksi
        </button>
        <button id="tabLogSistem" class="tab-btn" onclick="switchAdminLogTab('sistem')" style="padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--text-muted); cursor:pointer;">
            Log Sistem
        </button>
    </div>
    
    <div id="contentLogTransaksi" style="display: block; height: calc(100vh - 200px); min-height: 400px; overflow: hidden;">
        <x-glass-card padding="24px" display="flex" flex="true" style="height: 100%; display: flex; flex-direction: column;">
            <x-table :headers="['Waktu (WIB)', 'User', 'Role', 'Aksi', 'Modul', 'Detail']">
                <tbody id="adminLogTableBody">
                    <tr>
                        <td colspan="6" style="text-align:center;">Memuat log aktivitas...</td>
                    </tr>
                </tbody>
            </x-table>
        </x-glass-card>
    </div>
    
    <div id="contentLogSistem" style="display: none; height: calc(100vh - 200px); min-height: 400px; overflow: hidden;">
        <x-glass-card padding="24px" display="flex" flex="true" style="height: 100%; display: flex; flex-direction: column;">
            <x-table :headers="['Waktu (WIB)', 'Level', 'User', 'URL', 'Pesan Error', 'Aksi']">
                <tbody id="systemLogTableBody">
                    <tr>
                        <td colspan="6" style="text-align:center;">Pilih tab ini untuk memuat log sistem...</td>
                    </tr>
                </tbody>
            </x-table>
        </x-glass-card>
    </div>
</section>

<!-- Modal Context -->
<x-modal id="modalSystemLogContext" title="Detail Konteks Log">
    <div style="background: #1e293b; color: #38bdf8; padding: 15px; border-radius: 8px; font-family: monospace; overflow-x: auto; font-size: 12px; white-space: pre-wrap;" id="contextLogContent">
    </div>
</x-modal>

<!-- Modal Detail Transaksi -->
<x-modal id="modalDetailTransaksi" title="Detail Transaksi" maxWidth="850px">
    <div id="detailTransaksiBody">
        Memuat detail...
    </div>
</x-modal>

<!-- Modal Edit Harga -->
<x-modal id="modalEditHarga" title="Edit Harga Jual">
    <div style="margin-bottom: 20px;">
        <div style="font-weight: 600;" id="modalEditHargaNama">Nama Barang</div>
        <div style="font-size: 11px; color: var(--text-muted);" id="modalEditHargaId">BRG-0000</div>
    </div>

    <div class="alert alert-info"
        style="margin-bottom: 20px; padding: 12px; background: #e0f2fe; border-radius: 8px; border-left: 4px solid #0284c7;">
        <strong>Harga Modal (Max):</strong> <span id="infoHargaModalTertinggi">Rp 0</span>
    </div>

    <x-input-group label="Harga Jual Dasar (Regular)">
        <input type="number" class="input-control" id="editHargaReg" value="0" oninput="previewEditHarga()"
            style="font-size: 14px; font-weight: 600; padding: 10px;">
    </x-input-group>
    
    <x-input-group label="Status Tampil di Menu Penjualan">
        <select class="input-control" id="editStatusHarga" style="font-weight: 600;">
            <option value="Aktif">Aktif (Tampilkan)</option>
            <option value="Nonaktif">Nonaktif (Sembunyikan)</option>
        </select>
        <small style="color: var(--text-muted); display: block; margin-top: 4px;">Pilih Nonaktif untuk menyembunyikan barang ini dari kasir.</small>
    </x-input-group>
    
    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 12px;">
        <x-input-group label="Harga Member (<span id='labelDiskonMemEdit'>5</span>%)">
            <input type="text" class="input-control" id="editHargaMem" disabled style="background: #f3f4f6;">
        </x-input-group>
        <x-input-group label="Harga Langganan (<span id='labelDiskonLanEdit'>10</span>%)">
            <input type="text" class="input-control" id="editHargaLan" disabled style="background: #f3f4f6;">
        </x-input-group>
        <x-input-group label="Harga Bengkel (<span id='labelDiskonBengEdit'>15</span>%)">
            <input type="text" class="input-control" id="editHargaBeng" disabled style="background: #f3f4f6;">
        </x-input-group>
        <x-input-group label="Harga Teman (<span id='labelDiskonTemEdit'>20</span>%)">
            <input type="text" class="input-control" id="editHargaTem" disabled style="background: #f3f4f6;">
        </x-input-group>
    </div>
    
    <x-input-group label="Harga Grosir / VIP (<span id='labelDiskonGroEdit'>25</span>%)">
        <input type="text" class="input-control" id="editHargaGro" disabled style="background: #f3f4f6;">
    </x-input-group>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="closeEditHarga()">Batal</button>
        <button class="btn btn-primary" id="btnSimpanHarga" onclick="simpanEditHarga()">Simpan</button>
    </x-slot>
</x-modal>

<!-- Modal Tambah/Edit Barang -->
<x-modal id="modalAdminBarang" title="Tambah Barang Baru" titleId="modalAdminBarangTitle">
    <input type="hidden" id="formBarangId">
    <div class="grid" style="grid-template-columns: 1fr; gap: 16px;">
          <x-input-group label="Nama Barang *" marginBottom="0">
              <input type="text" class="input-control" id="formBarangNama" required>
          </x-input-group>
          <x-input-group label="Gambar (Opsional)" marginBottom="0">
              <input type="file" class="input-control" id="formBarangGambar" accept="image/*">
              <div id="gambarPreviewContainer" style="display: none; margin-top: 8px; border-radius: 8px; overflow: hidden; border: 1px solid #E5E7EB; width: fit-content; max-height: 150px;">
                  <img id="formBarangGambarPreview" src="" style="height: 100px; width: auto; object-fit: cover;">
              </div>
          </x-input-group>
          <x-input-group label="Lokasi Rak" marginBottom="0">
            <input type="text" class="input-control" id="formBarangLokasiRak" placeholder="Kosongkan jika belum ada">
        </x-input-group>
        <x-input-group label="Barcode 1" marginBottom="0">
            <input type="text" class="input-control" id="formBarangBarcode1" pattern="[0-9]*"
                inputmode="numeric" placeholder="Hanya Angka">
        </x-input-group>
        <x-input-group label="Barcode 2" marginBottom="0">
            <input type="text" class="input-control" id="formBarangBarcode2" pattern="[0-9]*"
                inputmode="numeric" placeholder="Hanya Angka">
        </x-input-group>
        <x-input-group label="Status Barang" marginBottom="0">
            <select class="input-control" id="formBarangStatus">
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </x-input-group>
    </div>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="tutupModalBarang()">Batal</button>
        <button class="btn btn-primary" id="btnSimpanBarang" onclick="simpanBarang()">Simpan</button>
    </x-slot>
</x-modal>

<!-- Modal Atur Supplier Barang -->
<x-modal id="modalBarangSupplier" title="Atur Supplier & Harga Beli">
    <input type="hidden" id="formBsIdBarang">
    <div style="margin-bottom: 20px;">
        <div style="font-weight: 600;" id="formBsNamaBarang">Nama Barang</div>
    </div>

    <div class="glass-card" style="padding: 16px; margin-bottom: 20px; background: rgba(0,0,0,0.02);">
        <div class="grid" style="grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: end;">
            <x-input-group label="Supplier" marginBottom="0">
                <select class="input-control" id="formBsSupplierSelect"></select>
            </x-input-group>
            <x-input-group label="Harga Beli" marginBottom="0">
                <input type="number" class="input-control" id="formBsHargaBeli" placeholder="0">
            </x-input-group>
            <x-input-group label="Diskon (%)" marginBottom="0">
                <input type="number" class="input-control" id="formBsDiskonPersen" placeholder="0" min="0" max="100">
            </x-input-group>
            <div style="grid-column: span 4;"></div>
            <x-input-group label="Satuan" marginBottom="0">
                <select class="input-control" id="formBsSatuan">
                    <option value="PCS">PCS</option>
                    <option value="BOTOL">BOTOL</option>
                    <option value="BOX">BOX</option>
                    <option value="SET">SET</option>
                    <option value="LITER">LITER</option>
                    <option value="LUSIN">LUSIN</option>
                    <option value="PACK">PACK</option>
                </select>
            </x-input-group>
            <x-input-group label="Isi per Box / Pak" marginBottom="0">
                <input type="number" class="input-control" id="formBsIsiPerBox" placeholder="1" min="1" value="1">
            </x-input-group>
            <x-input-group label="Is Utama?" marginBottom="0">
                <select class="input-control" id="formBsIsUtama">
                    <option value="false">Tidak</option>
                    <option value="true">â˜… Ya (Utama)</option>
                </select>
            </x-input-group>
            <button class="btn btn-primary" onclick="tambahBarangSupplier()">Tambah</button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <x-table :headers="['Supplier', 'Harga Beli', 'Diskon', 'Satuan', 'Isi/Box', 'Status', 'Aksi']">
            <tbody id="adminBarangSupplierTableBody">
                <tr>
                    <td colspan="7" style="text-align:center;">Memuat data...</td>
                </tr>
            </tbody>
        </x-table>
    </div>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="tutupModalBarangSupplier()">Tutup</button>
    </x-slot>
</x-modal>

<!-- Modal Tambah/Edit Supplier -->
<x-modal id="modalAdminSupplier" title="Tambah Supplier Baru" titleId="modalAdminSupplierTitle">
    <input type="hidden" id="formSupplierId">
    <x-input-group label="Nama Supplier *">
        <input type="text" class="input-control" id="formSupplierNama" placeholder="Contoh: PT Astra Otoparts"
            required>
    </x-input-group>
    
    <x-input-group marginBottom="20px">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label style="margin-bottom: 0;">Daftar PIC & Kontak (HP/WA)</label>
            <button type="button" class="btn btn-secondary btn-sm" onclick="tambahBarisPicForm()">
                <i class='bx bx-plus'></i> Tambah PIC
            </button>
        </div>
        <div id="supplierPicContainer" style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Dynamic PIC Rows -->
        </div>
    </x-input-group>
    
    <x-input-group label="Email">
        <input type="email" class="input-control" id="formSupplierEmail" placeholder="supplier@email.com">
    </x-input-group>
    
    <x-input-group id="formSupplierStatusGroup" label="Status Supplier" style="display:none;">
        <select id="formSupplierStatus" class="input-control">
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>
    </x-input-group>

    <x-slot name="footer">
        <button class="btn btn-secondary" onclick="tutupModalSupplier()">Batal</button>
        <button class="btn btn-primary" id="btnSimpanSupplier" onclick="simpanSupplier()">Simpan</button>
    </x-slot>
</x-modal>

<!-- Modal Tambah/Edit User -->
<x-modal id="modalAdminUser" title="Tambah User Baru" titleId="modalAdminUserTitle">
    <input type="hidden" id="formUserMode" value="add">

    <x-input-group label="Username (ID Login) *">
        <input type="text" id="formUserUsername" class="input-control" placeholder="Contoh: kasir1" required>
        <small style="color: var(--text-muted);">Username tidak bisa diubah setelah dibuat.</small>
    </x-input-group>
    
    <x-input-group label="Nama Lengkap *">
        <input type="text" id="formUserNama" class="input-control" placeholder="Nama lengkap user" required>
    </x-input-group>
    
    <x-input-group label="Password">
        <input type="text" id="formUserPassword" class="input-control"
            placeholder="Biarkan kosong jika tidak ingin ubah password saat edit">
        <small style="color: var(--text-muted);">Jika user baru, wajib diisi. Default: 123456</small>
    </x-input-group>
    
    <x-input-group label="Role / Hak Akses">
        <select id="formUserRole" class="input-control">
            <option value="Kasir">Kasir</option>
            <option value="Restocker">Restocker</option>
            <option value="Admin">Admin</option>
        </select>
    </x-input-group>
    
    <x-input-group label="Keterangan">
        <textarea id="formUserKeterangan" class="input-control" placeholder="Opsional, keterangan status user (cth: Sudah tidak kerja)" style="height: 60px; resize: none;"></textarea>
    </x-input-group>
    
    <x-input-group id="formUserStatusGroup" label="Status Akun" style="display:none;">
        <select id="formUserStatus" class="input-control">
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>
        <small style="color: var(--text-muted);">Ubah menjadi Nonaktif untuk mencabut akses user ini.</small>
    </x-input-group>

    <x-slot name="footer">
        <button class="btn btn-secondary"
            onclick="document.getElementById('modalAdminUser').classList.remove('active')">Batal</button>
        <button class="btn btn-primary" id="btnSimpanUser" onclick="simpanUser()">Simpan</button>
    </x-slot>
</x-modal>


<script src="/js/modules/admin.js?v={{ time() }}"></script>

