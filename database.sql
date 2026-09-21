CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`id_admin`, `nama`, `username`, `password`) VALUES
(1, 'Administrator', 'Admin', 'Admin123')
ON DUPLICATE KEY UPDATE `id_admin`=`id_admin`;

CREATE TABLE IF NOT EXISTS `dokumen` (
  `id_dokumen` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `jenis_dokumen` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `tanggal_upload` date NOT NULL,
  `id_admin` int(11) NOT NULL,
  PRIMARY KEY (`id_dokumen`),
  KEY `id_admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `dokumen` (`id_dokumen`, `judul`, `jenis_dokumen`, `deskripsi`, `nama_file`, `tanggal_upload`, `id_admin`) VALUES
(1, 'regulasi pusat 2', 'Regulasi Pusat', '', '233510352_RENGGANURARDIYANSAH_P1.pdf', '2026-08-20', 1),
(2, 'edaran kegiatan kerja', 'Surat Edaran', '', 'BIGDATA_6F_rengganurardiyansah_233510352.pdf', '2026-08-20', 1),
(3, 'peraturan daerah', 'Regulasi Pusat', '', '(111-117)+JURNAL+DANANG+-+JITTER.pdf', '2026-09-09', 1)
ON DUPLICATE KEY UPDATE `id_dokumen`=`id_dokumen`;
