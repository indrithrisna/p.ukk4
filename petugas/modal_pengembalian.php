<!-- Modal Pengembalian untuk ID: 
 <?php echo $row['id']; ?> -->
<div class="modal fade" id="pengembalianModal<?php echo $row['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Proses Pengembalian #<?php echo $row['id']; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="peminjaman_id" value="<?php echo $row['id']; ?>">
                    
                    <h6>Informasi Peminjam</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <td width="30%"><strong>Nama</strong></td>
                            <td><?php echo $row['nama']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Pinjam</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Kembali</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Biaya Sewa</strong></td>
                            <td>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                    
                    <h6 class="mt-3">Alat yang Dipinjam</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Alat</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $detail_q = "SELECT dp.*, a.nama_alat FROM detail_peminjaman dp
                                        JOIN alat a ON dp.alat_id = a.id
                                        WHERE dp.peminjaman_id = {$row['id']}";
                            $detail_r = mysqli_query($conn, $detail_q);
                            while ($d = mysqli_fetch_assoc($detail_r)):
                            ?>
                            <tr>
                                <td><?php echo $d['nama_alat']; ?></td>
                                <td><?php echo $d['jumlah']; ?> unit</td>
                                <td>Rp <?php echo number_format($d['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <hr>
                    
                    <h6>Kondisi Pengembalian</h6>
                    <div class="mb-3">
                        <label class="form-label">Kondisi Alat <span class="text-danger">*</span></label>
                        <select name="kondisi_pengembalian" class="form-control" id="kondisi<?php echo $row['id']; ?>" required onchange="hitungDenda<?php echo $row['id']; ?>()">
                            <option value="baik">Baik - Tidak ada kerusakan</option>
                            <option value="rusak ringan">Rusak Ringan - Kerusakan kecil (Denda: Rp <?php echo number_format($pengaturan['denda_rusak_ringan'], 0, ',', '.'); ?>)</option>
                            <option value="rusak berat">Rusak Berat - Kerusakan parah (Denda: Rp <?php echo number_format($pengaturan['denda_rusak_berat'], 0, ',', '.'); ?>)</option>
                            <option value="hilang">Hilang - Alat tidak dikembalikan (Denda: <?php echo $pengaturan['denda_hilang_persen']; ?>% dari harga)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Pengembalian</label>
                        <textarea name="catatan_pengembalian" class="form-control" rows="3" placeholder="Catatan kondisi alat atau keterangan lainnya..."></textarea>
                    </div>
                    
                    <hr>
                    
                    <h6>Perhitungan Denda</h6>
                    <table class="table table-sm">
                        <?php
                        $hari_terlambat = max(0, (strtotime(date('Y-m-d')) - strtotime($row['tanggal_kembali'])) / (60 * 60 * 24));
                        $denda_keterlambatan = $hari_terlambat * $pengaturan['denda_per_hari'];
                        ?>
                        <tr>
                            <td width="60%">Denda Keterlambatan (<?php echo $hari_terlambat; ?> hari × Rp <?php echo number_format($pengaturan['denda_per_hari'], 0, ',', '.'); ?>)</td>
                            <td class="text-end">
                                <strong class="text-danger">Rp <?php echo number_format($denda_keterlambatan, 0, ',', '.'); ?></strong>
                                <input type="hidden" name="denda_keterlambatan" value="<?php echo $denda_keterlambatan; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>Denda Kondisi Alat</td>
                            <td class="text-end">
                                <strong class="text-danger" id="dendaKondisi<?php echo $row['id']; ?>">Rp 0</strong>
                                <input type="hidden" name="denda_kondisi" id="dendaKondisiInput<?php echo $row['id']; ?>" value="0">
                            </td>
                        </tr>
                        <tr class="table-warning">
                            <td><strong>Total Denda</strong></td>
                            <td class="text-end">
                                <h5 class="text-danger mb-0" id="totalDenda<?php echo $row['id']; ?>">Rp <?php echo number_format($denda_keterlambatan, 0, ',', '.'); ?></h5>
                            </td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Total yang Harus Dibayar</strong></td>
                            <td class="text-end">
                                <h5 class="text-primary mb-0" id="grandTotal<?php echo $row['id']; ?>">Rp <?php echo number_format($row['total_biaya'] + $denda_keterlambatan, 0, ',', '.'); ?></h5>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_pengembalian" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Proses Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function hitungDenda<?php echo $row['id']; ?>() {
    const kondisi = document.getElementById('kondisi<?php echo $row['id']; ?>').value;
    const dendaKeterlambatan = <?php echo $denda_keterlambatan; ?>;
    const totalBiaya = <?php echo $row['total_biaya']; ?>;
    let dendaKondisi = 0;
    
    if (kondisi === 'rusak ringan') {
        dendaKondisi = <?php echo $pengaturan['denda_rusak_ringan']; ?>;
    } else if (kondisi === 'rusak berat') {
        dendaKondisi = <?php echo $pengaturan['denda_rusak_berat']; ?>;
    } else if (kondisi === 'hilang') {
        dendaKondisi = totalBiaya * (<?php echo $pengaturan['denda_hilang_persen']; ?> / 100);
    }
    
    const totalDenda = dendaKeterlambatan + dendaKondisi;
    const grandTotal = totalBiaya + totalDenda;
    
    document.getElementById('dendaKondisi<?php echo $row['id']; ?>').textContent = 'Rp ' + dendaKondisi.toLocaleString('id-ID');
    document.getElementById('dendaKondisiInput<?php echo $row['id']; ?>').value = dendaKondisi;
    document.getElementById('totalDenda<?php echo $row['id']; ?>').textContent = 'Rp ' + totalDenda.toLocaleString('id-ID');
    document.getElementById('grandTotal<?php echo $row['id']; ?>').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
}
</script>
