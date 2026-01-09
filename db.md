## 1. Tabel Wilayah Kabupaten

 
CREATE TABLE wilayah_kabupaten (
    kode_kabupaten CHAR(4) PRIMARY KEY,
    nama_kabupaten VARCHAR2(100) NOT NULL
);
 

 

## 2. Tabel Wilayah Kecamatan

 
CREATE TABLE wilayah_kecamatan (
    kode_kecamatan CHAR(6) PRIMARY KEY,
    kode_kabupaten CHAR(4) NOT NULL,
    nama_kecamatan VARCHAR2(100) NOT NULL,
    CONSTRAINT fk_kecamatan_kabupaten
        FOREIGN KEY (kode_kabupaten)
        REFERENCES wilayah_kabupaten(kode_kabupaten)
);
 

 

## 3. Tabel Wilayah Desa

 
CREATE TABLE wilayah_desa (
    kode_desa CHAR(10) PRIMARY KEY,
    kode_kecamatan CHAR(6) NOT NULL,
    nama_desa VARCHAR2(100) NOT NULL,
    nama_kepala_desa VARCHAR2(100),
    titik_koordinat VARCHAR2(50),
    kontur_wilayah VARCHAR2(50),
    luas_wilayah NUMBER(10,2),
    jarak_disdukcapil NUMBER(10,2),
    jumlah_rt NUMBER,
    jumlah_rw NUMBER,
    jumlah_dusun NUMBER,
    CONSTRAINT fk_desa_kecamatan
        FOREIGN KEY (kode_kecamatan)
        REFERENCES wilayah_kecamatan(kode_kecamatan)
);
 

 

## 4. Tabel Pendamping

 
CREATE TABLE pendamping (
    nik CHAR(16) PRIMARY KEY,
    kode_desa CHAR(10) UNIQUE NOT NULL,
    nama VARCHAR2(100) NOT NULL,
    nomor_ponsel VARCHAR2(20),
    jenis_kelamin CHAR(1)
        CHECK (jenis_kelamin IN ('L','P','-')),
    status_aktif VARCHAR2(10) DEFAULT 'Aktif'
        CHECK (status_aktif IN ('Aktif','Nonaktif')),

    password VARCHAR2(255) NOT NULL,
    akses VARCHAR2(10) DEFAULT 'Operator'
        CHECK (akses IN ('Admin','Operator')),

    CONSTRAINT fk_pendamping_desa
        FOREIGN KEY (kode_desa)
        REFERENCES wilayah_desa(kode_desa)
        ON DELETE CASCADE
);
 

 

## 5. Tabel Petugas Desa

> **Catatan:** Tabel ini hanya untuk **petugas desa**. `level_akses` disimpan **manual tanpa constraint**.

CREATE TABLE petugas (
nik CHAR(16) PRIMARY KEY,
nama VARCHAR2(100) NOT NULL,
nomor_ponsel VARCHAR2(20),
jenis_kelamin CHAR(1)
CHECK (jenis_kelamin IN ('L','P','-')),
level_akses VARCHAR2(20),
status_aktif VARCHAR2(10) DEFAULT 'Aktif'
CHECK (status_aktif IN ('Aktif','Nonaktif')),
keterangan_nonaktif CLOB,
tanggal_mulai_aktif VARCHAR2(20),


kode_desa CHAR(10) NOT NULL,
kode_kecamatan CHAR(6) NOT NULL,
kode_kabupaten CHAR(4) NOT NULL,


CONSTRAINT fk_petugas_desa
FOREIGN KEY (kode_desa)
REFERENCES wilayah_desa(kode_desa),


CONSTRAINT fk_petugas_kecamatan
FOREIGN KEY (kode_kecamatan)
REFERENCES wilayah_kecamatan(kode_kecamatan),


CONSTRAINT fk_petugas_kabupaten
FOREIGN KEY (kode_kabupaten)
REFERENCES wilayah_kabupaten(kode_kabupaten)
);

 

## 6. Tabel Petugas Kecamatan

> **Catatan:** Tabel khusus **petugas kecamatan** (tanpa `level_akses`).

 
CREATE TABLE petugas_kecamatan (
    nik CHAR(16) PRIMARY KEY,
    nama VARCHAR2(100) NOT NULL,
    nomor_ponsel VARCHAR2(20),
    jenis_kelamin CHAR(1)
        CHECK (jenis_kelamin IN ('L','P','-')),
    status_aktif VARCHAR2(10) DEFAULT 'Aktif'
        CHECK (status_aktif IN ('Aktif','Nonaktif')),
    tanggal_mulai_akses DATE,
    bcard VARCHAR2(50),
    benroller VARCHAR2(50),
    kode_kecamatan CHAR(6) NOT NULL,

    CONSTRAINT fk_petugas_kecamatan
        FOREIGN KEY (kode_kecamatan)
        REFERENCES wilayah_kecamatan(kode_kecamatan)
);
 

 

## 7. Tabel Petugas Dinas

 
CREATE TABLE petugas_dinas (
    nik CHAR(16) PRIMARY KEY,
    nama VARCHAR2(100) NOT NULL,
    nomor_ponsel VARCHAR2(20),
    jenis_kelamin CHAR(1)
        CHECK (jenis_kelamin IN ('L','P','-')),
    status_aktif VARCHAR2(10) DEFAULT 'Aktif'
        CHECK (status_aktif IN ('Aktif','Nonaktif')),
    tanggal_mulai_akses DATE,
    bcard VARCHAR2(50),
    benroller VARCHAR2(50),
    kode_kabupaten CHAR(4) NOT NULL,

    CONSTRAINT fk_petugas_dinas
        FOREIGN KEY (kode_kabupaten)
        REFERENCES wilayah_kabupaten(kode_kabupaten)
);
 

 

