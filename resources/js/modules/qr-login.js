import QRCode from 'qrcode';

// Render QR ke canvas dari sebuah URL. Dipake khusus di halaman
// "QR Login Saya", setelah token baru di-generate.
export function initQrLoginPage() {
    const canvas = document.querySelector('#qr-login-canvas');
    const url = canvas?.dataset.qrUrl;

    if (!canvas || !url) return;

    QRCode.toCanvas(canvas, url, { width: 260, margin: 2 }, (error) => {
        if (error) console.error('Gagal render QR login:', error);
    });
}