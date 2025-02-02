<?php
header('Content-type: application/json');

function cekResi($resi)
{
    $date = floor(time());
    $url = 'https://spx.co.id/api/v2/fleet_order/tracking/search?sls_tracking_number=' . $resi . '|' . $date . '' . hash('sha256', $resi . '' . $date . 'MGViZmZmZTYzZDJhNDgxY2Y1N2ZlN2Q1ZWJkYzlmZDY=');
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);

    return json_decode($resp, true);
}

// Fungsi untuk menerjemahkan status dan pesan ke Bahasa Indonesia
function terjemahkanStatus($status, $message)
{
    $messageID = str_replace(
        ['Your parcel', 'has been delivered', 'is being delivered by courier', 'has been received by delivery hub', 'Parcel', 'has been received by sorting center', 'has been received by pickup hub', 'has been picked up', 'Order has been created', 'Family member', 'has been received by drop off point', 'is on-hold', 'Insufficient time', 'has been returned', 'is being returned to seller', 'is being returned to the sorting center', 'Recipient reject'],
        ['Paket anda', 'telah diterima', 'sedang dikirim oleh kurir', 'telah diterima oleh hub pengiriman', 'Paket', 'telah diterima oleh pusat penyortiran', 'telah diterima Pick-up Hub', 'telah dijemput kurir', 'Pesanan telah dibuat', 'Anggota keluarga', 'telah diterima oleh drop off point', 'ditunda', 'Waktu pengiriman tidak cukup', 'telah dikembalikan', 'akan dikembalikan ke pengirim', 'sedang dikembalikan ke pusat penyortiran', 'Penerima menolak paket'],
        $message
    );

    return [$messageID];
}

$data = cekResi($_GET['resi']);

if ($data['retcode'] === 0) {
    // Ambil status saat ini
    $currentStatus = strtolower($data['data']['current_status']);

    // Tentukan warna teks berdasarkan status
    $statusClass = in_array($currentStatus, ['delivered', 'completed', 'success']) ? 'text-success' : 'text-danger';

    $output = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Tracking Resi</title>
            
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="container">
        ';

    $output .= '<style>li { margin-bottom: 10px; }</style>';
    $output .= '<h6 class="text-center">Nomor Resi: ' . htmlspecialchars($data['data']['sls_tracking_number']) . '</h6>';

    // Tambahkan warna pada current status
    $output .= '<h6 class="mb-4 text-center ' . $statusClass . '">' . htmlspecialchars($data['data']['current_status']) . '</h6>';

    $output .= '<ul class="list-unstyled">';

    foreach ($data['data']['tracking_list'] as $item) {
        list($statusID, $messageID) = terjemahkanStatus($item['status'], $item['message']);

        $output .= '<li>';
        $output .= '<div class="row g-0 justify-content-end">';

        // Kolom pertama: Waktu dan tanggal
        $output .= '<div class="col-4 col-md-2 text-md-center">';
        $output .= '<label style="font-size: 15px;">' . date('d/m/Y', $item['timestamp']) . '</label></br>';
        $output .= '<label style="font-size: 14px; margin-right: 22px;">' . date('H:i:s', $item['timestamp']) . '</label>';
        $output .= '</div>';

        // Kolom kedua: Status dan pesan dalam Bahasa Indonesia
        $output .= '<div class="col-8 col-md-8">';
        $output .= '<p class="msg">' . htmlspecialchars($statusID) . ' ' . htmlspecialchars($messageID) . '</p>';
        $output .= '</div>';

        $output .= '</div>'; // Tutup div row
        $output .= '</li>';
    }

    $output .= '</ul>';
    echo json_encode(['html' => $output]);
} else {
    echo json_encode(['html' => '<div class="alert alert-danger">' . htmlspecialchars($data['message']) . '</div>']);
}
