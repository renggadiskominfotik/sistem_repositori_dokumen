<?php
include("config/koneksi.php");

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
$cleanFilename = rawurldecode($filename);

if (empty($cleanFilename)) {
    echo "Dokumen tidak ditemukan.";
    exit;
}

// Dapatkan info file dan query data dokumen dari database
$sql = "SELECT * FROM dokumen WHERE nama_file = '" . db_real_escape_string($koneksi, $cleanFilename) . "' LIMIT 1";
$query = db_query($koneksi, $sql);
$data = db_fetch_assoc($query);

$judul = $data ? $data['judul'] : $cleanFilename;
$fileUrl = get_public_file_url($cleanFilename);
$ext = strtolower(pathinfo($cleanFilename, PATHINFO_EXTENSION));

// Halaman view.php sekarang menampilkan SEMUA format dokumen secara seragam (konsisten)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul); ?> - Pratinjau Dokumen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; min-height: 100vh; }
        .viewer-card { border-radius: 16px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .docx-wrapper { background: #fff !important; padding: 2rem !important; border-radius: 8px; }
        .docx-wrapper > section.docx { box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important; margin-bottom: 2rem !important; }
        .pdf-viewer-frame { width: 100%; min-height: 82vh; height: 850px; border: none; border-radius: 8px; }
    </style>
</head>
<body class="py-3 py-md-4">
<div class="container-fluid px-3 px-md-5">
    <!-- Header Toolbar Konsisten -->
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="javascript:window.close();" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Tutup Tab
            </a>
            <div>
                <h4 class="fw-bold text-dark m-0"><?= htmlspecialchars($judul); ?></h4>
                <small class="text-muted"><i class="bi bi-file-earmark-text me-1 text-primary"></i> <?= htmlspecialchars($cleanFilename); ?></small>
            </div>
        </div>
        <div>
            <button type="button" onclick="downloadWithFolderPicker('<?= $fileUrl; ?>', '<?= htmlspecialchars($cleanFilename, ENT_QUOTES); ?>')" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-cloud-arrow-down-fill fs-6"></i>
                <span>Unduh Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Container Konten Dokumen -->
    <div class="viewer-card p-3 p-md-4">
        <div id="viewerContainer" class="rounded-3 bg-light d-flex flex-column align-items-center justify-content-center p-2" style="min-height: 750px;">
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Memuat...</span>
                </div>
                <h6 class="fw-bold text-dark mb-1">Memuat Pratinjau Dokumen <?= strtoupper($ext); ?>...</h6>
                <p class="text-muted small m-0">Sedang menyiapkan penampil dokumen.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js"></script>
<script>
const fileUrl = '<?= $fileUrl; ?>';
const fileName = '<?= htmlspecialchars($cleanFilename, ENT_QUOTES); ?>';
const ext = '<?= $ext; ?>';

// Trigger Save As Folder Picker secara konsisten
async function downloadWithFolderPicker(url, cleanFilename) {
    const downloadEndpoint = '/assets/uploads/' + encodeURIComponent(cleanFilename) + '?download=1';
    
    if ('showSaveFilePicker' in window) {
        try {
            const ext = (cleanFilename.split('.').pop() || 'pdf').toLowerCase();
            const mimeTypes = {
                'pdf': 'application/pdf',
                'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'doc': 'application/msword',
                'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls': 'application/vnd.ms-excel',
                'png': 'image/png',
                'jpg': 'image/jpeg',
                'jpeg': 'image/jpeg'
            };
            const mime = mimeTypes[ext] || 'application/octet-stream';

            const handle = await window.showSaveFilePicker({
                suggestedName: cleanFilename,
                types: [{
                    description: 'Dokumen (' + ext.toUpperCase() + ')',
                    accept: { [mime]: ['.' + ext] }
                }]
            });

            const response = await fetch(downloadEndpoint);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const blob = await response.blob();

            const writable = await handle.createWritable();
            await writable.write(blob);
            await writable.close();
            return;
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.warn('showSaveFilePicker fallback:', err);
        }
    }

    const a = document.createElement('a');
    a.href = downloadEndpoint;
    a.download = cleanFilename;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => { document.body.removeChild(a); }, 100);
}

// Viewer Renderer untuk SEMUA format file
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('viewerContainer');
    
    if (['pdf'].includes(ext)) {
        container.className = 'rounded-3 overflow-hidden bg-light p-0';
        container.innerHTML = `<iframe src="${fileUrl}#toolbar=1" class="pdf-viewer-frame"></iframe>`;
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
        container.className = 'rounded-3 bg-dark d-flex align-items-center justify-content-center p-3 text-center';
        container.innerHTML = `<img src="${fileUrl}" alt="${fileName}" class="img-fluid rounded shadow" style="max-height: 80vh; object-fit: contain;">`;
    } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
        container.className = 'rounded-3 bg-black d-flex align-items-center justify-content-center p-3';
        container.innerHTML = `<video controls class="w-100 rounded" style="max-height: 80vh;"><source src="${fileUrl}">Browser Anda tidak mendukung pemutar video.</video>`;
    } else if (['docx', 'doc'].includes(ext)) {
        fetch(fileUrl)
            .then(res => {
                if (!res.ok) throw new Error('File tidak dapat diakses');
                return res.blob();
            })
            .then(blob => {
                container.className = 'rounded-3 bg-white p-3 overflow-auto';
                container.innerHTML = '<div id="docxTarget" class="w-100"></div>';
                const docxTarget = document.getElementById('docxTarget');
                if (typeof docx !== 'undefined' && docx.renderAsync) {
                    docx.renderAsync(blob, docxTarget, null, {
                        className: "docx-wrapper",
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false
                    }).catch(err => {
                        console.error('Docx render error:', err);
                        renderFallback();
                    });
                } else {
                    renderFallback();
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                renderFallback();
            });
    } else {
        renderFallback();
    }

    function renderFallback() {
        container.className = 'rounded-3 bg-light d-flex flex-column align-items-center justify-content-center p-5 text-center';
        container.innerHTML = `
            <i class="bi bi-file-earmark-text text-primary mb-3" style="font-size: 5rem;"></i>
            <h4 class="fw-bold text-dark">Pratinjau .${ext.toUpperCase()}</h4>
            <p class="text-muted mb-4" style="max-width: 500px;">
                Format ini dapat langsung Anda unduh dan simpan ke folder komputer untuk dibuka secara lengkap.
            </p>
            <button type="button" onclick="downloadWithFolderPicker('${fileUrl}', '${fileName}')" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Unduh & Tentukan Folder
            </button>
        `;
    }
});
</script>
</body>
</html>
