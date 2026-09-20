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
                            selisihHtml = `<span style="color: var(--danger-color);">Refund: ${formatRupiah(Math.abs(selisih))}</span>`;
                        } else if (selisih > 0) {
                            selisihHtml = `<span style="color: var(--success-color);">Nambah: ${formatRupiah(selisih)}</span>`;
                        }
                        
                        return `
                        <tr>
                            <td>${r.no_return}</td>
                            <td>${new Date(r.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</td>
                            <td>${r.no_invoice}</td>
                            <td>${r.kasir}</td>
                            <td>${r.jenis_return}</td>
                            <td>${selisihHtml}</td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" style="font-weight: 400;" onclick="detailRetur('${r.no_return}')">
                                    <i class='bx bx-receipt'></i> Detail
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

        function detailRetur(noReturn) {
            const data = globalHistoriReturData.find(r => r.no_return === noReturn);
            if (!data) return showToast("Data retur tidak ditemukan!", "error");
            
            document.getElementById('modalDetailRetur').classList.add('active');

            let itemsHtml = '';
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td style="padding:12px; border-bottom:1px solid #e2e8f0; color: var(--text-main);">
                                ${item.nama_barang_kembali} <span style="color: var(--danger-color); font-size: 10px;">(Retur)</span>
                            </td>
                            <td style="padding:12px; border-bottom:1px solid #e2e8f0; text-align:center; color: var(--text-main);">${item.qty_kembali}</td>
                        </tr>
                    `;
                    if (item.nama_barang_pengganti) {
                        itemsHtml += `
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #e2e8f0; color: var(--success-color);">
                                    ➜ Ganti: ${item.nama_barang_pengganti}
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #e2e8f0; text-align:center; color: var(--success-color);">${item.qty_pengganti}</td>
                            </tr>
                        `;
                    }
                });
            } else {
                itemsHtml = `<tr><td colspan="2" style="padding:12px; text-align:center; color: var(--text-muted);">Tidak ada detail barang.</td></tr>`;
            }

            const selisih = Number(data.selisih_harga) || 0;
            let selisihInfo = "Tidak ada selisih biaya.";
            if (selisih < 0) selisihInfo = `Refund Tunai ke Pelanggan: Rp ${Math.abs(selisih).toLocaleString('id-ID')}`;
            if (selisih > 0) selisihInfo = `Terima Tunai dari Pelanggan: Rp ${selisih.toLocaleString('id-ID')}`;

            const html = `
                <div style="display:flex; justify-content:space-between; margin-bottom: 16px; font-size: 12px;">
                    <div>
                        <div style="font-weight:600; color: var(--text-main); margin-bottom: 4px;">No Retur: ${data.no_return}</div>
                        <div>${new Date(data.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</div>
                        <div style="margin-top: 4px;">Jenis: ${data.jenis_return}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="margin-bottom: 4px;">Kasir: ${data.kasir}</div>
                        <div>Inv Asal: ${data.no_invoice}</div>
                    </div>
                </div>
                <table style="width:100%; border-collapse:collapse; font-size: 12px;">
                    <thead style="background:#f1f5f9; text-align:left; border-radius: 8px;">
                        <tr>
                            <th style="padding:12px; font-weight: 600; color: var(--text-main);">Barang</th>
                            <th style="padding:12px; text-align:center; font-weight: 600; color: var(--text-main);">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e2e8f0; font-size: 12px; text-align:center;">
                    <strong style="color: var(--text-main);">${selisihInfo}</strong>
                </div>
            `;
            document.getElementById('detailReturBody').innerHTML = html;
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
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">${d.nama_barang}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">
                            <span class="badge badge-secondary" style="font-size: 11px; padding: 2px 6px; margin-right: 6px;">${d.qty} PCS</span> 
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
                
                <div id="${formId}" style="display: none; background: var(--surface-light, rgba(255,255,255,0.01)); padding: 16px; border-radius: var(--radius-md); margin-top: 16px; border: 1px solid var(--border-color);">
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
                                <input type="text" class="input-control" style="border-radius: var(--radius-md);" id="searchBarang_${index}" placeholder="Ketik nama atau scan barcode barang pengganti..." oninput="searchPengganti('${index}')" onfocus="searchPengganti('${index}')" onblur="setTimeout(() => { const el = document.getElementById('searchResult_${index}'); if(el) el.style.display = 'none'; }, 200)">
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
            <div style="margin-top: auto; padding-top: 12px; text-align: right;">
                <button class="btn btn-primary" onclick="prosesReturnAPI()" id="btnProsesReturn" style="min-height: 44px; font-size: 14px; width: 100%; justify-content: center; border-radius: 12px;">
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
        
        // Removed minimum query length restriction so it fetches all data if empty
        
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

