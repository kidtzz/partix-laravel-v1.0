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

    <script>
        let globalHistoriReturData = [];

        function loadHistoriReturLengkap() {
            const tbody = document.getElementById('tbodyReturnList');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);"><i class="bx bx-loader-alt bx-spin"></i> Memuat data...</td></tr>';
            
            BackendAPI.call('getDaftarReturLengkap', [])
                .then(res => {
                    globalHistoriReturData = res;
                    if (res.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada histori retur.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = res.map(r => {
                        const selisih = Number(r.selisih_harga) || 0;
                        let selisihHtml = '-';
                        if (selisih < 0) {
                            selisihHtml = `<span style="color: var(--danger-color); font-weight: 600;">Refund: ${formatRupiah(Math.abs(selisih))}</span>`;
                        } else if (selisih > 0) {
                            selisihHtml = `<span style="color: var(--success-color); font-weight: 600;">Nambah: ${formatRupiah(selisih)}</span>`;
                        }
                        
                        return `
                        <tr>
                            <td style="font-size: 13px; font-weight: 600; color: var(--primary-color);">${r.no_return}</td>
                            <td>${new Date(r.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</td>
                            <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">${r.no_invoice}</span></td>
                            <td>${r.kasir}</td>
                            <td><span class="badge badge-secondary">${r.jenis_return}</span></td>
                            <td>${selisihHtml}</td>
                            <td>
                                <button class="btn btn-outline btn-sm" onclick="printInvoiceReturn('${r.no_return}')" style="padding: 4px 8px;">
                                    <i class='bx bx-printer'></i> Cetak
                                </button>
                            </td>
                        </tr>
                        `;
                    }).join('');
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--danger-color);">Gagal memuat data: ${err.message}</td></tr>`;
                });
        }

        function printInvoiceReturn(noReturn) {
            const data = globalHistoriReturData.find(r => r.no_return === noReturn);
            if (!data) return showToast("Data retur tidak ditemukan!", "error");

            let itemsHtml = '';
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    itemsHtml += `
                        <tr><td colspan="2" class="bold">${item.nama_barang_kembali}</td></tr>
                        <tr>
                            <td>Retur: ${item.qty_kembali} PCS</td>
                            <td class="right"></td>
                        </tr>
                    `;
                    if (item.nama_barang_pengganti) {
                        itemsHtml += `
                            <tr><td colspan="2" style="padding-left: 10px;">➜ Ganti: ${item.nama_barang_pengganti} (${item.qty_pengganti} PCS)</td></tr>
                        `;
                    }
                });
            }

            const selisih = Number(data.selisih_harga) || 0;
            let selisihInfo = "Tidak ada selisih biaya.";
            if (selisih < 0) selisihInfo = `Refund Tunai ke Pelanggan: Rp ${Math.abs(selisih).toLocaleString('id-ID')}`;
            if (selisih > 0) selisihInfo = `Terima Tunai dari Pelanggan: Rp ${selisih.toLocaleString('id-ID')}`;

            const htmlContent = `
                <div class="center bold" style="font-size: 16px; margin-bottom: 5px;">PARTIX BENGKEL</div>
                <div class="center" style="margin-bottom: 15px;">BUKTI TRANSAKSI RETUR</div>
                
                <table>
                    <tr><td>No Retur</td><td class="right">${data.no_return}</td></tr>
                    <tr><td>Tgl</td><td class="right">${new Date(data.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</td></tr>
                    <tr><td>Kasir</td><td class="right">${data.kasir}</td></tr>
                    <tr><td>Inv Asal</td><td class="right">${data.no_invoice}</td></tr>
                    <tr><td>Jenis</td><td class="right">${data.jenis_return}</td></tr>
                </table>
                
                <div class="divider"></div>
                
                <table>
                    ${itemsHtml}
                </table>
                
                <div class="divider"></div>
                
                <div class="center bold" style="margin: 15px 0;">
                    ${selisihInfo}
                </div>
                
                <div class="divider"></div>
                
                <div class="center" style="margin-top: 20px;">
                    Terima kasih<br>
                    Barang yang sudah dibeli tidak dapat ditukar kecuali ada cacat pabrik.
                </div>
            `;

            const printWindow = window.open('', '_blank', 'width=400,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Struk Retur - ${noReturn}</title>
                    <style>
                        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; margin: 0; padding: 20px; }
                        .center { text-align: center; }
                        .bold { font-weight: bold; }
                        .divider { border-bottom: 1px dashed #000; margin: 12px 0; }
                        table { width: 100%; border-collapse: collapse; }
                        td { padding: 4px 0; vertical-align: top; }
                        .right { text-align: right; }
                    </style>
                </head>
                <body>
                    ${htmlContent}
                    <script>
                        window.onload = function() { 
                            setTimeout(() => {
                                window.print(); 
                                window.close(); 
                            }, 300);
                        }
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
        
        // Modal detail tak lagi dipakai, dibiarkan kosong atau dihapus.

        document.addEventListener('DOMContentLoaded', () => {
            const navItem = document.querySelector('li[data-target="return-list"]');
            if (navItem) {
                navItem.addEventListener('click', () => {
                    setTimeout(loadHistoriReturLengkap, 100);
                });
            }
        });
    </script>
</section>

<section id="view-return" class="view-section">
<style>
    .return-grid-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    @media (min-width: 768px) {
        .return-grid-layout {
            grid-template-columns: 320px 1fr;
            gap: 24px;
        }
    }
    .invoice-receipt {
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: var(--radius-md);
        padding: 16px;
        margin-top: 20px;
    }
    .return-item-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: var(--radius-md);
        padding: 16px;
        box-shadow: var(--shadow-xs);
        transition: all var(--transition-fast);
    }
    .return-item-card:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-sm);
    }
    .return-checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        background: #F1F5F9;
        padding: 6px 12px;
        border-radius: var(--radius-full);
        border: 1px solid transparent;
        transition: all var(--transition-fast);
    }
    .return-checkbox-wrapper:hover {
        background: #E2E8F0;
    }
    .return-checkbox-wrapper.active {
        background: var(--primary-light);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
</style>
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

<script>
    let currentInvoice = null;
    let currentReturnItems = {}; // id_barang -> { qty_return, jenis, harga_satuan }

    function cariInvoice() {
        const input = document.getElementById('searchInvoice').value.trim();
        if (!input) return;

        const btn = document.querySelector('button[onclick="cariInvoice()"]');
        btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;

        BackendAPI.call('verifikasiInvoice', [input])
            .then(res => {
                currentInvoice = res;
                renderInvoiceInfo();
                renderReturnItems();
            })
            .catch(err => {
                showToast(err.message, 'error');
                resetReturnView();
            })
            .finally(() => {
                btn.innerHTML = `<i class='bx bx-search'></i> Cari`;
            });
    }

    function renderInvoiceInfo() {
        const header = currentInvoice.header;
        document.getElementById('emptyInvoiceState').style.display = 'none';
        document.getElementById('invoiceInfoPanel').style.display = 'block';

        const tgl = new Date(header.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });

        document.getElementById('invInfoNo').textContent = header.no_invoice;
        document.getElementById('invInfoTgl').textContent = tgl;
        document.getElementById('invInfoKasir').textContent = header.kasir;
        document.getElementById('invInfoTotal').textContent = formatRupiah(header.total);

        if (header.is_returned) {
            document.getElementById('invInfoStatus').innerHTML = `<span class="badge badge-danger" style="font-size: 11px;"><i class='bx bx-error-circle'></i> Sudah Pernah Diretur</span>`;
        } else {
            document.getElementById('invInfoStatus').innerHTML = ``;
        }
    }

    function renderReturnItems() {
        const container = document.getElementById('returnItemsContainer');
        container.style.display = 'flex';
        currentReturnItems = {};
        
        const isAlreadyReturned = currentInvoice.header.is_returned;
        
        if (isAlreadyReturned) {
            document.getElementById('btnProsesReturn').disabled = true;
            document.getElementById('btnProsesReturn').innerHTML = "Sudah Diretur";
        } else {
            document.getElementById('btnProsesReturn').disabled = false;
            document.getElementById('btnProsesReturn').innerHTML = "<i class='bx bx-check'></i> Proses Return";
        }

        // Render semua item dari detail transaksi
        const itemsHtml = currentInvoice.detail.map((d, index) => {
            const formId = `returnForm_${index}`;
            return `
            <div class="return-item-card">
                <div class="flex justify-between items-center mb-2" style="flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">${d.nama_barang}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            <span class="badge badge-secondary" style="font-size: 10px; margin-right: 4px;">${d.qty} PCS</span> 
                            @ ${formatRupiah(d.harga_satuan)}
                        </div>
                    </div>
                    ${isAlreadyReturned ? '' : `
                    <label class="return-checkbox-wrapper" id="lblCheck_${index}">
                        <input type="checkbox" style="width: 16px; height: 16px; accent-color: var(--primary-color);" id="chk_${index}" onchange="toggleReturnForm('${index}', this.checked, '${d.id_barang}', ${d.qty}, ${d.harga_satuan})">
                        <span style="font-size: 12px; font-weight: 600;">Pilih Retur</span>
                    </label>
                    `}
                </div>
                
                <div id="${formId}" style="display: none; background: #F8FAFC; padding: 16px; border-radius: var(--radius-md); margin-top: 16px; border: 1px solid #E2E8F0;">
                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div class="input-group mb-0">
                            <label>Qty Diretur</label>
                            <input type="number" class="input-control" id="qtyRet_${index}" max="${d.qty}" min="1" value="1" onchange="updateReturnItem('${index}')">
                        </div>
                        <div class="input-group mb-0">
                            <label>Jenis Penyelesaian</label>
                            <select class="input-control" id="jenisRet_${index}" onchange="updateReturnItem('${index}')">
                                <option value="Tukar Barang Sama">Tukar Barang Sama (Ganti Baru)</option>
                                <option value="Tukar Barang Lain">Tukar Barang Lain (Tukar Tambah)</option>
                                <option value="Refund Uang">Refund Uang Tunai</option>
                            </select>
                        </div>
                        <div class="input-group mb-0">
                            <label>Alasan Return</label>
                            <select class="input-control" id="alasanRet_${index}" onchange="updateReturnItem('${index}')">
                                <option value="Cacat Pabrik">Cacat Pabrik (Masuk Karantina)</option>
                                <option value="Rusak">Rusak (Masuk Karantina)</option>
                                <option value="Salah Beli">Salah Beli (Tidak Masuk Stok)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Area Tukar Barang Lain -->
                    <div id="tukarLainArea_${index}" style="display: none; margin-top: 16px; padding: 16px; background: #fff; border: 1px dashed var(--primary-color); border-radius: var(--radius-md);">
                        <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--primary-color);"><i class='bx bx-search'></i> Pilih Barang Pengganti</div>
                        <div class="input-group mb-2">
                            <div class="search-bar" style="max-width: 100%;">
                                <i class='bx bx-search'></i>
                                <input type="text" class="input-control" style="border-radius: var(--radius-md);" id="searchBarang_${index}" placeholder="Ketik nama atau scan barcode barang pengganti..." oninput="searchPengganti('${index}')">
                            </div>
                            <div id="searchResult_${index}" style="position: absolute; width: 100%; max-height: 250px; overflow-y: auto; background: white; border: 1px solid #e2e8f0; border-radius: var(--radius-md); z-index: 10; display: none; box-shadow: var(--shadow-md); margin-top: 40px;"></div>
                        </div>
                        <div id="selectedPenggantiInfo_${index}" style="display: none; font-size: 13px; background: #F1F5F9; padding: 12px; border-radius: var(--radius-md); border: 1px solid #E2E8F0;">
                            <div class="flex justify-between items-center mb-2" style="flex-wrap: wrap; gap: 12px;">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main);" id="namaPengganti_${index}">-</div>
                                    <div style="color: var(--text-muted); margin-top: 4px;">
                                        Harga: <span id="hargaPengganti_${index}" style="font-weight: 600;">-</span> 
                                        <span style="margin: 0 8px;">|</span> 
                                        Sisa Stok: <span id="stokPengganti_${index}" style="font-weight: 600;">-</span>
                                    </div>
                                </div>
                                <div style="width: 100px;">
                                    <label style="font-size: 10px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Qty Pengganti</label>
                                    <input type="number" class="input-control" style="min-height: 30px; padding: 4px 8px;" id="qtyPengganti_${index}" min="1" value="1" onchange="hitungSelisih('${index}')">
                                </div>
                            </div>
                            <div id="selisihInfo_${index}" style="margin-top: 12px; padding: 10px; border-radius: var(--radius-sm); text-align: center; font-weight: 600;"></div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }).join('');

        container.innerHTML = itemsHtml + `
            <div style="margin-top: auto; padding-top: 24px; border-top: 1px solid var(--border-solid); text-align: right;">
                <button class="btn btn-primary" onclick="prosesReturnAPI()" id="btnProsesReturn">
                    <i class='bx bx-check'></i> Proses Return
                </button>
            </div>
        `;
    }

    function toggleReturnForm(index, isChecked, idBarang, maxQty, hargaSatuan) {
        document.getElementById(`returnForm_${index}`).style.display = isChecked ? 'block' : 'none';
        
        const lbl = document.getElementById(`lblCheck_${index}`);
        if (isChecked) {
            lbl.classList.add('active');
            currentReturnItems[index] = {
                id_barang_direturn: idBarang,
                qty_return: Number(document.getElementById(`qtyRet_${index}`).value),
                jenis: document.getElementById(`jenisRet_${index}`).value,
                alasan_return: document.getElementById(`alasanRet_${index}`).value,
                harga_satuan: hargaSatuan,
                // untuk tukar tambah
                id_barang_pengganti: null,
                qty_pengganti: 0,
                harga_pengganti: 0,
                selisih_harga: 0
            };
            updateReturnItem(index);
        } else {
            lbl.classList.remove('active');
            delete currentReturnItems[index];
        }
    }

    function updateReturnItem(index) {
        if (currentReturnItems[index]) {
            currentReturnItems[index].qty_return = Number(document.getElementById(`qtyRet_${index}`).value);
            const jenis = document.getElementById(`jenisRet_${index}`).value;
            currentReturnItems[index].jenis = jenis;
            currentReturnItems[index].alasan_return = document.getElementById(`alasanRet_${index}`).value;
            
            const areaLain = document.getElementById(`tukarLainArea_${index}`);
            if (jenis === "Tukar Barang Lain") {
                areaLain.style.display = "block";
            } else {
                areaLain.style.display = "none";
                currentReturnItems[index].id_barang_pengganti = null;
                currentReturnItems[index].selisih_harga = 0;
            }
            hitungSelisih(index);
        }
    }
    
    // ==========================================
    // LOGIKA TUKAR TAMBAH (Pencarian Barang)
    // ==========================================
    let searchTimeoutRet = null;
    function searchPengganti(index) {
        clearTimeout(searchTimeoutRet);
        const query = document.getElementById(`searchBarang_${index}`).value.trim();
        const resDiv = document.getElementById(`searchResult_${index}`);
        
        if (query.length < 2) {
            resDiv.style.display = 'none';
            return;
        }
        
        searchTimeoutRet = setTimeout(() => {
            BackendAPI.call('cariBarangAktif', [query])
                .then(res => {
                    if (res.length === 0) {
                        resDiv.innerHTML = '<div style="padding: 12px; text-align: center; color: var(--text-muted); font-size: 13px;">Barang tidak ditemukan atau stok kosong.</div>';
                    } else {
                        resDiv.innerHTML = res.map(b => `
                            <div style="padding: 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; hover:background: #f8fafc;" 
                                 onclick="pilihPengganti('${index}', '${b.id_barang}', '${b.nama_barang}', ${b.stok_saat_ini}, ${b.harga_jual})">
                                <div style="font-weight: 600; font-size: 13px;">${b.nama_barang}</div>
                                <div style="font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between;">
                                    <span>Rp ${Number(b.harga_jual).toLocaleString('id-ID')}</span>
                                    <span style="color: ${b.stok_saat_ini > 0 ? 'var(--success-color)' : 'var(--danger-color)'}">Stok: ${b.stok_saat_ini}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                    resDiv.style.display = 'block';
                });
        }, 500);
    }
    
    function pilihPengganti(index, idBarang, nama, stok, harga) {
        if (stok <= 0) {
            showToast("Stok barang ini kosong, tidak bisa dijadikan pengganti!", "error");
            return;
        }
        
        document.getElementById(`searchResult_${index}`).style.display = 'none';
        document.getElementById(`searchBarang_${index}`).value = '';
        
        document.getElementById(`selectedPenggantiInfo_${index}`).style.display = 'block';
        document.getElementById(`namaPengganti_${index}`).innerText = nama;
        document.getElementById(`hargaPengganti_${index}`).innerText = formatRupiah(harga);
        document.getElementById(`stokPengganti_${index}`).innerText = stok;
        document.getElementById(`qtyPengganti_${index}`).max = stok;
        document.getElementById(`qtyPengganti_${index}`).value = 1;
        
        if (currentReturnItems[index]) {
            currentReturnItems[index].id_barang_pengganti = idBarang;
            currentReturnItems[index].harga_pengganti = harga;
            hitungSelisih(index);
        }
    }
    
    function hitungSelisih(index) {
        const item = currentReturnItems[index];
        if (!item) return;
        
        let selisihInfo = document.getElementById(`selisihInfo_${index}`);
        if (!selisihInfo) return;

        if (item.jenis === "Tukar Barang Lain" && item.id_barang_pengganti) {
            item.qty_pengganti = Number(document.getElementById(`qtyPengganti_${index}`).value);
            
            const totalRetur = item.qty_return * item.harga_satuan; // Nilai barang yang dikembalikan
            const totalPengganti = item.qty_pengganti * item.harga_pengganti; // Nilai barang baru
            
            // Positif = Pelanggan Kurang Bayar, Negatif = Toko Refund Uang
            item.selisih_harga = totalPengganti - totalRetur;
            
            if (item.selisih_harga > 0) {
                selisihInfo.style.background = '#fef2f2';
                selisihInfo.style.color = 'var(--danger-color)';
                selisihInfo.innerHTML = `Pelanggan Tambah Bayar: ${formatRupiah(item.selisih_harga)}`;
            } else if (item.selisih_harga < 0) {
                selisihInfo.style.background = '#f0fdf4';
                selisihInfo.style.color = 'var(--success-color)';
                selisihInfo.innerHTML = `Kembalian/Refund: ${formatRupiah(Math.abs(item.selisih_harga))}`;
            } else {
                selisihInfo.style.background = '#f1f5f9';
                selisihInfo.style.color = 'var(--text-muted)';
                selisihInfo.innerHTML = `Pas (Tidak ada selisih)`;
            }
        } else if (item.jenis === "Refund Uang") {
            item.selisih_harga = -(item.qty_return * item.harga_satuan);
        } else if (item.jenis === "Tukar Barang Sama") {
            item.selisih_harga = 0;
        }
    }

    function prosesReturnAPI() {
        const keys = Object.keys(currentReturnItems);
        if (keys.length === 0) {
            return showToast("Pilih minimal 1 barang untuk direturn!", "error");
        }

        let selisihBayar = 0;
        const items = [];

        for (let k of keys) {
            const ri = currentReturnItems[k];
            
            // Tambahkan selisih dari item ini ke total selisih
            selisihBayar += ri.selisih_harga;
            
            let idPengganti = "";
            let qtyPengganti = 0;
            
            if (ri.jenis === "Tukar Barang Sama") {
                idPengganti = ri.id_barang_direturn;
                qtyPengganti = ri.qty_return;
            } else if (ri.jenis === "Tukar Barang Lain") {
                idPengganti = ri.id_barang_pengganti;
                qtyPengganti = ri.qty_pengganti;
                if (!idPengganti) {
                    return showToast("Pilih barang pengganti untuk Tukar Barang Lain!", "error");
                }
            }

            items.push({
                id_barang_direturn: ri.id_barang_direturn,
                qty_return: ri.qty_return,
                id_barang_pengganti: idPengganti,
                qty_pengganti: qtyPengganti,
                alasan_return: ri.alasan_return
            });
        }

        const noInvoice = currentInvoice.header.no_invoice;
        let jenisGlobal = "Campuran";
        if (items.every(i => !i.id_barang_pengganti)) jenisGlobal = "Refund Uang";
        else if (items.every(i => i.id_barang_pengganti === i.id_barang_direturn)) jenisGlobal = "Tukar Barang Sama";
        else if (items.some(i => i.id_barang_pengganti && i.id_barang_pengganti !== i.id_barang_direturn)) jenisGlobal = "Tukar Tambah";

        const btn = document.getElementById('btnProsesReturn');
        btn.disabled = true;
        btn.innerHTML = "Memproses...";

        BackendAPI.call('prosesReturn', [noInvoice, items, jenisGlobal, selisihBayar])
            .then(res => {
                const finishReturnProcess = () => {
                    showToast(`Return Berhasil! No: ${res.noReturn}`, "success");
                    resetReturnView();
                    loadHistoriReturLengkap(); // Auto-refresh histori retur
                };

                if (selisihBayar < 0) {
                    showReturnPaymentModal('refund', Math.abs(selisihBayar), finishReturnProcess);
                } else if (selisihBayar > 0) {
                    showReturnPaymentModal('receive', selisihBayar, finishReturnProcess);
                } else {
                    finishReturnProcess();
                }
            })
            .catch(err => showToast(err.message, "error"))
            .finally(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = "<i class='bx bx-check'></i> Proses Return"; }
            });
    }

    function resetReturnView() {
        document.getElementById('emptyInvoiceState').style.display = 'block';
        document.getElementById('invoiceInfoPanel').style.display = 'none';
        document.getElementById('returnItemsContainer').style.display = 'none';
        document.getElementById('searchInvoice').value = '';
        currentInvoice = null;
        currentReturnItems = {};
    }

    function showReturnPaymentModal(type, amount, callback) {
        const titleEl = document.getElementById('returnPaymentTitle');
        const iconEl = document.getElementById('returnPaymentIcon');
        const amountEl = document.getElementById('returnPaymentAmount');
        
        if (type === 'refund') {
            titleEl.textContent = "Kembalikan Uang Tunai ke Pelanggan";
            iconEl.className = "bx bx-log-out-circle";
            iconEl.style.color = "var(--danger-color)";
            amountEl.style.color = "var(--danger-color)";
        } else {
            titleEl.textContent = "Terima Uang Tunai dari Pelanggan";
            iconEl.className = "bx bx-log-in-circle";
            iconEl.style.color = "var(--secondary-color)";
            amountEl.style.color = "var(--secondary-color)";
        }
        
        amountEl.textContent = formatRupiah(amount);
        document.getElementById('returnPaymentModal').classList.add('active');

        const btnClose = document.getElementById('btnTutupPaymentModal');
        btnClose.onclick = function() {
            document.getElementById('returnPaymentModal').classList.remove('active');
            if (callback) callback();
        };
    }
