<html>

<head>
	<title> EXPENSES REPORT BENSIN & TOL </title>
</head>

<body>
	<style>
		body {
			font-family: sans-serif;
		}

		table.garis {
			border-collapse: collapse;
			font-size: 0.9em;
			font-family: sans-serif;
		}
	</style>
	<table cellpadding=2 cellspacing=0 border=0 width=650>
		<tr>
			<th colspan=11>EXPENSES REPORT BENSIN & TOL</th>
		</tr>
		<tr>
			<td colspan=11>
				<table cellpadding=2 cellspacing=0 border=1 width=650 class="garis">
					<tr>
						<th rowspan=3>TGL</th>
						<th rowspan=3>Mobil</th>
						<th rowspan=3>AKTIVITAS<br />(PT.)</th>
						<th rowspan=3>RUTE</th>
						<th colspan=5>Jenis Jenis Bukti</th>
						<th rowspan=3>Total</th>
						<th rowspan=3>Ket</th>
					</tr>
					<tr>
						<th colspan=3>Bensin</th>
						<th rowspan=2 nowrap>Tol &<br />Parkir</th>
						<th rowspan=2 nowrap>Transport /<br />Lain Lain</th>
					</tr>
					<tr>
						<th>Mobil</th>
						<th>KM</th>
						<th>Jumlah</th>
					</tr>
					<?php $total_bensin = 0;
					$total_tol = 0;
					$total_parkir = 0;
					$total_kasbon = 0;
					$idd = 1;
					$total_km = 0;
					$grand_total = 0;
					$i = 0;
					$lainnya = 0;
					$gambar = '';
					if (!empty($data_detail)) {
						foreach ($data_detail as $record) {
							$i++;
							// Normalisasi nilai kosong ('' / NULL) menjadi 0 agar aman di PHP 8
							$r_bensin  = (float) $record->bensin;
							$r_tol     = (float) $record->tol;
							$r_parkir  = (float) $record->parkir;
							$r_lainnya = (float) $record->lainnya;
							$r_km      = (float) $record->km_akhir - (float) $record->km_awal;
							?>
							<tr>
								<td><?= tgl_indo($record->tgl_doc); ?></td>
								<td><?= $record->nopol; ?></td>
								<td><?= $record->keperluan; ?></td>
								<td><?= $record->rute; ?></td>
								<td align="right"><?= number_format($r_bensin); ?></td>
								<td align="right"><?= number_format($r_km); ?></td>
								<td></td>
								<td align="right"><?= number_format($r_tol + $r_parkir); ?></td>
								<td align="right"><?= number_format($r_lainnya); ?></td>
								<td align="right"><?= number_format($r_bensin + $r_tol + $r_parkir + $r_lainnya); ?></td>
								<td align="right"><?= ($record->keterangan); ?></td>
							</tr>
					<?php
							$total_bensin += $r_bensin;
							$total_tol += $r_tol;
							$total_parkir += $r_parkir;
							$total_km += $r_km;
							$lainnya += $r_lainnya;
							if ($record->doc_file != '') {
								if (strpos($record->doc_file, 'pdf', 0) > 1) {
									$gambar .= '<div class="col-md-12">
					<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
							 Presss me: <a href="' . base_url('assets/expense/' . $record->doc_file) . '">Download PDF</a>
					</iframe>
					<br />' . $record->no_doc . '</div>';
								} else {
									$gambar .= '<img src="' . base_url("assets/expense/" . $record->doc_file) . '" width="500"><br />';
								}
							}

							$idd++;
						}
					}
					$grand_total = ($total_bensin + $total_tol + $total_parkir + $lainnya);
					for ($x = 0; $x < (9 - $i); $x++) {
						echo '
		<tr>
			<td>&nbsp;</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	';
					}
					?>
				</table>
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>Mengetahui<br /><?= date("d-m-Y", strtotime(($data->fin_check_on))) ?></td>
			<td></td>
			<td align=center>Pelapor</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td align="right"><?= number_format($grand_total); ?></td>
			<td></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<?php
		$pelapor = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE username='" . $data->created_by . "'")->row();
		$mengetahui = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE username='" . $data->fin_check_by . "'")->row();
		?>
		<tr height=120>
			<td colspan=2 nowrap valign="bottom"><em>SSPM/ADM/17/Rev. 01</em></td>
			<td align=center nowrap valign="bottom"><?php
													if (!empty($mengetahui)) {
														// if ($mengetahui->ttd != '') {
														// 	echo '<img src="https://sentral.dutastudy.com/hrsentral/assets/profile/' . $mengetahui->ttd . '" height=120><br>';
														// }
													}
													?><u>&nbsp; &nbsp; <?= (($mengetahui) ? $mengetahui->name : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u></td>
			<td></td>
			<td align=center nowrap valign="bottom"><?php
													// if ($pelapor->ttd != '') {
													// 	echo '<img src="https://sentral.dutastudy.com/hrsentral/assets/profile/' . $pelapor->ttd . '" height=120><br>';
													// }
													?><u>&nbsp; &nbsp; <?= (($pelapor) ? $pelapor->name : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>

	<br />
	<?= $gambar ?>
</body>

</html>