// Memindahkan modal ke body (teleport) agar tidak terpengaruh oleh stacking context (.main-content / .views-container)
    // sehingga dapat menutupi sidebar dan topbar secara penuh (full page)
    (function() {
        const modal = document.getElementById('returnPaymentModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    })();

// Tab switching
        let globalBarangReturnData = [];
                // Load Tab 1
        function loadListBarangReturn() {
            const tbody = document.getElementById('tbodyBarangReturn');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);"><i class="bx bx-loader-alt bx-spin"></i> Memuat data...</td></tr>';
            
            BackendAPI.call('getListBarangReturn', [])
                .then(res => {
                    globalBarangReturnData = res;
                    if (res.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Tidak ada barang karantina yang menunggu diretur.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = res.map(r => `
                        <tr>
                            <td><div>${r.id_return}</div></td>
                            <td>${new Date(r.tanggal).toLocaleDateString('id-ID')}</td>
                            <td><div>${r.no_invoice}</div></td>
                            <td>${r.nama_barang}</td>
                            <td style="color: var(--danger-color);">${r.qty_rusak}</td>
                            <td>
                                <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${r.alasan}">
                                    ${r.alasan}
                                </div>
                            </td>
                            <td style="text-align: center; display: flex; gap: 8px; justify-content: center;">
                                <button class="btn btn-secondary btn-sm" style="font-weight: 400;" 
                                    onclick="detailKarantina('${r.id_return}')">
                                    <i class='bx bx-receipt'></i> Detail
                                </button>
                                <button class="btn btn-primary btn-sm" style="font-weight: 400;" 
                                    onclick="openModalRetur('${r.id_return}', '${r.nama_barang}', ${r.qty_rusak})">
                                    Proses Retur
                                </button>
                            </td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--danger-color);">Gagal memuat data: ${err.message}</td></tr>`;
                });
        }
        
        function detailKarantina(idReturn) {
            const data = globalBarangReturnData.find(r => r.id_return === idReturn);
            if (!data) return showToast("Data tidak ditemukan!", "error");
            
            document.getElementById('modalDetailKarantina').classList.add('active');

            const html = `
                <div style="display:flex; justify-content:space-between; margin-bottom: 16px; font-size: 12px;">
                    <div>
                        <div style="font-weight:600; color: var(--text-main); margin-bottom: 4px;">ID Karantina: ${data.id_return}</div>
                        <div>${new Date(data.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</div>
                        <div style="margin-top: 4px;">Kasir: ${data.kasir}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="margin-bottom: 4px;">Inv Asal: ${data.no_invoice}</div>
                    </div>
                </div>
                <table style="width:100%; border-collapse:collapse; font-size: 12px; margin-bottom: 16px;">
                    <thead style="background:#f1f5f9; text-align:left; border-radius: 8px;">
                        <tr>
                            <th style="padding:12px; font-weight: 600; color: var(--text-main);">Barang Retur</th>
                            <th style="padding:12px; font-weight: 600; text-align:center; color: var(--text-main);">Qty Rusak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:12px; border-bottom:1px solid #e2e8f0; color: var(--text-main);">
                                <div style="font-weight: 500;">${data.nama_barang}</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">Kode: ${data.id_barang}</div>
                            </td>
                            <td style="padding:12px; border-bottom:1px solid #e2e8f0; text-align:center; color: var(--danger-color); font-weight: 600;">${data.qty_rusak}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="background: var(--surface-light, #f8fafc); padding: 12px; border-radius: 8px; border: 1px dashed var(--border-color, #cbd5e1);">
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">Alasan / Keterangan</div>
                    <div style="font-size: 13px; color: var(--text-main); line-height: 1.5;">${data.alasan || '-'}</div>
                </div>
                <div class="modal-footer" style="justify-content: flex-end; padding-bottom: 0; padding-right: 0; border-top: none; margin-top: 24px;">
                    <button class="btn btn-outline" onclick="document.getElementById('modalDetailKarantina').classList.remove('active')">Tutup</button>
                </div>
            `;
            
            document.getElementById('detailKarantinaBody').innerHTML = html;
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
                        <tr style="height: 52px;">
                            <td><div>${h.id_return_supplier}</div></td>
                            <td>${new Date(h.tanggal_retur).toLocaleDateString('id-ID')}</td>
                            <td>${h.nama_supplier}</td>
                            <td>${h.nama_barang}</td>
                            <td>${h.qty_retur}</td>
                            <td>Rp ${Number(h.harga_beli).toLocaleString('id-ID')}</td>
                            <td>${h.no_invoice_supplier}</td>
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
                    
                });
            }
        });