## 8. Tabel Sarpras Desa

 
CREATE TABLE sarpras_desa (
    id NUMBER PRIMARY KEY,
    kode_desa CHAR(10) NOT NULL,
    komputer NUMBER DEFAULT 0,
    printer NUMBER DEFAULT 0,
    internet NUMBER DEFAULT 0,
    ruang_pelayanan VARCHAR2(10) DEFAULT 'Tidak'
        CHECK (ruang_pelayanan IN ('Ada','Tidak')),
    provider VARCHAR2(100),

    CONSTRAINT fk_sarpras_desa
        FOREIGN KEY (kode_desa)
        REFERENCES wilayah_desa(kode_desa)
        ON DELETE CASCADE
);

CREATE SEQUENCE sarpras_desa_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER trg_sarpras_desa_ai
BEFORE INSERT ON sarpras_desa
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT sarpras_desa_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/
 

 

## 9. Tabel VPN Desa

 
CREATE TABLE vpn_desa (
    id NUMBER PRIMARY KEY,
    kode_desa CHAR(10) UNIQUE NOT NULL,
    username VARCHAR2(150) NOT NULL,
    password VARCHAR2(255) NOT NULL,
    jenis_vpn VARCHAR2(20)
        CHECK (jenis_vpn IN ('PPTP','L2TP','OpenVPN','WireGuard')),

    CONSTRAINT fk_vpn_desa
        FOREIGN KEY (kode_desa)
        REFERENCES wilayah_desa(kode_desa)
        ON DELETE CASCADE
);

CREATE SEQUENCE vpn_desa_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER trg_vpn_desa_ai
BEFORE INSERT ON vpn_desa
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT vpn_desa_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/
 

 

## 10. Tabel Kinerja Petugas

 
CREATE TABLE kinerja_petugas (
    id NUMBER PRIMARY KEY,
    nik_petugas CHAR(16) NOT NULL,
    kode_desa CHAR(10) NOT NULL,
    tahun NUMBER NOT NULL,
    bulan NUMBER NOT NULL,
    aktivasi_ikd NUMBER DEFAULT 0,
    ikd_desa NUMBER DEFAULT 0,
    akta_kelahiran NUMBER DEFAULT 0,
    akta_kematian NUMBER DEFAULT 0,
    pengajuan_kk NUMBER DEFAULT 0,
    pengajuan_pindah NUMBER DEFAULT 0,
    pengajuan_kia NUMBER DEFAULT 0,
    jumlah_login NUMBER DEFAULT 0,

    CONSTRAINT fk_kinerja_petugas
        FOREIGN KEY (nik_petugas) REFERENCES petugas(nik)
        ON DELETE CASCADE,
    CONSTRAINT fk_kinerja_desa
        FOREIGN KEY (kode_desa) REFERENCES wilayah_desa(kode_desa)
        ON DELETE CASCADE,
    CONSTRAINT uq_kinerja UNIQUE (nik_petugas, kode_desa, tahun, bulan)
);

CREATE SEQUENCE kinerja_petugas_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER trg_kinerja_petugas_ai
BEFORE INSERT ON kinerja_petugas
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT kinerja_petugas_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/
 

 

## 11. Tabel Kependudukan Semester

 
CREATE TABLE kependudukan_semester (
    id NUMBER PRIMARY KEY,
    kode_desa CHAR(10) NOT NULL,
    kode_semester CHAR(6) NOT NULL,
    jumlah_penduduk NUMBER,
    jumlah_laki NUMBER,
    jumlah_perempuan NUMBER,
    wajib_ktp NUMBER,
    kartu_keluarga NUMBER,
    akta_kelahiran_jml NUMBER,
    akta_kelahiran_persen NUMBER(5,2),
    akta_kematian_jml NUMBER,
    akta_kematian_persen NUMBER(5,2),
    kepemilikan_ktp_jml NUMBER,
    kepemilikan_ktp_persen NUMBER(5,2),
    kepemilikan_kia_jml NUMBER,
    kepemilikan_kia_persen NUMBER(5,2),
    jumlah_kematian NUMBER,
    pindah_keluar NUMBER,
    status_kawin_jml NUMBER,
    status_kawin_persen NUMBER(5,2),

    CONSTRAINT fk_kependudukan_desa
        FOREIGN KEY (kode_desa)
        REFERENCES wilayah_desa(kode_desa)
        ON DELETE CASCADE,
    CONSTRAINT uq_kependudukan UNIQUE (kode_desa, kode_semester)
);

CREATE SEQUENCE kependudukan_semester_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER trg_kependudukan_semester_ai
BEFORE INSERT ON kependudukan_semester
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT kependudukan_semester_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/
 

 

## 12. Tabel Header Pelayanan

 
CREATE TABLE header_pelayanan (
    id NUMBER PRIMARY KEY,
    nomor_pelayanan VARCHAR2(50),
    nomor_pengaduan VARCHAR2(50),
    tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE SEQUENCE header_pelayanan_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER trg_header_pelayanan_ai
BEFORE INSERT ON header_pelayanan
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT header_pelayanan_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/