</script>
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

<script>
    // Memindahkan modal ke body (teleport) agar tidak terpengaruh oleh stacking context (.main-content / .views-container)
    // sehingga dapat menutupi sidebar dan topbar secara penuh (full page)
    (function() {
        const modal = document.getElementById('returnPaymentModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    })();
</script>

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

    <script>
        // Tab switching
        function switchReturTab(tab) {
            document.getElementById('tabKarantina').style = "padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--text-muted); cursor:pointer;";
            document.getElementById('tabHistoriRetur').style = "padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--text-muted); cursor:pointer;";
            
            document.getElementById('contentKarantina').style.display = 'none';
            document.getElementById('contentHistoriRetur').style.display = 'none';
            
            if (tab === 'karantina') {
                document.getElementById('tabKarantina').style = "padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--primary-color); border-bottom: 2px solid var(--primary-color); cursor:pointer;";
                document.getElementById('contentKarantina').style.display = 'block';
                loadListBarangReturn();
            } else {
                document.getElementById('tabHistoriRetur').style = "padding: 10px 16px; border:none; background:none; font-weight:600; color:var(--primary-color); border-bottom: 2px solid var(--primary-color); cursor:pointer;";
                document.getElementById('contentHistoriRetur').style.display = 'block';
                loadHistoriReturSupplier();
            }
        }

        // Load Tab 1
        function loadListBarangReturn() {
            const tbody = document.getElementById('tbodyBarangReturn');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--text-muted);"><i class="bx bx-loader-alt bx-spin"></i> Memuat data...</td></tr>';
            
            BackendAPI.call('getListBarangReturn', [])
                .then(res => {
                    if (res.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Tidak ada barang karantina yang menunggu diretur.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = res.map(r => `
                        <tr>
                            <td style="font-size: 13px; color: var(--text-muted);">${r.id_return}</td>
                            <td>${new Date(r.tanggal).toLocaleDateString('id-ID')}</td>
                            <td style="font-weight: 500;">${r.nama_barang}</td>
                            <td><b style="color: var(--danger-color);">${r.qty_rusak}</b></td>
                            <td>${r.alasan} (Dari: ${r.no_invoice})</td>
                            <td style="text-align: center;">
                                <button class="btn btn-primary" style="padding: 4px 12px; font-size: 11px;" 
                                    onclick="openModalRetur('${r.id_return}', '${r.nama_barang}', ${r.qty_rusak})">
                                    Proses Retur
                                </button>
                            </td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger-color);">Gagal memuat data: ${err.message}</td></tr>`;
                });
        }
        
        // Load Tab 2
        function loadHistoriReturSupplier() {
            const tbody = document.getElementById('tbodyHistoriRetur');
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: var(--text-muted);"><i class="bx bx-loader-alt bx-spin"></i> Memuat histori...</td></tr>';
            
            BackendAPI.call('getHistoriReturSupplier', [])
                .then(res => {
                    if (res.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada histori retur ke supplier.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = res.map(h => `
                        <tr>
                            <td>${new Date(h.tanggal_retur).toLocaleDateString('id-ID')}</td>
                            <td style="font-size: 11px; color: var(--text-muted);">${h.id_return_supplier}</td>
                            <td style="font-weight: 500;">${h.nama_supplier}</td>
                            <td>${h.nama_barang}</td>
                            <td><b>${h.qty_retur}</b></td>
                            <td>Rp ${Number(h.harga_beli).toLocaleString('id-ID')}</td>
                            <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-weight:500;">${h.no_invoice_supplier}</span></td>
                            <td>${h.user}</td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--danger-color);">Gagal memuat data: ${err.message}</td></tr>`;
                });
        }

        // Modal Logic
        function openModalRetur(id_barang_return, nama_barang, max_qty) {
            document.getElementById('rsIdBarangReturn').value = id_barang_return;
            document.getElementById('rsNamaBarang').value = nama_barang;
            document.getElementById('rsMaxQty').value = max_qty;
            document.getElementById('rsLabelMaxQty').innerText = max_qty;
            document.getElementById('rsQty').max = max_qty;
            document.getElementById('rsQty').value = max_qty;
            
            document.getElementById('rsNoInvoice').value = '';
            document.getElementById('rsHargaBeli').value = '';
            
            // Populate suppliers
            BackendAPI.call('getSuppliers', [])
                .then(sups => {
                    const sel = document.getElementById('rsSupplier');
                    sel.innerHTML = '<option value="">-- Pilih Supplier --</option>' + 
                        sups.filter(s => s.status_supplier === 'Aktif').map(s => `<option value="${s.id_supplier}">${s.nama_supplier}</option>`).join('');
                });
                
            document.getElementById('modalReturSupplier').classList.add('active');
        }
        
        function closeModalRetur() {
            document.getElementById('modalReturSupplier').classList.remove('active');
        }
        
        function submitReturSupplier(btn) {
            const payload = {
                id_barang_return: document.getElementById('rsIdBarangReturn').value,
                qty_retur: document.getElementById('rsQty').value,
                id_supplier: document.getElementById('rsSupplier').value,
                no_invoice_supplier: document.getElementById('rsNoInvoice').value,
                harga_beli: document.getElementById('rsHargaBeli').value,
                user: (typeof AppState !== 'undefined' && AppState.user) ? AppState.user.nama : "Admin"
            };
            
            if (!payload.id_supplier) return showToast("Pilih supplier tujuan!", "error");
            if (!payload.no_invoice_supplier) return showToast("Masukkan nomor invoice!", "error");
            if (!payload.harga_beli || payload.harga_beli <= 0) return showToast("Masukkan harga beli!", "error");
            if (Number(payload.qty_retur) > Number(document.getElementById('rsMaxQty').value)) return showToast("Qty melebihi batas maksimal!", "error");
            
            btn.disabled = true;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Memproses...";
            
            BackendAPI.call('prosesReturSupplier', [payload])
                .then(res => {
                    showToast("Retur ke supplier berhasil dicatat!", "success");
                    closeModalRetur();
                    loadListBarangReturn();
                    loadHistoriReturSupplier();
                })
                .catch(err => showToast(err.message, "error"))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = "<i class='bx bx-send'></i> Kirim Retur";
                });
        }

        // Init default jika user ada di tab return
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => switchReturTab('karantina'), 500);

            // Observasi saat tab Return aktif agar otomatis refresh
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.target.id === 'view-return' && mutation.target.classList.contains('active')) {
                        loadListBarangReturn();
                        loadHistoriReturSupplier();
                    }
                });
            });
            const viewReturn = document.getElementById('view-return');
            if (viewReturn) {
                observer.observe(viewReturn, { attributes: true, attributeFilter: ['class'] });
            }

            // Pindahkan modal ke luar dari root element (ke body) agar overlay full screen
            const modalReturSupplier = document.getElementById('modalReturSupplier');
            if (modalReturSupplier) {
                document.body.appendChild(modalReturSupplier);
            }
            
            const modalDetailReturn = document.getElementById('modalDetailReturn');
            if (modalDetailReturn) {
                document.body.appendChild(modalDetailReturn);
            }

            const navItem = document.querySelector('li[data-target="return-supplier"]');
            if (navItem) {
                navItem.addEventListener('click', () => {
                    setTimeout(() => switchReturTab('karantina'), 100);
                });
            }
        });
    </script>
</section>
