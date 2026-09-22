-- SCRIPT DATABASE UNTUK SUPABASE (POSTGRESQL)

CREATE TABLE IF NOT EXISTS admin (
  id_admin SERIAL PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL
);

INSERT INTO admin (id_admin, nama, username, password) 
VALUES (1, 'Administrator', 'Admin', 'Admin123')
ON CONFLICT (id_admin) DO NOTHING;

CREATE TABLE IF NOT EXISTS dokumen (
  id_dokumen SERIAL PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  jenis_dokumen VARCHAR(100) NOT NULL,
  deskripsi TEXT NOT NULL,
  nama_file VARCHAR(255) NOT NULL,
  tanggal_upload DATE NOT NULL,
  id_admin INT NOT NULL REFERENCES admin(id_admin)
);

INSERT INTO dokumen (id_dokumen, judul, jenis_dokumen, deskripsi, nama_file, tanggal_upload, id_admin) VALUES
(1, 'regulasi pusat 2', 'Regulasi Pusat', '', '233510352_RENGGANURARDIYANSAH_P1.pdf', '2026-08-20', 1),
(2, 'edaran kegiatan kerja', 'Surat Edaran', '', 'BIGDATA_6F_rengganurardiyansah_233510352.pdf', '2026-08-20', 1),
(3, 'peraturan daerah', 'Regulasi Pusat', '', '(111-117)+JURNAL+DANANG+-+JITTER.pdf', '2026-09-09', 1)
ON CONFLICT (id_dokumen) DO NOTHING;

-- SINKRONISASI SEQUENCE ID UNTUK AUTO-INCREMENT
SELECT setval(pg_get_serial_sequence('admin', 'id_admin'), COALESCE(max(id_admin), 1)) FROM admin;
SELECT setval(pg_get_serial_sequence('dokumen', 'id_dokumen'), COALESCE(max(id_dokumen), 1)) FROM dokumen;

-- BUKA IZIN UPLOAD & AKSES FILE SUPABASE STORAGE UNTUK BUCKET 'dokumen'
INSERT INTO storage.buckets (id, name, public) 
VALUES ('dokumen', 'dokumen', true) 
ON CONFLICT (id) DO UPDATE SET public = true;

DROP POLICY IF EXISTS "Allow Public Select" ON storage.objects;
DROP POLICY IF EXISTS "Allow Public Insert" ON storage.objects;
DROP POLICY IF EXISTS "Allow Public Update" ON storage.objects;
DROP POLICY IF EXISTS "Allow Public Delete" ON storage.objects;

CREATE POLICY "Allow Public Select" ON storage.objects FOR SELECT USING (bucket_id = 'dokumen');
CREATE POLICY "Allow Public Insert" ON storage.objects FOR INSERT WITH CHECK (bucket_id = 'dokumen');
CREATE POLICY "Allow Public Update" ON storage.objects FOR UPDATE USING (bucket_id = 'dokumen');
CREATE POLICY "Allow Public Delete" ON storage.objects FOR DELETE USING (bucket_id = 'dokumen');
