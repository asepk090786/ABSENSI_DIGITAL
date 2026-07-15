/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: simadis
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `absensi_guru`
--

DROP TABLE IF EXISTS `absensi_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi_guru` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned NOT NULL,
  `pencatat_guru_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('hadir','tidak_hadir','izin','sakit') NOT NULL,
  `keterangan` varchar(191) DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `absensi_guru_guru_id_tanggal_unique` (`guru_id`,`tanggal`),
  KEY `absensi_guru_pencatat_guru_id_foreign` (`pencatat_guru_id`),
  KEY `absensi_guru_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `absensi_guru_semester_id_foreign` (`semester_id`),
  KEY `absensi_guru_tanggal_index` (`tanggal`),
  KEY `absensi_guru_status_index` (`status`),
  CONSTRAINT `absensi_guru_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `absensi_guru_pencatat_guru_id_foreign` FOREIGN KEY (`pencatat_guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  CONSTRAINT `absensi_guru_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE SET NULL,
  CONSTRAINT `absensi_guru_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_guru`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `absensi_guru` WRITE;
/*!40000 ALTER TABLE `absensi_guru` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi_guru` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `absensi_kelas`
--

DROP TABLE IF EXISTS `absensi_kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi_kelas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `guru_id` bigint(20) unsigned NOT NULL,
  `jam_belajar_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `status_kelas` varchar(191) DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absensi_kelas_kelas_id_foreign` (`kelas_id`),
  KEY `absensi_kelas_guru_id_foreign` (`guru_id`),
  KEY `absensi_kelas_jam_belajar_id_foreign` (`jam_belajar_id`),
  KEY `absensi_kelas_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `absensi_kelas_semester_id_foreign` (`semester_id`),
  CONSTRAINT `absensi_kelas_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `absensi_kelas_jam_belajar_id_foreign` FOREIGN KEY (`jam_belajar_id`) REFERENCES `jam_belajar` (`id`),
  CONSTRAINT `absensi_kelas_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  CONSTRAINT `absensi_kelas_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `absensi_kelas_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_kelas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `absensi_kelas` WRITE;
/*!40000 ALTER TABLE `absensi_kelas` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi_kelas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `absensi_siswa`
--

DROP TABLE IF EXISTS `absensi_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi_siswa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `absensi_kelas_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `status` varchar(191) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absensi_siswa_absensi_kelas_id_foreign` (`absensi_kelas_id`),
  KEY `absensi_siswa_siswa_id_foreign` (`siswa_id`),
  CONSTRAINT `absensi_siswa_absensi_kelas_id_foreign` FOREIGN KEY (`absensi_kelas_id`) REFERENCES `absensi_kelas` (`id`),
  CONSTRAINT `absensi_siswa_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_siswa`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `absensi_siswa` WRITE;
/*!40000 ALTER TABLE `absensi_siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi_siswa` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `agenda_guru`
--

DROP TABLE IF EXISTS `agenda_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda_guru` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned NOT NULL,
  `jam_belajar_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agenda_guru_guru_id_foreign` (`guru_id`),
  KEY `agenda_guru_jam_belajar_id_foreign` (`jam_belajar_id`),
  KEY `agenda_guru_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `agenda_guru_semester_id_foreign` (`semester_id`),
  CONSTRAINT `agenda_guru_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `agenda_guru_jam_belajar_id_foreign` FOREIGN KEY (`jam_belajar_id`) REFERENCES `jam_belajar` (`id`),
  CONSTRAINT `agenda_guru_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `agenda_guru_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agenda_guru`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `agenda_guru` WRITE;
/*!40000 ALTER TABLE `agenda_guru` DISABLE KEYS */;
/*!40000 ALTER TABLE `agenda_guru` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `agenda_kelas`
--

DROP TABLE IF EXISTS `agenda_kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda_kelas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned DEFAULT NULL,
  `guru_id` bigint(20) unsigned NOT NULL,
  `jenis_kegiatan` varchar(191) NOT NULL DEFAULT 'kbm',
  `jam_belajar_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text DEFAULT NULL,
  `nama_kegiatan` varchar(191) DEFAULT NULL,
  `tujuan_pembelajaran` text DEFAULT NULL,
  `strategi_pembelajaran` text DEFAULT NULL,
  `media_pembelajaran` text DEFAULT NULL,
  `sumber_belajar` text DEFAULT NULL,
  `penilaian` text DEFAULT NULL,
  `catatan_tambahan` text DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agenda_kelas_kelas_id_foreign` (`kelas_id`),
  KEY `agenda_kelas_guru_id_foreign` (`guru_id`),
  KEY `agenda_kelas_jam_belajar_id_foreign` (`jam_belajar_id`),
  KEY `agenda_kelas_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `agenda_kelas_semester_id_foreign` (`semester_id`),
  CONSTRAINT `agenda_kelas_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `agenda_kelas_jam_belajar_id_foreign` FOREIGN KEY (`jam_belajar_id`) REFERENCES `jam_belajar` (`id`),
  CONSTRAINT `agenda_kelas_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  CONSTRAINT `agenda_kelas_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `agenda_kelas_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agenda_kelas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `agenda_kelas` WRITE;
/*!40000 ALTER TABLE `agenda_kelas` DISABLE KEYS */;
/*!40000 ALTER TABLE `agenda_kelas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `agenda_piket`
--

DROP TABLE IF EXISTS `agenda_piket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda_piket` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned NOT NULL,
  `jam_belajar_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agenda_piket_guru_id_foreign` (`guru_id`),
  KEY `agenda_piket_jam_belajar_id_foreign` (`jam_belajar_id`),
  KEY `agenda_piket_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `agenda_piket_semester_id_foreign` (`semester_id`),
  CONSTRAINT `agenda_piket_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `agenda_piket_jam_belajar_id_foreign` FOREIGN KEY (`jam_belajar_id`) REFERENCES `jam_belajar` (`id`),
  CONSTRAINT `agenda_piket_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `agenda_piket_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agenda_piket`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `agenda_piket` WRITE;
/*!40000 ALTER TABLE `agenda_piket` DISABLE KEYS */;
/*!40000 ALTER TABLE `agenda_piket` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `capaian_pembelajarans`
--

DROP TABLE IF EXISTS `capaian_pembelajarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `capaian_pembelajarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_capaian_pembelajaran` varchar(191) NOT NULL,
  `deskripsi` longtext DEFAULT NULL,
  `tujuan_pembelajaran` longtext DEFAULT NULL,
  `alur_tujuan_pembelajaran` longtext DEFAULT NULL,
  `indikator_kriteria` longtext DEFAULT NULL,
  `fase` varchar(191) DEFAULT NULL COMMENT 'Fase pembelajaran (A, B, C, D, E, F)',
  `tahun_ajaran_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `capaian_pembelajarans_nama_capaian_pembelajaran_unique` (`nama_capaian_pembelajaran`),
  KEY `capaian_pembelajarans_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `capaian_pembelajarans_user_id_foreign` (`user_id`),
  CONSTRAINT `capaian_pembelajarans_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `capaian_pembelajarans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `capaian_pembelajarans`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `capaian_pembelajarans` WRITE;
/*!40000 ALTER TABLE `capaian_pembelajarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `capaian_pembelajarans` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `ekstrakurikuler`
--

DROP TABLE IF EXISTS `ekstrakurikuler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekstrakurikuler` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_ekskul` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `pembina_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekstrakurikuler_pembina_id_index` (`pembina_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekstrakurikuler`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `ekstrakurikuler` WRITE;
/*!40000 ALTER TABLE `ekstrakurikuler` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekstrakurikuler` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `guru`
--

DROP TABLE IF EXISTS `guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned DEFAULT NULL,
  `nama` varchar(191) NOT NULL,
  `nip` varchar(191) DEFAULT NULL,
  `jenis_tugas_wakil` varchar(191) DEFAULT NULL,
  `hari_piket` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hari_piket`)),
  `pangkat_golongan` varchar(191) DEFAULT NULL,
  `kode_guru` varchar(191) DEFAULT NULL,
  `username` varchar(191) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `telepon` varchar(191) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guru_guru_id_foreign` (`guru_id`),
  CONSTRAINT `guru_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `guru` WRITE;
/*!40000 ALTER TABLE `guru` DISABLE KEYS */;
INSERT INTO `guru` VALUES
(2,NULL,'Drs.H. Asep Sopiyandi ,M.Pd','196705041994031016',NULL,NULL,'-','2',NULL,NULL,'guru2@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 18:54:29'),
(3,NULL,'Drs. Hasan Basri ,M.Pd','196909131994121002',NULL,NULL,'-','3',NULL,NULL,'guru3@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 18:54:28'),
(4,NULL,'Dra.Hj.Isnaini Nasuka R,M.Pd','196807281996012001',NULL,NULL,'-','5',NULL,NULL,'guru5@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 18:54:28'),
(5,NULL,'Iri Setiawan','197001141995121001',NULL,NULL,'-','4',NULL,NULL,'guru4@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 18:54:30'),
(6,NULL,'H. Mulyadi,S.Pd,MM','196706022006041002',NULL,NULL,'-','6',NULL,NULL,'guru6@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:29'),
(7,NULL,'Ahmad Taufik Halaili','197311062006041004',NULL,NULL,'-','7',NULL,NULL,'guru7@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 18:54:27'),
(8,NULL,'Rani Laetamani, S.Pd','197403252006042002',NULL,NULL,'-','8',NULL,NULL,'guru8@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:32'),
(9,NULL,'Drs. H. Hawar','196809052008011006',NULL,NULL,'-','9',NULL,NULL,'guru9@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:28'),
(10,NULL,'H. Kasir, S.Pd','197004152008011006',NULL,NULL,'-','10',NULL,NULL,'guru10@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:29'),
(11,NULL,'Yayad Ginting, S.IP','197102252008011007',NULL,NULL,'-','11',NULL,NULL,'guru11@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:32'),
(12,NULL,'Hj. Nur Emiliyah, SE','197902162008012012',NULL,NULL,'-','12',NULL,NULL,'guru12@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:30'),
(13,NULL,'Naziyah,S.Pd,M.Pd','197203072008012007',NULL,NULL,'-','13',NULL,NULL,'guru13@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:31'),
(14,NULL,'Arifuddin, S.Pd','198008142009021002',NULL,NULL,'-','14',NULL,NULL,'guru14@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:28'),
(15,NULL,'Elis MukhlisAh, S.Pd','198206302009022003',NULL,NULL,'-','15',NULL,NULL,'guru15@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:29'),
(16,NULL,'Mukhlish,ST,M.Pd','197803182010011013',NULL,NULL,'-','16',NULL,NULL,'guru16@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:31'),
(17,NULL,'Hj. Maria Ulfah, S.Psi','198102212010012003',NULL,NULL,'-','17',NULL,NULL,'guru17@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:30'),
(18,NULL,'Asep Kurniawan','198607092010011005',NULL,NULL,'-','18',NULL,NULL,'guru18@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:17','2026-07-14 07:54:31'),
(19,NULL,'Herni Wahyuni,S.Pd','198410132011012001',NULL,NULL,'-','19',NULL,NULL,'guru19@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:17','2026-07-14 18:54:30'),
(20,NULL,'Ihsana Romadlon','198406122011011001',NULL,NULL,'-','20',NULL,NULL,'guru20@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(21,NULL,'Enih Sulastri,S.Pd','198508102010012005',NULL,NULL,'-','21',NULL,NULL,'guru21@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(22,NULL,'Neng Astry Mediana,S.Pd','198905162019032014',NULL,NULL,'-','22',NULL,NULL,'guru22@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(23,NULL,'Iis Khaerunisah,S.Pd','199409272020122018',NULL,NULL,'-','23',NULL,NULL,'guru23@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(24,NULL,'Prayogo, S.Kom','198101012022211017',NULL,NULL,'-','24',NULL,NULL,'guru24@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(25,NULL,'Safili,S.Pd','198408022022211016',NULL,NULL,'-','25',NULL,NULL,'guru25@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(26,NULL,'Sri Zuniar Ningsih,S.H','196906102023212005',NULL,NULL,'-','26',NULL,NULL,'guru26@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(27,NULL,'Fikri Burhani','198310182022211004',NULL,NULL,'-','27',NULL,NULL,'guru27@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(28,NULL,'Nur Kholifah,S.Pd,M.Pd','198710272024212027',NULL,NULL,'-','28',NULL,NULL,'guru28@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(29,NULL,'Widowati, S.Pd','197103082024212002',NULL,NULL,'-','29',NULL,NULL,'guru29@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(30,NULL,'Rahmatullah,S.Pd','198505012024211007',NULL,NULL,'-','30',NULL,NULL,'guru30@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(31,NULL,'Nurjannah Triastuti Rahajeng, S.Hi','198112012025212024',NULL,NULL,'-','31',NULL,NULL,'guru31@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(32,NULL,'Iha Musliha, S.Sos','198309172025212026',NULL,NULL,'-','32',NULL,NULL,'guru32@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(33,NULL,'Nurul Anwar,S.Pd','198405062025211024',NULL,NULL,'-','33',NULL,NULL,'guru33@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(34,NULL,'Anisatul Hatroh,S.Pd','198608292025212035',NULL,NULL,'-','34',NULL,NULL,'guru34@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:28'),
(35,NULL,'Rokilah, S.Hum','198706072025212046',NULL,NULL,'-','35',NULL,NULL,'guru35@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(36,NULL,'Rahmat Qurniawan, SE','198706052025211060',NULL,NULL,'-','36',NULL,NULL,'guru36@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(37,NULL,'Ali Sudirman,S.Pd','198812152025211038',NULL,NULL,'-','37',NULL,NULL,'guru37@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:28'),
(38,NULL,'Mukhlisin, SE','199106062025211060',NULL,NULL,'-','38',NULL,NULL,'guru38@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(39,NULL,'Patmawati,S.Pd','199210122025212047',NULL,NULL,'-','39',NULL,NULL,'guru39@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(40,NULL,'Fahrudin, S.Pd','199207252025211032',NULL,NULL,'-','40',NULL,NULL,'guru40@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(41,NULL,'Aulia Rahmawati,S.Pd','199209282025212040',NULL,NULL,'-','41',NULL,NULL,'guru41@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:28'),
(42,NULL,'Fina Faelasufatunnajah,M.Pd','199604152025212037',NULL,NULL,'-','42',NULL,NULL,'guru42@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(43,NULL,'Yola Robihatul Azhar,S.Pd','199607042025212039',NULL,NULL,'-','43',NULL,NULL,'guru43@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(44,NULL,'Eva Muzdalifah,S.Psi','199704132025212022',NULL,NULL,'-','44',NULL,NULL,'guru44@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(45,NULL,'Dyah Kartika Sari','199404212025212100',NULL,NULL,'-','45',NULL,NULL,'guru45@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:29'),
(46,NULL,'Nur Fairuz Fatin','199711272025212048',NULL,NULL,'-','46',NULL,NULL,'guru46@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:31'),
(47,NULL,'Lutfah, ST','197607272025212021',NULL,NULL,'-','47',NULL,NULL,'guru47@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(48,NULL,'M Fatkhul Alam Ori','198803222025211102',NULL,NULL,'-','48',NULL,NULL,'guru48@simadis.sch',NULL,'-','-',NULL,'L',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(49,NULL,'Yunda Walida P,S.Pd','199103102025212133',NULL,NULL,'-','49',NULL,NULL,'guru49@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:33'),
(50,NULL,'Ifat Kasyifaturrohmah,M.Pd','198803042025212072',NULL,NULL,'-','50',NULL,NULL,'guru50@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:30'),
(51,NULL,'Veni Sri Nurlita Sari,S.Pd','199502122025212152',NULL,NULL,'-','51',NULL,NULL,'guru51@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(52,NULL,'Amimah,SM','199304212025212181',NULL,NULL,'-','52',NULL,NULL,'guru52@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:28'),
(53,NULL,'Rosalina,S.Pd','199406242025212093',NULL,NULL,'-','53',NULL,NULL,'guru53@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:32'),
(54,NULL,'Muhamad Sopiyudin,S.Pd','199306062025211186',NULL,NULL,'-','54',NULL,NULL,'guru54@simadis.sch',NULL,'-','-',NULL,'P',1,'2026-07-14 07:52:18','2026-07-14 18:54:30');
/*!40000 ALTER TABLE `guru` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `izin_siswa`
--

DROP TABLE IF EXISTS `izin_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `izin_siswa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `jenis_izin_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jam_keluar` time DEFAULT NULL,
  `jam_kembali` time DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `bukti` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'menunggu',
  `guru_piket_id` bigint(20) unsigned DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `izin_siswa_siswa_id_foreign` (`siswa_id`),
  KEY `izin_siswa_kelas_id_foreign` (`kelas_id`),
  KEY `izin_siswa_jenis_izin_id_foreign` (`jenis_izin_id`),
  KEY `izin_siswa_guru_piket_id_foreign` (`guru_piket_id`),
  KEY `izin_siswa_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `izin_siswa_semester_id_foreign` (`semester_id`),
  CONSTRAINT `izin_siswa_guru_piket_id_foreign` FOREIGN KEY (`guru_piket_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `izin_siswa_jenis_izin_id_foreign` FOREIGN KEY (`jenis_izin_id`) REFERENCES `jenis_izin` (`id`),
  CONSTRAINT `izin_siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  CONSTRAINT `izin_siswa_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `izin_siswa_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  CONSTRAINT `izin_siswa_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `izin_siswa`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `izin_siswa` WRITE;
/*!40000 ALTER TABLE `izin_siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `izin_siswa` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jadwal_kbm`
--

DROP TABLE IF EXISTS `jadwal_kbm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_kbm` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `guru_id` bigint(20) unsigned NOT NULL,
  `mata_pelajaran_id` bigint(20) unsigned NOT NULL,
  `jam_belajar_id` bigint(20) unsigned NOT NULL,
  `hari` varchar(191) NOT NULL,
  `jam_ke` int(11) NOT NULL,
  `tahun_ajaran_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_jadwal_guru_mapel` (`guru_id`,`mata_pelajaran_id`,`hari`,`jam_ke`,`tahun_ajaran_id`,`semester_id`),
  KEY `jadwal_kbm_mata_pelajaran_id_foreign` (`mata_pelajaran_id`),
  KEY `jadwal_kbm_jam_belajar_id_foreign` (`jam_belajar_id`),
  KEY `jadwal_kbm_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `jadwal_kbm_semester_id_foreign` (`semester_id`),
  CONSTRAINT `jadwal_kbm_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kbm_jam_belajar_id_foreign` FOREIGN KEY (`jam_belajar_id`) REFERENCES `jam_belajar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kbm_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kbm_mata_pelajaran_id_foreign` FOREIGN KEY (`mata_pelajaran_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kbm_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jadwal_kbm_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jadwal_kbm`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jadwal_kbm` WRITE;
/*!40000 ALTER TABLE `jadwal_kbm` DISABLE KEYS */;
INSERT INTO `jadwal_kbm` VALUES
(1,2,14,8,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(2,2,14,8,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(3,4,14,8,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(4,4,14,8,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(5,5,14,8,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(6,5,14,8,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(7,22,7,1,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(8,22,7,1,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(9,22,7,1,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(10,23,7,1,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(11,23,7,1,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(12,23,7,1,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(13,24,7,1,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(14,24,7,1,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(15,24,7,1,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(16,25,7,1,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(17,25,7,1,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(18,25,7,1,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(19,27,10,1,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(20,27,10,1,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(21,27,10,1,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(22,28,10,1,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(23,28,10,1,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(24,28,10,1,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(25,29,10,1,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(26,29,10,1,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(27,29,10,1,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(28,30,10,1,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(29,30,10,1,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(30,30,10,1,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(31,31,10,1,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(32,31,10,1,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(33,31,10,1,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(34,6,33,1,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(35,6,33,1,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(36,6,33,1,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(37,7,33,1,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(38,7,33,1,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(39,7,33,1,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(40,8,33,1,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(41,8,33,1,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(42,8,33,1,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(43,9,33,1,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(44,9,33,1,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(45,9,33,1,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(46,13,39,1,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(47,13,39,1,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(48,13,39,1,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(49,14,39,1,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(50,14,39,1,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(51,14,39,1,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(52,15,39,1,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:10','2026-07-14 20:58:10'),
(53,15,39,1,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(54,15,39,1,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(55,16,39,1,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(56,16,39,1,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(57,16,39,1,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(58,17,39,1,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(59,17,39,1,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(60,17,39,1,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(61,18,39,1,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(62,18,39,1,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(63,18,39,1,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(64,20,10,1,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(65,20,10,1,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(66,20,10,1,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(67,21,10,1,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(68,21,10,1,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(69,21,10,1,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(70,10,33,1,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(71,10,33,1,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(72,10,33,1,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(73,22,2,8,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(74,22,2,8,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(75,22,2,8,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(76,23,2,8,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(77,23,2,8,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(78,23,2,8,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(79,24,2,8,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(80,24,2,8,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(81,24,2,8,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(82,25,2,8,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(83,25,2,8,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(84,25,2,8,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(85,26,2,8,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(86,26,2,8,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(87,26,2,8,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(88,14,2,8,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(89,14,2,8,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(90,14,2,8,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(91,12,14,8,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(92,12,14,8,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(93,12,14,8,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(94,13,14,8,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(95,13,14,8,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(96,13,14,8,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(97,15,2,8,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(98,15,2,8,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(99,15,2,8,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(100,16,2,8,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(101,16,2,8,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(102,16,2,8,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(103,6,14,8,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(104,6,14,8,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(105,7,14,8,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(106,7,14,8,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(107,8,14,8,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(108,8,14,8,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(109,9,14,8,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(110,9,14,8,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(111,1,14,8,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(112,1,14,8,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(113,11,2,8,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(114,11,2,8,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(115,10,14,8,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(116,10,14,8,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(117,3,14,8,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(118,3,14,8,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(119,31,28,17,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(120,31,28,17,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(121,31,28,17,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(122,27,12,11,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(123,27,12,11,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(124,27,12,11,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(125,27,12,11,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(126,28,12,11,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(127,28,12,11,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(128,28,12,11,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(129,28,12,11,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(130,29,12,11,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(131,29,12,11,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(132,29,12,11,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(133,29,12,11,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(134,30,12,11,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(135,30,12,11,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(136,30,12,11,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(137,30,12,11,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(138,31,12,11,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(139,31,12,11,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(140,31,12,11,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(141,31,12,11,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(142,19,31,11,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(143,19,31,11,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(144,19,31,11,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(145,19,31,11,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(146,20,31,11,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(147,20,31,11,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(148,20,31,11,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(149,20,31,11,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(150,21,31,11,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(151,21,31,11,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(152,21,31,11,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(153,21,31,11,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(154,18,31,11,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(155,18,31,11,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(156,18,31,11,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(157,18,31,11,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(158,11,31,11,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(159,11,31,11,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(160,11,31,11,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(161,10,31,11,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(162,10,31,11,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(163,10,31,11,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(164,5,52,11,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(165,5,52,11,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(166,5,52,11,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(167,6,52,11,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(168,6,52,11,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(169,6,52,11,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(170,7,52,11,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(171,7,52,11,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(172,7,52,11,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(173,22,13,5,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(174,22,13,5,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(175,23,13,5,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(176,23,13,5,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(177,24,13,5,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(178,24,13,5,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(179,25,13,5,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(180,25,13,5,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(181,26,13,5,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(182,26,13,5,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(183,27,13,5,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(184,27,13,5,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(185,28,13,5,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(186,28,13,5,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(187,29,13,5,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(188,29,13,5,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(189,30,13,5,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(190,30,13,5,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(191,31,13,5,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(192,31,13,5,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(193,12,13,5,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(194,12,13,5,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(195,13,13,5,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(196,13,13,5,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(197,18,35,5,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(198,18,35,5,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(199,19,35,5,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(200,19,35,5,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(201,20,35,5,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(202,20,35,5,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(203,21,35,5,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(204,21,35,5,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(205,8,52,11,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(206,8,52,11,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(207,8,52,11,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(208,14,30,5,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(209,14,30,5,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(210,22,24,18,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(211,22,24,18,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(212,23,24,18,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(213,23,24,18,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(214,24,24,18,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(215,24,24,18,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(216,25,24,18,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(217,25,24,18,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(218,26,24,18,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(219,26,24,18,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(220,28,36,18,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(221,28,36,18,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(222,29,36,18,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(223,29,36,18,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:11','2026-07-14 20:58:11'),
(224,30,36,18,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(225,30,36,18,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(226,31,36,18,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(227,31,36,18,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(228,12,36,18,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(229,12,36,18,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(230,13,36,18,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(231,13,36,18,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(232,14,36,18,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(233,14,36,18,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(234,15,36,18,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(235,15,36,18,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(236,16,36,18,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(237,16,36,18,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(238,21,36,18,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(239,21,36,18,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(240,5,46,18,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(241,5,46,18,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(242,6,46,18,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(243,6,46,18,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(244,7,46,18,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(245,7,46,18,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(246,8,46,18,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(247,8,46,18,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(248,9,46,18,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(249,9,46,18,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(250,10,46,18,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(251,10,46,18,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(252,11,46,18,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(253,11,46,18,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(254,17,46,18,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(255,17,46,18,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(256,1,24,18,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(257,1,24,18,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(258,2,24,18,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(259,2,24,18,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(260,11,35,5,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(261,11,35,5,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(262,10,35,5,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(263,10,35,5,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(264,9,35,5,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(265,9,35,5,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(266,4,35,5,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(267,4,35,5,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(268,3,35,5,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(269,3,35,5,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(270,2,35,5,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(271,2,35,5,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(272,1,35,5,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(273,1,35,5,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(274,27,3,3,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(275,27,3,3,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(276,27,3,3,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(277,28,3,3,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(278,28,3,3,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(279,28,3,3,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(280,29,3,3,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(281,29,3,3,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(282,29,3,3,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(283,30,3,3,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(284,30,3,3,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(285,30,3,3,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(286,31,3,3,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(287,31,3,3,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(288,31,3,3,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(289,22,20,3,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(290,22,20,3,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(291,22,20,3,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(292,23,20,3,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(293,23,20,3,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(294,23,20,3,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(295,24,20,3,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(296,24,20,3,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(297,24,20,3,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(298,25,20,3,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(299,25,20,3,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(300,25,20,3,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(301,11,9,3,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(302,11,9,3,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(303,11,9,3,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(304,10,9,3,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(305,10,9,3,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(306,10,9,3,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(307,9,9,3,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(308,9,9,3,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(309,9,9,3,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(310,8,9,3,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(311,8,9,3,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(312,8,9,3,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(313,7,9,3,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(314,7,9,3,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(315,7,9,3,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(316,12,49,3,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(317,12,49,3,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(318,12,49,3,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(319,13,49,3,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(320,13,49,3,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(321,13,49,3,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(322,14,49,3,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(323,14,49,3,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(324,14,49,3,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(325,15,49,3,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(326,15,49,3,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(327,15,49,3,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(328,16,49,3,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(329,16,49,3,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(330,16,49,3,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(331,2,49,3,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(332,2,49,3,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(333,2,49,3,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(334,3,49,3,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(335,3,49,3,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(336,3,49,3,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(337,6,9,3,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(338,6,9,3,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(339,6,9,3,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(340,4,49,3,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(341,4,49,3,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(342,4,49,3,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(343,29,28,17,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(344,29,28,17,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(345,29,28,17,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(346,28,28,17,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(347,28,28,17,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(348,28,28,17,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(349,22,16,9,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(350,22,16,9,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(351,22,16,9,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(352,22,16,9,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(353,23,16,9,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(354,23,16,9,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(355,23,16,9,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(356,23,16,9,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(357,24,16,9,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(358,24,16,9,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(359,24,16,9,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(360,24,16,9,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(361,25,16,9,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(362,25,16,9,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(363,25,16,9,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(364,25,16,9,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(365,26,16,9,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(366,26,16,9,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(367,26,16,9,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(368,26,16,9,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(369,12,29,9,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(370,12,29,9,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(371,12,29,9,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(372,12,29,9,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(373,13,29,9,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(374,13,29,9,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(375,13,29,9,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(376,13,29,9,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(377,14,29,9,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(378,14,29,9,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(379,14,29,9,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(380,14,29,9,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(381,15,29,9,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(382,15,29,9,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(383,15,29,9,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(384,15,29,9,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(385,16,29,9,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(386,16,29,9,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(387,16,29,9,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(388,16,29,9,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(389,2,16,9,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(390,2,16,9,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(391,2,16,9,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(392,3,29,9,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(393,3,29,9,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(394,3,29,9,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(395,4,29,9,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(396,4,29,9,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(397,4,29,9,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(398,5,47,9,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(399,5,47,9,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(400,5,47,9,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(401,6,47,9,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(402,6,47,9,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(403,6,47,9,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(404,7,47,9,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(405,7,47,9,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(406,7,47,9,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(407,8,47,9,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(408,8,47,9,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(409,8,47,9,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(410,9,47,9,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(411,9,47,9,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(412,9,47,9,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(413,10,47,9,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(414,10,47,9,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(415,10,47,9,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:12','2026-07-14 20:58:12'),
(416,11,47,9,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(417,11,47,9,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(418,11,47,9,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(419,17,31,11,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(420,17,31,11,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(421,17,31,11,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(422,17,31,11,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(423,9,52,11,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(424,9,52,11,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(425,9,52,11,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(426,22,25,6,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(427,22,25,6,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(428,22,25,6,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(429,23,25,6,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(430,23,25,6,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(431,23,25,6,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(432,24,25,6,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(433,24,25,6,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(434,24,25,6,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(435,25,25,6,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(436,25,25,6,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(437,25,25,6,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(438,26,25,6,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(439,26,25,6,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(440,26,25,6,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(441,27,25,6,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(442,27,25,6,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(443,27,25,6,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(444,28,25,6,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(445,28,25,6,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(446,28,25,6,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(447,29,25,6,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(448,29,25,6,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(449,29,25,6,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(450,31,40,6,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(451,31,40,6,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(452,31,40,6,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(453,21,40,6,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(454,21,40,6,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(455,21,40,6,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(456,20,40,6,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(457,20,40,6,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(458,20,40,6,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(459,19,40,6,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(460,19,40,6,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(461,19,40,6,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(462,18,40,6,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(463,18,40,6,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(464,18,40,6,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(465,17,40,6,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(466,17,40,6,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(467,17,40,6,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(468,16,40,6,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(469,16,40,6,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(470,16,40,6,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(471,12,37,6,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(472,12,37,6,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(473,12,37,6,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(474,13,37,6,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(475,13,37,6,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(476,13,37,6,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(477,11,37,6,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(478,11,37,6,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(479,11,37,6,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(480,10,37,6,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(481,10,37,6,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(482,10,37,6,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(483,9,37,6,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(484,9,37,6,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(485,9,37,6,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(486,1,48,6,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(487,1,48,6,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(488,1,48,6,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(489,2,48,6,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(490,2,48,6,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(491,2,48,6,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(492,3,48,6,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(493,3,48,6,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(494,3,48,6,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(495,4,48,6,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(496,4,48,6,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(497,4,48,6,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(498,2,54,12,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(499,2,54,12,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(500,3,54,12,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(501,3,54,12,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(502,4,54,12,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(503,4,54,12,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(504,5,54,12,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(505,5,54,12,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(506,20,11,10,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(507,20,11,10,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(508,20,11,10,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(509,20,11,10,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(510,19,11,10,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(511,19,11,10,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(512,19,11,10,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(513,19,11,10,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(514,21,11,10,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(515,21,11,10,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(516,21,11,10,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(517,21,11,10,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(518,27,32,12,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(519,27,32,12,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(520,28,32,12,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(521,28,32,12,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(522,29,32,12,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(523,29,32,12,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(524,30,32,12,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(525,30,32,12,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(526,31,32,12,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(527,31,32,12,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(528,30,40,6,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(529,30,40,6,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(530,30,40,6,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(531,14,37,6,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(532,14,37,6,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(533,14,37,6,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(534,15,37,6,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(535,15,37,6,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(536,15,37,6,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(537,5,48,6,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(538,5,48,6,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(539,5,48,6,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(540,6,48,6,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(541,6,48,6,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(542,6,48,6,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(543,7,48,6,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(544,7,48,6,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(545,7,48,6,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(546,8,37,6,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(547,8,37,6,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(548,8,37,6,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(549,19,36,18,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(550,19,36,18,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(551,20,36,18,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(552,20,36,18,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(553,17,30,5,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(554,17,30,5,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(555,6,54,12,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(556,6,54,12,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(557,7,54,12,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(558,7,54,12,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(559,8,54,12,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(560,8,54,12,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(561,15,30,5,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(562,15,30,5,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(563,23,4,16,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(564,23,4,16,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(565,23,4,16,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(566,23,4,16,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(567,24,4,16,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(568,24,4,16,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(569,24,4,16,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(570,24,4,16,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(571,25,4,16,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(572,25,4,16,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(573,25,4,16,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(574,25,4,16,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(575,26,4,16,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(576,26,4,16,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(577,26,4,16,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(578,26,4,16,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(579,12,4,16,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(580,12,4,16,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(581,12,4,16,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(582,13,4,16,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(583,13,4,16,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(584,13,4,16,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(585,18,34,16,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(586,18,34,16,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(587,18,34,16,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(588,15,50,16,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(589,15,50,16,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(590,15,50,16,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(591,16,50,16,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(592,16,50,16,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(593,16,50,16,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(594,20,50,16,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(595,20,50,16,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(596,20,50,16,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(597,21,50,16,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(598,21,50,16,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(599,21,50,16,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(600,17,34,16,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(601,17,34,16,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(602,17,34,16,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(603,3,38,16,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(604,3,38,16,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(605,3,38,16,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(606,4,38,16,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(607,4,38,16,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(608,4,38,16,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(609,5,38,16,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(610,5,38,16,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(611,5,38,16,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(612,6,38,16,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(613,6,38,16,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(614,6,38,16,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(615,7,38,16,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(616,7,38,16,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:13','2026-07-14 20:58:13'),
(617,7,38,16,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(618,8,38,16,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(619,8,38,16,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(620,8,38,16,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(621,10,50,16,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(622,10,50,16,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(623,10,50,16,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(624,1,16,9,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(625,1,16,9,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(626,1,16,9,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(627,6,32,10,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(628,6,32,10,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(629,20,47,12,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(630,20,47,12,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(631,21,47,12,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(632,21,47,12,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(633,18,52,12,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(634,18,52,12,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(635,5,35,5,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(636,5,35,5,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(637,16,13,5,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(638,16,13,5,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(639,7,32,10,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(640,7,32,10,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(641,1,43,21,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(642,1,43,21,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(643,2,43,21,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(644,2,43,21,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(645,3,43,21,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(646,3,43,21,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(647,4,43,21,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(648,4,43,21,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(649,5,43,21,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(650,5,43,21,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(651,6,43,21,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(652,6,43,21,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(653,7,43,21,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(654,7,43,21,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(655,10,43,21,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(656,10,43,21,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(657,11,43,21,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(658,11,43,21,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(659,8,48,21,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(660,8,48,21,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(661,9,48,21,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(662,9,48,21,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(663,22,19,4,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(664,22,19,4,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(665,22,19,4,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(666,22,19,4,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(667,23,19,4,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(668,23,19,4,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(669,23,19,4,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(670,23,19,4,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(671,24,19,4,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(672,24,19,4,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(673,24,19,4,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(674,24,19,4,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(675,25,19,4,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(676,25,19,4,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(677,25,19,4,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(678,25,19,4,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(679,26,19,4,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(680,26,19,4,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(681,26,19,4,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(682,26,19,4,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(683,27,21,4,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(684,27,21,4,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(685,27,21,4,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(686,27,21,4,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(687,28,21,4,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(688,28,21,4,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(689,28,21,4,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(690,28,21,4,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(691,29,21,4,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(692,29,21,4,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(693,29,21,4,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(694,29,21,4,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(695,30,21,4,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(696,30,21,4,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(697,30,21,4,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(698,30,21,4,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(699,31,21,4,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(700,31,21,4,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(701,31,21,4,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(702,31,21,4,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(703,22,18,13,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(704,22,18,13,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(705,23,18,13,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(706,23,18,13,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(707,24,18,13,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(708,24,18,13,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(709,14,41,13,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(710,14,41,13,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(711,15,41,13,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(712,15,41,13,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(713,13,41,13,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(714,13,41,13,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(715,12,41,13,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(716,12,41,13,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(717,1,19,4,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(718,1,19,4,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(719,1,19,4,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(720,2,19,4,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(721,2,19,4,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(722,2,19,4,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(723,3,21,4,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(724,3,21,4,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(725,3,21,4,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(726,4,21,4,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(727,4,21,4,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(728,4,21,4,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(729,12,53,4,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(730,12,53,4,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(731,12,53,4,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(732,12,53,4,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(733,13,53,4,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(734,13,53,4,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(735,13,53,4,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(736,13,53,4,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(737,14,53,4,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(738,14,53,4,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(739,14,53,4,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(740,14,53,4,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(741,15,53,4,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(742,15,53,4,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(743,15,53,4,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(744,15,53,4,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(745,16,53,4,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(746,16,53,4,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(747,16,53,4,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(748,16,53,4,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(749,17,51,4,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(750,17,51,4,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(751,17,51,4,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(752,17,51,4,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(753,18,51,4,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(754,18,51,4,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(755,18,51,4,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(756,18,51,4,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(757,19,51,4,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(758,19,51,4,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(759,19,51,4,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(760,19,51,4,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(761,20,51,4,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(762,20,51,4,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(763,20,51,4,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(764,20,51,4,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(765,21,51,4,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(766,21,51,4,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(767,21,51,4,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(768,21,51,4,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(769,11,51,4,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(770,11,51,4,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(771,11,51,4,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(772,10,51,4,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(773,10,51,4,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(774,10,51,4,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(775,25,18,13,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(776,25,18,13,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(777,26,18,13,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(778,26,18,13,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(779,9,53,4,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(780,9,53,4,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(781,9,53,4,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(782,8,53,4,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(783,8,53,4,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(784,8,53,4,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(785,7,41,4,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(786,7,41,4,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(787,7,41,4,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(788,6,41,4,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(789,6,41,4,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(790,6,41,4,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(791,5,41,4,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(792,5,41,4,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(793,5,41,4,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(794,18,11,10,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(795,18,11,10,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(796,18,11,10,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(797,18,11,10,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(798,1,6,10,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(799,1,6,10,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(800,2,6,10,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(801,2,6,10,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(802,8,32,10,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(803,8,32,10,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(804,17,11,10,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(805,17,11,10,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(806,17,11,10,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(807,17,11,10,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(808,28,6,10,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(809,28,6,10,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(810,28,6,10,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(811,28,6,10,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(812,29,6,10,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(813,29,6,10,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(814,29,6,10,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(815,29,6,10,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(816,30,6,10,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(817,30,6,10,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(818,30,6,10,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(819,30,6,10,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(820,31,6,10,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(821,31,6,10,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(822,31,6,10,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(823,31,6,10,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(824,5,32,10,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(825,5,32,10,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(826,4,32,10,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(827,4,32,10,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(828,11,11,10,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(829,11,11,10,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(830,10,11,10,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(831,10,11,10,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(832,27,6,10,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(833,27,6,10,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(834,27,6,10,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(835,27,6,10,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(836,28,27,21,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(837,28,27,21,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(838,29,27,21,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(839,29,27,21,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(840,30,27,21,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(841,30,27,21,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(842,31,27,21,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(843,31,27,21,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(844,27,27,21,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(845,27,27,21,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(846,22,4,16,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(847,22,4,16,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(848,22,4,16,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(849,22,4,16,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(850,19,50,16,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(851,19,50,16,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(852,19,50,16,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(853,28,34,16,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(854,28,34,16,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(855,28,34,16,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(856,28,34,16,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(857,29,34,16,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(858,29,34,16,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(859,29,34,16,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(860,29,34,16,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:14','2026-07-14 20:58:14'),
(861,30,34,16,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(862,30,34,16,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(863,30,34,16,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(864,30,34,16,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(865,31,34,16,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(866,31,34,16,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(867,31,34,16,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(868,31,34,16,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(869,27,34,16,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(870,27,34,16,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(871,27,34,16,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(872,27,34,16,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(873,1,38,16,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(874,1,38,16,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(875,1,38,16,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(876,2,38,16,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(877,2,38,16,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(878,2,38,16,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(879,14,50,16,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(880,14,50,16,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(881,14,50,16,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(882,11,32,12,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(883,11,32,12,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(884,26,3,3,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(885,26,3,3,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(886,26,3,3,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(887,1,12,11,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(888,1,12,11,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(889,1,12,11,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(890,2,12,11,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(891,2,12,11,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(892,2,12,11,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(893,3,52,11,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(894,3,52,11,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(895,3,52,11,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(896,4,52,11,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(897,4,52,11,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(898,4,52,11,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(899,1,39,1,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(900,1,39,1,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(901,1,39,1,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(902,2,39,1,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(903,2,39,1,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(904,2,39,1,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(905,4,33,1,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(906,4,33,1,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(907,4,33,1,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(908,5,33,1,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(909,5,33,1,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(910,5,33,1,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(911,3,32,10,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(912,3,32,10,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(913,9,32,10,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(914,9,32,10,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(915,16,43,21,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(916,16,43,21,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(917,16,43,21,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(918,15,43,21,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(919,15,43,21,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(920,15,43,21,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(921,26,45,21,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(922,26,45,21,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(923,26,45,21,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(924,25,45,21,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(925,25,45,21,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(926,25,45,21,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(927,24,45,21,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(928,24,45,21,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(929,24,45,21,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(930,23,45,21,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(931,23,45,21,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(932,23,45,21,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(933,22,45,21,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(934,22,45,21,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(935,22,45,21,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(936,21,27,21,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(937,21,27,21,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(938,21,27,21,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(939,17,27,21,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(940,17,27,21,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(941,17,27,21,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(942,18,27,21,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(943,18,27,21,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(944,18,27,21,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(945,19,27,21,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(946,19,27,21,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(947,19,27,21,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(948,20,27,21,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(949,20,27,21,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(950,20,27,21,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(951,13,45,21,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(952,13,45,21,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(953,13,45,21,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(954,14,45,21,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(955,14,45,21,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(956,14,45,21,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(957,9,50,16,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(958,9,50,16,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(959,9,50,16,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(960,16,41,13,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(961,16,41,13,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(962,20,28,17,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(963,20,28,17,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(964,20,28,17,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(965,19,52,12,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(966,19,52,12,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(967,19,28,17,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(968,19,28,17,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(969,19,28,17,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(970,30,28,17,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(971,30,28,17,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(972,30,28,17,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(973,1,54,12,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(974,1,54,12,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(975,17,47,12,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(976,17,47,12,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(977,1,49,3,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(978,1,49,3,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(979,1,49,3,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(980,5,9,3,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(981,5,9,3,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(982,5,9,3,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(983,19,10,1,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(984,19,10,1,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(985,19,10,1,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(986,26,10,1,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(987,26,10,1,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(988,26,10,1,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(989,27,36,18,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(990,27,36,18,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(991,18,46,18,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(992,18,46,18,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(993,12,45,21,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(994,12,45,21,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(995,12,45,21,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(996,17,28,17,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(997,17,28,17,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(998,17,28,17,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(999,3,33,1,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1000,3,33,1,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1001,3,33,1,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1002,12,39,1,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1003,12,39,1,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1004,12,39,1,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1005,20,9,3,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1006,20,9,3,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1007,20,9,3,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1008,18,3,3,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1009,18,3,3,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1010,18,3,3,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1011,19,9,3,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1012,19,9,3,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1013,19,9,3,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1014,17,3,3,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1015,17,3,3,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1016,17,3,3,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1017,21,3,3,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1018,21,3,3,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1019,21,3,3,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1020,11,50,16,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1021,11,50,16,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1022,11,50,16,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1023,18,54,17,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1024,18,54,17,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1025,18,54,17,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1026,26,5,7,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1027,26,5,7,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1028,26,5,7,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1029,25,5,7,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1030,25,5,7,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1031,25,5,7,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1032,24,5,7,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1033,24,5,7,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1034,24,5,7,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1035,23,5,7,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1036,23,5,7,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1037,23,5,7,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1038,12,15,7,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1039,12,15,7,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1040,12,15,7,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1041,12,15,7,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1042,13,15,7,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1043,13,15,7,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1044,13,15,7,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1045,13,15,7,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1046,14,15,7,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1047,14,15,7,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1048,14,15,7,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1049,14,15,7,26,'Senin',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1050,15,15,7,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1051,15,15,7,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1052,15,15,7,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1053,15,15,7,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1054,16,15,7,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1055,16,15,7,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1056,16,15,7,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1057,16,15,7,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1058,22,46,7,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1059,22,46,7,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1060,22,46,7,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1061,4,22,7,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1062,4,22,7,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1063,4,22,7,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1064,5,22,7,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1065,5,22,7,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1066,5,22,7,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1067,6,22,7,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1068,6,22,7,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1069,6,22,7,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1070,7,22,7,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1071,7,22,7,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1072,7,22,7,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1073,8,22,7,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1074,8,22,7,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1075,8,22,7,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1076,27,28,17,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1077,27,28,17,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1078,27,28,17,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1079,21,54,17,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1080,21,54,17,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1081,21,54,17,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1082,9,54,12,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1083,9,54,12,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1084,10,54,12,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1085,10,54,12,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1086,7,23,5,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1087,7,23,5,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1088,8,23,5,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1089,8,23,5,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1090,22,26,2,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1091,22,26,2,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1092,23,26,2,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1093,23,26,2,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1094,24,26,2,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1095,24,26,2,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1096,25,26,2,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1097,25,26,2,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1098,26,26,2,16,'Senin',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1099,26,26,2,21,'Senin',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1100,27,26,2,10,'Jumat',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1101,27,26,2,15,'Jumat',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1102,28,26,2,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1103,28,26,2,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1104,29,26,2,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1105,29,26,2,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1106,30,26,2,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1107,30,26,2,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1108,31,26,2,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1109,31,26,2,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1110,11,26,2,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1111,11,26,2,41,'Senin',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1112,10,26,2,6,'Senin',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1113,10,26,2,11,'Senin',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1114,12,23,2,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1115,12,23,2,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1116,13,23,2,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1117,13,23,2,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1118,14,23,2,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1119,14,23,2,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1120,15,23,2,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1121,15,23,2,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1122,16,23,2,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1123,16,23,2,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1124,17,23,2,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1125,17,23,2,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1126,18,23,2,17,'Selasa',4,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1127,18,23,2,22,'Selasa',6,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1128,19,23,2,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1129,19,23,2,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1130,20,23,2,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1131,20,23,2,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1132,21,23,2,7,'Selasa',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1133,21,23,2,12,'Selasa',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1134,1,30,2,8,'Rabu',2,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1135,1,30,2,13,'Rabu',3,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1136,2,30,2,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1137,2,30,2,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1138,3,30,2,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1139,3,30,2,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:15','2026-07-14 20:58:15'),
(1140,4,30,2,18,'Rabu',4,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1141,4,30,2,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1142,5,30,2,20,'Jumat',4,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1143,5,30,2,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1144,6,30,2,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1145,6,30,2,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1146,7,30,2,27,'Selasa',7,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1147,7,30,2,32,'Selasa',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1148,8,30,2,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1149,8,30,2,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1150,9,30,2,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1151,9,30,2,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1152,6,35,5,24,'Kamis',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1153,6,35,5,29,'Kamis',7,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1154,3,24,18,31,'Senin',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1155,3,24,18,36,'Senin',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1156,1,46,7,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1157,1,46,7,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1158,1,46,7,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1159,2,15,7,23,'Rabu',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1160,2,15,7,28,'Rabu',7,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1161,2,15,7,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1162,10,22,7,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1163,10,22,7,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1164,10,22,7,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1165,4,24,18,9,'Kamis',2,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1166,4,24,18,14,'Kamis',3,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1167,9,22,7,25,'Jumat',6,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1168,9,22,7,30,'Jumat',8,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1169,9,22,7,35,'Jumat',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1170,11,22,7,33,'Rabu',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1171,11,22,7,38,'Rabu',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1172,11,22,7,43,'Rabu',11,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1173,3,46,7,34,'Kamis',9,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1174,3,46,7,39,'Kamis',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1175,3,46,7,44,'Kamis',11,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1176,11,33,1,37,'Selasa',10,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1177,11,33,1,42,'Selasa',11,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16'),
(1178,11,33,1,19,'Kamis',4,NULL,NULL,'2026-07-14 20:58:16','2026-07-14 20:58:16');
/*!40000 ALTER TABLE `jadwal_kbm` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jam_belajar`
--

DROP TABLE IF EXISTS `jam_belajar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jam_belajar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hari` varchar(191) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 1 COMMENT 'Nomor urut jam (jam ke-1, jam ke-2, dst)',
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `jenis` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jam_belajar`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jam_belajar` WRITE;
/*!40000 ALTER TABLE `jam_belajar` DISABLE KEYS */;
INSERT INTO `jam_belajar` VALUES
(1,'Senin',1,'07:15:00','08:00:00','UPACARA','2026-07-14 20:58:10','2026-07-14 20:58:55'),
(2,'Selasa',1,'07:15:00','08:00:00','PEMBIASAAN','2026-07-14 20:58:10','2026-07-14 20:59:06'),
(3,'Rabu',1,'07:15:00','08:00:00','PEMBIASAAN','2026-07-14 20:58:10','2026-07-14 20:59:18'),
(4,'Kamis',1,'07:15:00','08:00:00','PEMBIASAAN','2026-07-14 20:58:10','2026-07-14 20:59:31'),
(5,'Jumat',1,'07:15:00','08:00:00','ISTIGOSAH','2026-07-14 20:58:10','2026-07-14 20:59:44'),
(6,'Senin',2,'08:00:00','08:45:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(7,'Selasa',2,'08:00:00','08:45:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(8,'Rabu',2,'08:00:00','08:30:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:40'),
(9,'Kamis',2,'08:00:00','08:45:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(10,'Jumat',2,'08:00:00','08:45:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(11,'Senin',3,'08:45:00','09:30:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(12,'Selasa',3,'08:45:00','09:30:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(13,'Rabu',3,'08:45:00','09:30:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(14,'Kamis',3,'08:45:00','09:30:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(15,'Jumat',3,'08:30:00','09:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:51'),
(16,'Senin',4,'09:30:00','10:15:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(17,'Selasa',4,'09:30:00','10:15:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(18,'Rabu',4,'09:30:00','10:15:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(19,'Kamis',4,'09:30:00','10:15:00','KBM','2026-07-14 20:58:10','2026-07-14 20:58:10'),
(20,'Jumat',4,'09:00:00','09:30:00','KBM','2026-07-14 20:58:10','2026-07-14 21:04:04'),
(21,'Senin',6,'10:30:00','11:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:00:20'),
(22,'Selasa',6,'10:30:00','11:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:01:41'),
(23,'Rabu',6,'10:30:00','11:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:02:18'),
(24,'Kamis',6,'10:30:00','11:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:07'),
(25,'Jumat',5,'09:30:00','10:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:10:27'),
(26,'Senin',7,'11:15:00','12:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:00:20'),
(27,'Selasa',7,'11:15:00','12:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:01:41'),
(28,'Rabu',7,'11:15:00','12:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:02:18'),
(29,'Kamis',7,'11:15:00','12:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:07'),
(30,'Jumat',7,'10:15:00','10:45:00','KBM','2026-07-14 20:58:10','2026-07-14 21:10:51'),
(31,'Senin',9,'12:30:00','13:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:00:52'),
(32,'Selasa',9,'12:30:00','13:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:01:55'),
(33,'Rabu',9,'12:30:00','13:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:02:34'),
(34,'Kamis',9,'12:30:00','13:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:21'),
(35,'Jumat',8,'10:45:00','11:15:00','KBM','2026-07-14 20:58:10','2026-07-14 21:11:05'),
(36,'Senin',10,'13:15:00','14:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:00:52'),
(37,'Selasa',10,'13:15:00','14:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:01:55'),
(38,'Rabu',10,'13:15:00','14:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:02:34'),
(39,'Kamis',10,'13:15:00','14:00:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:21'),
(41,'Senin',11,'14:00:00','14:45:00','KBM','2026-07-14 20:58:10','2026-07-14 21:00:52'),
(42,'Selasa',11,'14:00:00','14:45:00','KBM','2026-07-14 20:58:10','2026-07-14 21:01:55'),
(43,'Rabu',11,'14:00:00','14:45:00','KBM','2026-07-14 20:58:10','2026-07-14 21:02:34'),
(44,'Kamis',11,'14:00:00','14:45:00','KBM','2026-07-14 20:58:10','2026-07-14 21:03:21'),
(46,'Senin',5,'10:00:00','10:15:00','ISTIRAHAT','2026-07-14 21:00:20','2026-07-14 21:00:20'),
(47,'Senin',8,'12:00:00','12:30:00','ISTIRAHAT','2026-07-14 21:00:52','2026-07-14 21:00:52'),
(48,'Selasa',5,'10:00:00','10:15:00','ISTIRAHAT','2026-07-14 21:01:41','2026-07-14 21:01:41'),
(49,'Selasa',8,'12:00:00','12:30:00','ISTIRAHAT','2026-07-14 21:01:55','2026-07-14 21:01:55'),
(50,'Rabu',5,'10:00:00','10:15:00','ISTIRAHAT','2026-07-14 21:02:18','2026-07-14 21:02:18'),
(51,'Rabu',8,'12:00:00','12:30:00','ISTIRAHAT','2026-07-14 21:02:34','2026-07-14 21:02:34'),
(52,'Kamis',5,'10:00:00','10:15:00','ISTIRAHAT','2026-07-14 21:03:07','2026-07-14 21:03:07'),
(53,'Kamis',8,'12:00:00','12:30:00','ISTIRAHAT','2026-07-14 21:03:21','2026-07-14 21:03:21'),
(55,'Jumat',6,'10:00:00','10:15:00','ISTIRAHAT','2026-07-14 21:05:51','2026-07-14 21:10:40');
/*!40000 ALTER TABLE `jam_belajar` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jenis_izin`
--

DROP TABLE IF EXISTS `jenis_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_izin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_izin` varchar(191) NOT NULL,
  `butuh_bukti` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_izin`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jenis_izin` WRITE;
/*!40000 ALTER TABLE `jenis_izin` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_izin` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jenis_kegiatan`
--

DROP TABLE IF EXISTS `jenis_kegiatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_kegiatan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(191) NOT NULL,
  `kode` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_kegiatan_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_kegiatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jenis_kegiatan` WRITE;
/*!40000 ALTER TABLE `jenis_kegiatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_kegiatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jenis_pelanggaran`
--

DROP TABLE IF EXISTS `jenis_pelanggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_pelanggaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `poin_default` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_pelanggaran_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_pelanggaran`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jenis_pelanggaran` WRITE;
/*!40000 ALTER TABLE `jenis_pelanggaran` DISABLE KEYS */;
INSERT INTO `jenis_pelanggaran` VALUES
(1,'Terlambat','Terlambat Masuk Sekolah',5,1,'2026-07-14 07:49:02','2026-07-14 07:49:02'),
(2,'Seragam','Seragam Tidak Lengkap',10,1,'2026-07-14 07:49:02','2026-07-14 07:49:02'),
(3,'Atribut','Atribut Tidak Sesuai',5,1,'2026-07-14 07:49:02','2026-07-14 07:49:02'),
(4,'Disiplin','Pelanggaran Disiplin Kelas',15,1,'2026-07-14 07:49:02','2026-07-14 07:49:02');
/*!40000 ALTER TABLE `jenis_pelanggaran` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kegiatan`
--

DROP TABLE IF EXISTS `kegiatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kegiatan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jenis_kegiatan_id` bigint(20) unsigned DEFAULT NULL,
  `nama_kegiatan` varchar(191) NOT NULL,
  `kode_kegiatan` varchar(191) DEFAULT NULL,
  `kategori` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kegiatan_kode_kegiatan_unique` (`kode_kegiatan`),
  KEY `kegiatan_jenis_kegiatan_id_foreign` (`jenis_kegiatan_id`),
  CONSTRAINT `kegiatan_jenis_kegiatan_id_foreign` FOREIGN KEY (`jenis_kegiatan_id`) REFERENCES `jenis_kegiatan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kegiatan` WRITE;
/*!40000 ALTER TABLE `kegiatan` DISABLE KEYS */;
INSERT INTO `kegiatan` VALUES
(1,NULL,'UPACARA','UPC','Umum','2026-07-14 19:01:38','2026-07-14 19:01:38'),
(2,NULL,'PEMBIASAAN','PBS','Umum','2026-07-14 19:01:50','2026-07-14 19:01:50'),
(3,NULL,'ISTIGOSAH','ISTG','Umum','2026-07-14 19:02:02','2026-07-14 19:02:02'),
(4,NULL,'ISTIRAHAT','IST','Umum','2026-07-14 19:11:58','2026-07-14 19:11:58'),
(5,NULL,'KBM','KBM','Umum','2026-07-14 19:17:56','2026-07-14 19:46:49');
/*!40000 ALTER TABLE `kegiatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kelas`
--

DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(191) NOT NULL,
  `kode_kelas` varchar(191) DEFAULT NULL,
  `tingkat_kelas` varchar(191) DEFAULT NULL,
  `jurusan` varchar(20) DEFAULT NULL,
  `wali_kelas_id` bigint(20) unsigned DEFAULT NULL,
  `guru_bk_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelas_wali_kelas_id_foreign` (`wali_kelas_id`),
  KEY `kelas_jurusan_index` (`jurusan`),
  KEY `kelas_guru_bk_id_foreign` (`guru_bk_id`),
  CONSTRAINT `kelas_guru_bk_id_foreign` FOREIGN KEY (`guru_bk_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kelas_wali_kelas_id_foreign` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kelas` WRITE;
/*!40000 ALTER TABLE `kelas` DISABLE KEYS */;
INSERT INTO `kelas` VALUES
(1,'10.A',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(2,'10.B',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(3,'10.C',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(4,'10.D',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(5,'10.E',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(6,'10.F',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(7,'10.G',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(8,'10.H',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(9,'10.I',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(10,'10.J',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(11,'10.K',NULL,'X','Umum',NULL,NULL,NULL,NULL),
(12,'11.A1',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(13,'11.A2',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(14,'11.A3',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(15,'11.A4',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(16,'11.A5',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(17,'11.B1',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(18,'11.B2',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(19,'11.B3',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(20,'11.B4',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(21,'11.B5',NULL,'XI','Umum',NULL,NULL,NULL,NULL),
(22,'12.A1',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(23,'12.A2',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(24,'12.A3',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(25,'12.A4',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(26,'12.A5',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(27,'12.B1',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(28,'12.B2',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(29,'12.B3',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(30,'12.B4',NULL,'XII','Umum',NULL,NULL,NULL,NULL),
(31,'12.B5',NULL,'XII','Umum',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `kelas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kepala_sekolah`
--

DROP TABLE IF EXISTS `kepala_sekolah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kepala_sekolah` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned DEFAULT NULL,
  `nama` varchar(191) NOT NULL,
  `nip` varchar(191) DEFAULT NULL,
  `pangkat_golongan` varchar(191) DEFAULT NULL,
  `tanggal_mulai_jabatan` date NOT NULL,
  `tanggal_selesai_jabatan` date DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `alamat` text DEFAULT NULL,
  `telepon` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kepala_sekolah_nip_unique` (`nip`),
  KEY `kepala_sekolah_guru_id_foreign` (`guru_id`),
  CONSTRAINT `kepala_sekolah_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kepala_sekolah`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kepala_sekolah` WRITE;
/*!40000 ALTER TABLE `kepala_sekolah` DISABLE KEYS */;
/*!40000 ALTER TABLE `kepala_sekolah` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `komponen_nilai`
--

DROP TABLE IF EXISTS `komponen_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `komponen_nilai` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `capaian_pembelajaran_id` bigint(20) unsigned DEFAULT NULL,
  `nama_komponen` varchar(191) NOT NULL,
  `bobot` decimal(5,2) NOT NULL DEFAULT 0.00,
  `capaian_pembelajaran` longtext DEFAULT NULL,
  `tujuan_pembelajaran` longtext DEFAULT NULL,
  `alur_tujuan_pembelajaran` longtext DEFAULT NULL,
  `indikator_kriteria` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `komponen_nilai_capaian_pembelajaran_id_foreign` (`capaian_pembelajaran_id`),
  CONSTRAINT `komponen_nilai_capaian_pembelajaran_id_foreign` FOREIGN KEY (`capaian_pembelajaran_id`) REFERENCES `capaian_pembelajarans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `komponen_nilai`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `komponen_nilai` WRITE;
/*!40000 ALTER TABLE `komponen_nilai` DISABLE KEYS */;
/*!40000 ALTER TABLE `komponen_nilai` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kurikulum_mapel`
--

DROP TABLE IF EXISTS `kurikulum_mapel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kurikulum_mapel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tingkat` varchar(10) NOT NULL,
  `jurusan` varchar(20) DEFAULT NULL,
  `mata_pelajaran_id` bigint(20) unsigned NOT NULL,
  `jp` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kurikulum_mapel_unique` (`tingkat`,`jurusan`,`mata_pelajaran_id`),
  KEY `kurikulum_mapel_mata_pelajaran_id_foreign` (`mata_pelajaran_id`),
  KEY `kurikulum_mapel_tingkat_jurusan_index` (`tingkat`,`jurusan`),
  CONSTRAINT `kurikulum_mapel_mata_pelajaran_id_foreign` FOREIGN KEY (`mata_pelajaran_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kurikulum_mapel`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kurikulum_mapel` WRITE;
/*!40000 ALTER TABLE `kurikulum_mapel` DISABLE KEYS */;
INSERT INTO `kurikulum_mapel` VALUES
(1,'X','Umum',1,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(2,'XI','Umum',1,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(3,'XII','Umum',1,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(4,'X','Umum',2,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(5,'XI','Umum',2,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(6,'XII','Umum',2,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(7,'X','Umum',3,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(8,'XI','Umum',3,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(9,'XII','Umum',3,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(10,'X','Umum',4,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(11,'XI','Umum',4,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(12,'XII','Umum',4,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(13,'X','Umum',5,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(14,'XI','Umum',5,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(15,'XII','Umum',5,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(16,'X','Umum',6,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(17,'XI','Umum',6,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(18,'XII','Umum',6,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(19,'X','Umum',7,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(20,'XI','Umum',7,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(21,'XII','Umum',7,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(22,'X','Umum',8,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(23,'XI','Umum',8,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(24,'XII','Umum',8,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(25,'X','Umum',9,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(26,'XI','Umum',9,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(27,'XII','Umum',9,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(28,'X','Umum',10,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(29,'XI','Umum',10,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(30,'XII','Umum',10,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(31,'X','Umum',11,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(32,'XI','Umum',11,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(33,'XII','Umum',11,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(34,'X','Umum',12,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(35,'XI','Umum',12,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(36,'XII','Umum',12,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(37,'X','Umum',13,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(38,'XI','Umum',13,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(39,'XII','Umum',13,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(40,'X','Umum',14,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(41,'XI','Umum',14,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(42,'XII','Umum',14,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(43,'X','Umum',15,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(44,'XI','Umum',15,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(45,'XII','Umum',15,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(46,'X','Umum',16,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(47,'XI','Umum',16,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(48,'XII','Umum',16,4,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(49,'X','Umum',17,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(50,'XI','Umum',17,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(51,'XII','Umum',17,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(52,'X','Umum',18,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(53,'XI','Umum',18,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(54,'XII','Umum',18,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(55,'X','Umum',19,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(56,'XI','Umum',19,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(57,'XII','Umum',19,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(58,'X','Umum',20,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(59,'XI','Umum',20,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(60,'XII','Umum',20,0,'2026-07-14 07:52:17','2026-07-14 07:52:17'),
(61,'X','Umum',21,2,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(62,'XI','Umum',21,3,'2026-07-14 07:52:17','2026-07-14 20:58:10'),
(63,'XII','Umum',21,2,'2026-07-14 07:52:17','2026-07-14 20:58:10');
/*!40000 ALTER TABLE `kurikulum_mapel` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `laporan_siswa_guru`
--

DROP TABLE IF EXISTS `laporan_siswa_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan_siswa_guru` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `absensi_kelas_id` bigint(20) unsigned DEFAULT NULL,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `guru_pelapor_id` bigint(20) unsigned NOT NULL,
  `wali_kelas_id` bigint(20) unsigned DEFAULT NULL,
  `guru_bk_id` bigint(20) unsigned DEFAULT NULL,
  `deskripsi_permasalahan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laporan_siswa_guru_kelas_id_siswa_id_index` (`kelas_id`,`siswa_id`),
  KEY `laporan_siswa_guru_guru_pelapor_id_index` (`guru_pelapor_id`),
  KEY `laporan_siswa_guru_wali_kelas_id_index` (`wali_kelas_id`),
  KEY `laporan_siswa_guru_guru_bk_id_index` (`guru_bk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan_siswa_guru`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `laporan_siswa_guru` WRITE;
/*!40000 ALTER TABLE `laporan_siswa_guru` DISABLE KEYS */;
/*!40000 ALTER TABLE `laporan_siswa_guru` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `layanan_bk`
--

DROP TABLE IF EXISTS `layanan_bk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `layanan_bk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `guru_bk_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `deskripsi_layanan` text NOT NULL,
  `hasil_layanan` text DEFAULT NULL,
  `rencana_tindak_lanjut` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `layanan_bk_kelas_id_foreign` (`kelas_id`),
  KEY `layanan_bk_guru_bk_id_foreign` (`guru_bk_id`),
  KEY `layanan_bk_siswa_id_foreign` (`siswa_id`),
  CONSTRAINT `layanan_bk_guru_bk_id_foreign` FOREIGN KEY (`guru_bk_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `layanan_bk_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `layanan_bk_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layanan_bk`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `layanan_bk` WRITE;
/*!40000 ALTER TABLE `layanan_bk` DISABLE KEYS */;
/*!40000 ALTER TABLE `layanan_bk` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `log_keamanan`
--

DROP TABLE IF EXISTS `log_keamanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_keamanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `izin_siswa_id` bigint(20) unsigned NOT NULL,
  `jam_keluar_aktual` time DEFAULT NULL,
  `jam_kembali_aktual` time DEFAULT NULL,
  `petugas_keamanan_id` bigint(20) unsigned DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_keamanan_siswa_id_foreign` (`siswa_id`),
  KEY `log_keamanan_izin_siswa_id_foreign` (`izin_siswa_id`),
  KEY `log_keamanan_petugas_keamanan_id_foreign` (`petugas_keamanan_id`),
  CONSTRAINT `log_keamanan_izin_siswa_id_foreign` FOREIGN KEY (`izin_siswa_id`) REFERENCES `izin_siswa` (`id`),
  CONSTRAINT `log_keamanan_petugas_keamanan_id_foreign` FOREIGN KEY (`petugas_keamanan_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `log_keamanan_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_keamanan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `log_keamanan` WRITE;
/*!40000 ALTER TABLE `log_keamanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_keamanan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mata_pelajaran`
--

DROP TABLE IF EXISTS `mata_pelajaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mata_pelajaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jenis_kegiatan_id` bigint(20) unsigned DEFAULT NULL,
  `kode_mapel` varchar(191) DEFAULT NULL,
  `kategori` varchar(20) NOT NULL DEFAULT 'Umum',
  `nama_mapel` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mata_pelajaran_kategori_index` (`kategori`),
  KEY `mata_pelajaran_jenis_kegiatan_id_foreign` (`jenis_kegiatan_id`),
  CONSTRAINT `mata_pelajaran_jenis_kegiatan_id_foreign` FOREIGN KEY (`jenis_kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mata_pelajaran`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mata_pelajaran` WRITE;
/*!40000 ALTER TABLE `mata_pelajaran` DISABLE KEYS */;
INSERT INTO `mata_pelajaran` VALUES
(1,NULL,'PAI','Umum','Pendidikan Agama dan Budi Pekerti',NULL,NULL),
(2,NULL,'PKn','Umum','Pendidikan Pancasila dan Kewarganegaraan',NULL,NULL),
(3,NULL,'B.IND','Umum','Bahasa Indonesia',NULL,NULL),
(4,NULL,'MTK ','Umum','Matematika',NULL,NULL),
(5,NULL,'SEJ','Umum','Sejarah',NULL,NULL),
(6,NULL,'PJOK','Umum','Pendidikan Jasmani, Olahraga, dan Kesehatan',NULL,NULL),
(7,NULL,'BIO','Umum','Biologi',NULL,NULL),
(8,NULL,'FSK','Umum','Fisika',NULL,NULL),
(9,NULL,'KIM','Umum','Kimia',NULL,NULL),
(10,NULL,'SOS','Umum','Sosiologi',NULL,NULL),
(11,NULL,'EKO','Umum','Ekonomi',NULL,NULL),
(12,NULL,'GEO','Umum','Geografi',NULL,NULL),
(13,NULL,'MTL','Umum','Matematika Tingkat Lanjut',NULL,NULL),
(14,NULL,'PKWU','Umum','Prakarya',NULL,NULL),
(15,NULL,'SBD','Umum','Seni Budaya',NULL,NULL),
(16,NULL,'ENG','Umum','Bahasa Inggris',NULL,NULL),
(17,NULL,'BTL','Umum','Biologi Tingkat Lanjut',NULL,NULL),
(18,NULL,'KdKA','Umum','Koding dan Kecerdasan Artifisial',NULL,NULL),
(19,NULL,'SPS','Umum','Seni Paduan Suara',NULL,NULL),
(20,NULL,'STT','Umum','Seni Tari Tradisional',NULL,NULL),
(21,NULL,'SdP','Umum','Seni dan Prakarya',NULL,NULL);
/*!40000 ALTER TABLE `mata_pelajaran` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_reset_tokens_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2019_12_14_000001_create_personal_access_tokens_table',1),
(5,'2026_01_07_000001_create_roles_table',1),
(6,'2026_01_07_000002_create_guru_table',1),
(7,'2026_01_07_000003_create_kelas_table',1),
(8,'2026_01_07_000004_create_siswa_table',1),
(9,'2026_01_07_000005_create_tahun_ajaran_table',1),
(10,'2026_01_07_000006_create_semester_table',1),
(11,'2026_01_07_000007_create_jam_belajar_table',1),
(12,'2026_01_07_000008_create_agenda_tables',1),
(13,'2026_01_07_000009_create_absensi_tables',1),
(14,'2026_01_07_000010_create_izin_and_log_tables',1),
(15,'2026_01_07_000011_create_mata_pelajaran_and_nilai_tables',1),
(16,'2026_01_07_000012_modify_users_table_add_fields',1),
(17,'2026_01_08_132331_create_kepala_sekolah_table',1),
(18,'2026_01_08_132331_create_sekolah_table',1),
(19,'2026_01_08_210500_add_username_to_users_table',1),
(20,'2026_01_08_212000_add_jenis_kelamin_to_users_table',1),
(21,'2026_01_08_213500_add_nip_and_is_active_to_users_table',1),
(22,'2026_01_08_214500_add_kepala_sekolah_id_to_users_table',1),
(23,'2026_01_09_005112_add_foto_to_users_table',1),
(24,'2026_01_10_000001_add_tingkat_kelas_to_kelas_table',1),
(25,'2026_01_10_000100_add_fields_to_siswa_table',1),
(26,'2026_01_10_100000_update_jam_belajar_table',1),
(27,'2026_01_11_000001_make_kelas_id_nullable_in_siswa',1),
(28,'2026_01_11_100000_create_jadwal_kbm_table',1),
(29,'2026_01_11_120000_add_jurusan_to_kelas_table',1),
(30,'2026_01_11_121000_create_kurikulum_mapel_table',1),
(31,'2026_01_11_122000_add_jp_to_kurikulum_mapel_table',1),
(32,'2026_01_11_130000_add_category_to_mata_pelajaran_table',1),
(33,'2026_01_12_100000_add_kode_kelas_to_kelas_table',1),
(34,'2026_01_13_000001_fix_jadwal_kbm_unique_constraint',1),
(35,'2026_01_13_150444_add_header_fields_to_sekolah_table',1),
(36,'2026_01_13_150855_rename_logo_kanan_column_in_sekolah_table',1),
(37,'2026_01_13_160000_add_header_html_to_sekolah_table',1),
(38,'2026_01_14_015017_add_header_lines_to_sekolah_table',1),
(39,'2026_01_14_015433_update_header_lines_to_text_in_sekolah_table',1),
(40,'2026_01_14_015857_add_line_spacing_to_header_lines_in_sekolah_table',1),
(41,'2026_01_14_020212_add_header_line4_to_sekolah_table',1),
(42,'2026_01_14_021552_add_header_line5_to_sekolah_table',1),
(43,'2026_01_14_023910_drop_header_line5_from_sekolah_table',1),
(44,'2026_01_14_add_kode_guru_to_guru_table',1),
(45,'2026_01_14_add_kode_jenis_to_jadwal_kbm_table',1),
(46,'2026_01_14_create_kegiatan_table',1),
(47,'2026_01_14_drop_kode_jenis_from_jadwal_kbm_table',1),
(48,'2026_01_15_000000_create_jenis_kegiatan_table',1),
(49,'2026_01_15_000001_add_jenis_kegiatan_id_to_mata_pelajaran_and_kegiatan',1),
(50,'2026_01_15_000001_add_username_password_to_guru_table',1),
(51,'2026_01_15_000002_add_biodata_to_guru_table',1),
(52,'2026_01_15_000003_add_is_active_to_guru_table',1),
(53,'2026_01_15_133736_add_email_to_guru_table',1),
(54,'2026_01_15_ubah_fk_jenis_kegiatan_id_to_kegiatan',1),
(55,'2026_01_19_000001_add_template_columns_to_agenda_kelas',1),
(56,'2026_01_19_041712_create_rencana_pembelajarans_table',1),
(57,'2026_01_27_042329_add_guru_id_to_guru_table',1),
(58,'2026_01_27_042525_add_foto_to_guru_table',1),
(59,'2026_02_04_000001_create_ekstrakurikuler_table',1),
(60,'2026_02_06_000001_create_role_user_table',1),
(61,'2026_02_06_065055_create_tugas_guru_table',1),
(62,'2026_02_09_000001_add_jenis_tugas_wakil_to_guru_table',1),
(63,'2026_02_09_000002_add_rencana_pembelajaran_id_to_nilai_harian_table',1),
(64,'2026_02_09_000003_create_rencana_pembelajaran_komponen_nilai_table',1),
(65,'2026_02_09_000004_add_pembelajaran_fields_to_komponen_nilai_table',1),
(66,'2026_02_09_060119_create_capaian_pembelajarans_table',1),
(67,'2026_02_09_060152_add_capaian_pembelajaran_id_to_komponen_nilai_table',1),
(68,'2026_02_09_070145_add_pedagogy_fields_to_capaian_pembelajarans_table',1),
(69,'2026_02_09_070518_add_capaian_pembelajaran_id_to_rencana_pembelajarans_table',1),
(70,'2026_02_10_000003_add_hari_piket_to_guru_table',1),
(71,'2026_02_17_130000_add_user_id_to_capaian_pembelajarans_table',1),
(72,'2026_02_18_000001_add_nama_kepala_sekolah_to_sekolah_table',1),
(73,'2026_02_18_000002_add_jenis_kegiatan_to_agenda_kelas',1),
(74,'2026_02_19_120000_create_absensi_guru_table',1),
(75,'2026_02_19_123000_alter_status_absensi_guru_add_izin_sakit',1),
(76,'2026_02_20_000001_add_guru_bk_id_to_kelas_table',1),
(77,'2026_02_20_000002_create_layanan_bk_table',1),
(78,'2026_02_20_000003_create_pembinaan_bk_table',1),
(79,'2026_02_20_000004_create_laporan_siswa_guru_table',1),
(80,'2026_02_20_010000_create_pelanggaran_siswa_table',1),
(81,'2026_02_21_010000_create_tindak_lanjut_bk_table',1),
(82,'2026_02_21_090000_add_poin_pelanggaran_to_pelanggaran_siswa_table',1),
(83,'2026_02_21_091000_create_jenis_pelanggaran_table',1),
(84,'2026_05_19_000001_add_jabatan_kelas_to_siswa_table',1),
(85,'2026_07_14_000000_add_pangkat_golongan_to_guru_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `nilai_harian`
--

DROP TABLE IF EXISTS `nilai_harian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nilai_harian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `guru_id` bigint(20) unsigned DEFAULT NULL,
  `kelas_id` bigint(20) unsigned DEFAULT NULL,
  `mapel_id` bigint(20) unsigned DEFAULT NULL,
  `komponen_id` bigint(20) unsigned DEFAULT NULL,
  `rencana_pembelajaran_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nilai_harian_siswa_id_foreign` (`siswa_id`),
  KEY `nilai_harian_guru_id_foreign` (`guru_id`),
  KEY `nilai_harian_kelas_id_foreign` (`kelas_id`),
  KEY `nilai_harian_mapel_id_foreign` (`mapel_id`),
  KEY `nilai_harian_komponen_id_foreign` (`komponen_id`),
  KEY `nilai_harian_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `nilai_harian_semester_id_foreign` (`semester_id`),
  KEY `nilai_harian_rencana_pembelajaran_id_foreign` (`rencana_pembelajaran_id`),
  CONSTRAINT `nilai_harian_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `nilai_harian_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  CONSTRAINT `nilai_harian_komponen_id_foreign` FOREIGN KEY (`komponen_id`) REFERENCES `komponen_nilai` (`id`),
  CONSTRAINT `nilai_harian_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`),
  CONSTRAINT `nilai_harian_rencana_pembelajaran_id_foreign` FOREIGN KEY (`rencana_pembelajaran_id`) REFERENCES `rencana_pembelajarans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nilai_harian_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`),
  CONSTRAINT `nilai_harian_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  CONSTRAINT `nilai_harian_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nilai_harian`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `nilai_harian` WRITE;
/*!40000 ALTER TABLE `nilai_harian` DISABLE KEYS */;
/*!40000 ALTER TABLE `nilai_harian` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pelanggaran_siswa`
--

DROP TABLE IF EXISTS `pelanggaran_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelanggaran_siswa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `guru_piket_id` bigint(20) unsigned DEFAULT NULL,
  `absensi_kelas_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status_absensi` varchar(30) DEFAULT NULL,
  `deskripsi_pelanggaran` text DEFAULT NULL,
  `poin_pelanggaran` int(10) unsigned NOT NULL DEFAULT 0,
  `jam_ke_1_mulai` time DEFAULT NULL,
  `waktu_input_pelanggaran` datetime DEFAULT NULL,
  `terlambat_menit` int(10) unsigned NOT NULL DEFAULT 0,
  `tahun_ajaran_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pelanggaran_siswa_kelas_id_siswa_id_tanggal_unique` (`kelas_id`,`siswa_id`,`tanggal`),
  KEY `pelanggaran_siswa_siswa_id_foreign` (`siswa_id`),
  KEY `pelanggaran_siswa_guru_piket_id_foreign` (`guru_piket_id`),
  KEY `pelanggaran_siswa_absensi_kelas_id_foreign` (`absensi_kelas_id`),
  KEY `pelanggaran_siswa_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  KEY `pelanggaran_siswa_semester_id_foreign` (`semester_id`),
  KEY `pelanggaran_siswa_kelas_id_tanggal_index` (`kelas_id`,`tanggal`),
  CONSTRAINT `pelanggaran_siswa_absensi_kelas_id_foreign` FOREIGN KEY (`absensi_kelas_id`) REFERENCES `absensi_kelas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pelanggaran_siswa_guru_piket_id_foreign` FOREIGN KEY (`guru_piket_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pelanggaran_siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pelanggaran_siswa_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pelanggaran_siswa_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pelanggaran_siswa_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelanggaran_siswa`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pelanggaran_siswa` WRITE;
/*!40000 ALTER TABLE `pelanggaran_siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `pelanggaran_siswa` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pembinaan_bk`
--

DROP TABLE IF EXISTS `pembinaan_bk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembinaan_bk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `guru_bk_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `wali_kelas_nama` varchar(191) DEFAULT NULL,
  `hadir` int(10) unsigned NOT NULL DEFAULT 0,
  `sakit` int(10) unsigned NOT NULL DEFAULT 0,
  `izin` int(10) unsigned NOT NULL DEFAULT 0,
  `alpa` int(10) unsigned NOT NULL DEFAULT 0,
  `terlambat` int(10) unsigned NOT NULL DEFAULT 0,
  `deskripsi_permasalahan` text NOT NULL,
  `penanganan` text NOT NULL,
  `tindak_lanjut` text DEFAULT NULL,
  `bukti_dukung_absensi` text DEFAULT NULL,
  `laporan_guru` text DEFAULT NULL,
  `laporan_wali_kelas` text DEFAULT NULL,
  `bukti_dukung_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bukti_dukung_files`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembinaan_bk`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pembinaan_bk` WRITE;
/*!40000 ALTER TABLE `pembinaan_bk` DISABLE KEYS */;
/*!40000 ALTER TABLE `pembinaan_bk` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rencana_pembelajaran_komponen_nilai`
--

DROP TABLE IF EXISTS `rencana_pembelajaran_komponen_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rencana_pembelajaran_komponen_nilai` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rencana_pembelajaran_id` bigint(20) unsigned NOT NULL,
  `komponen_nilai_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_kn_unique` (`rencana_pembelajaran_id`,`komponen_nilai_id`),
  KEY `kn_id_fk` (`komponen_nilai_id`),
  CONSTRAINT `kn_id_fk` FOREIGN KEY (`komponen_nilai_id`) REFERENCES `komponen_nilai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rp_id_fk` FOREIGN KEY (`rencana_pembelajaran_id`) REFERENCES `rencana_pembelajarans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rencana_pembelajaran_komponen_nilai`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rencana_pembelajaran_komponen_nilai` WRITE;
/*!40000 ALTER TABLE `rencana_pembelajaran_komponen_nilai` DISABLE KEYS */;
/*!40000 ALTER TABLE `rencana_pembelajaran_komponen_nilai` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rencana_pembelajarans`
--

DROP TABLE IF EXISTS `rencana_pembelajarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rencana_pembelajarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned NOT NULL,
  `mata_pelajaran_id` bigint(20) unsigned NOT NULL,
  `capaian_pembelajaran_id` bigint(20) unsigned DEFAULT NULL,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `jadwal_kbm_id` bigint(20) unsigned DEFAULT NULL,
  `judul` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tujuan` text DEFAULT NULL,
  `metode` text DEFAULT NULL,
  `media` text DEFAULT NULL,
  `sumber` text DEFAULT NULL,
  `penilaian` text DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rencana_pembelajarans_mata_pelajaran_id_foreign` (`mata_pelajaran_id`),
  KEY `rencana_pembelajarans_kelas_id_foreign` (`kelas_id`),
  KEY `rencana_pembelajarans_jadwal_kbm_id_foreign` (`jadwal_kbm_id`),
  KEY `rencana_pembelajarans_guru_id_mata_pelajaran_id_index` (`guru_id`,`mata_pelajaran_id`),
  KEY `rencana_pembelajarans_capaian_pembelajaran_id_foreign` (`capaian_pembelajaran_id`),
  CONSTRAINT `rencana_pembelajarans_capaian_pembelajaran_id_foreign` FOREIGN KEY (`capaian_pembelajaran_id`) REFERENCES `capaian_pembelajarans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rencana_pembelajarans_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rencana_pembelajarans_jadwal_kbm_id_foreign` FOREIGN KEY (`jadwal_kbm_id`) REFERENCES `jadwal_kbm` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rencana_pembelajarans_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rencana_pembelajarans_mata_pelajaran_id_foreign` FOREIGN KEY (`mata_pelajaran_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rencana_pembelajarans`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rencana_pembelajarans` WRITE;
/*!40000 ALTER TABLE `rencana_pembelajarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `rencana_pembelajarans` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  KEY `role_user_role_id_index` (`role_id`),
  KEY `role_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'Guru',NULL,NULL),
(2,'Admin',NULL,NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sekolah`
--

DROP TABLE IF EXISTS `sekolah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sekolah` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_sekolah` varchar(191) NOT NULL,
  `nama_kepala_sekolah` varchar(191) DEFAULT NULL,
  `npsn` varchar(191) DEFAULT NULL,
  `alamat` text NOT NULL,
  `alamat_jalan` varchar(191) DEFAULT NULL,
  `kelurahan` varchar(191) DEFAULT NULL,
  `kecamatan` varchar(191) DEFAULT NULL,
  `kota` varchar(191) NOT NULL,
  `provinsi` varchar(191) NOT NULL,
  `kode_pos` varchar(191) DEFAULT NULL,
  `telepon` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `header_html` longtext DEFAULT NULL,
  `header_line1` text DEFAULT NULL,
  `header_line1_spacing` decimal(3,1) NOT NULL DEFAULT 1.0,
  `header_line2` text DEFAULT NULL,
  `header_line2_spacing` decimal(3,1) NOT NULL DEFAULT 1.0,
  `header_line3` text DEFAULT NULL,
  `header_line3_spacing` decimal(3,1) NOT NULL DEFAULT 1.0,
  `header_line4` text DEFAULT NULL,
  `header_line4_spacing` decimal(3,1) NOT NULL DEFAULT 1.0,
  `website` varchar(191) DEFAULT NULL,
  `jenjang` enum('SD','SMP','SMA','SMK') NOT NULL DEFAULT 'SMA',
  `status` enum('Negeri','Swasta') NOT NULL DEFAULT 'Negeri',
  `akreditasi` varchar(191) DEFAULT NULL,
  `logo` varchar(191) DEFAULT NULL,
  `logo_header_kiri` varchar(191) DEFAULT NULL,
  `logo_kanan` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sekolah_npsn_unique` (`npsn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sekolah`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sekolah` WRITE;
/*!40000 ALTER TABLE `sekolah` DISABLE KEYS */;
/*!40000 ALTER TABLE `sekolah` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `semester`
--

DROP TABLE IF EXISTS `semester`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tahun_ajaran_id` bigint(20) unsigned NOT NULL,
  `nama_semester` varchar(191) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `semester_tahun_ajaran_id_foreign` (`tahun_ajaran_id`),
  CONSTRAINT `semester_tahun_ajaran_id_foreign` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `semester` WRITE;
/*!40000 ALTER TABLE `semester` DISABLE KEYS */;
/*!40000 ALTER TABLE `semester` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `siswa`
--

DROP TABLE IF EXISTS `siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nis` varchar(191) DEFAULT NULL,
  `nisn` varchar(191) DEFAULT NULL,
  `nama` varchar(191) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `kelas_id` bigint(20) unsigned DEFAULT NULL,
  `jabatan_kelas` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `siswa_nis_unique` (`nis`),
  UNIQUE KEY `siswa_nisn_unique` (`nisn`),
  UNIQUE KEY `siswa_email_unique` (`email`),
  KEY `siswa_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siswa`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `siswa` WRITE;
/*!40000 ALTER TABLE `siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `siswa` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tahun_ajaran`
--

DROP TABLE IF EXISTS `tahun_ajaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tahun_ajaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_tahun` varchar(191) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahun_ajaran`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tahun_ajaran` WRITE;
/*!40000 ALTER TABLE `tahun_ajaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahun_ajaran` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tindak_lanjut_bk`
--

DROP TABLE IF EXISTS `tindak_lanjut_bk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tindak_lanjut_bk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint(20) unsigned NOT NULL,
  `siswa_id` bigint(20) unsigned NOT NULL,
  `guru_bk_id` bigint(20) unsigned DEFAULT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `nis` varchar(50) DEFAULT NULL,
  `nisn` varchar(50) DEFAULT NULL,
  `nama_wali_kelas` varchar(150) DEFAULT NULL,
  `nama_guru_bk` varchar(150) DEFAULT NULL,
  `waktu` varchar(255) NOT NULL,
  `nama_penyusun` varchar(150) DEFAULT NULL,
  `rencana_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rencana_items`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tindak_lanjut_bk_siswa_id_foreign` (`siswa_id`),
  KEY `tindak_lanjut_bk_guru_bk_id_foreign` (`guru_bk_id`),
  KEY `tindak_lanjut_bk_kelas_id_siswa_id_index` (`kelas_id`,`siswa_id`),
  CONSTRAINT `tindak_lanjut_bk_guru_bk_id_foreign` FOREIGN KEY (`guru_bk_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tindak_lanjut_bk_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tindak_lanjut_bk_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tindak_lanjut_bk`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tindak_lanjut_bk` WRITE;
/*!40000 ALTER TABLE `tindak_lanjut_bk` DISABLE KEYS */;
/*!40000 ALTER TABLE `tindak_lanjut_bk` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tugas_guru`
--

DROP TABLE IF EXISTS `tugas_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tugas_guru` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guru_id` bigint(20) unsigned NOT NULL,
  `mata_pelajaran_id` bigint(20) unsigned NOT NULL,
  `tingkat_kelas` varchar(10) NOT NULL,
  `kelas_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tugas_guru_unique` (`guru_id`,`mata_pelajaran_id`,`tingkat_kelas`,`kelas_id`),
  KEY `tugas_guru_guru_id_index` (`guru_id`),
  KEY `tugas_guru_mata_pelajaran_id_index` (`mata_pelajaran_id`),
  KEY `tugas_guru_kelas_id_index` (`kelas_id`),
  KEY `tugas_guru_guru_id_is_active_index` (`guru_id`,`is_active`),
  KEY `tugas_guru_mata_pelajaran_id_tingkat_kelas_index` (`mata_pelajaran_id`,`tingkat_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=399 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tugas_guru`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tugas_guru` WRITE;
/*!40000 ALTER TABLE `tugas_guru` DISABLE KEYS */;
INSERT INTO `tugas_guru` VALUES
(1,2,8,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(2,2,8,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(3,2,8,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(4,2,8,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(5,2,8,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(6,2,8,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(7,2,8,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(8,2,8,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(9,2,8,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(10,3,3,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(11,3,3,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(12,3,3,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(13,3,3,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(14,3,3,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(15,3,3,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(16,3,3,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(17,3,3,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(18,3,3,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(19,4,16,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(20,4,16,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(21,4,16,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(22,4,16,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(23,4,16,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(24,4,16,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(25,4,16,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(26,5,7,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(27,5,7,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(28,5,7,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(29,5,7,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(30,6,10,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(31,6,10,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(32,6,10,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(33,6,10,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(34,6,10,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(35,6,10,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(36,6,10,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(37,7,1,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(38,7,1,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(39,7,1,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(40,7,1,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(41,9,3,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(42,9,3,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(43,9,3,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(44,9,3,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(45,9,3,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(46,9,3,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(47,9,3,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(48,9,3,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(49,9,3,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(50,10,1,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(51,10,1,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(52,10,1,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(53,10,1,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(54,10,1,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(55,10,1,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(56,10,1,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(57,10,1,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(58,10,1,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(59,11,10,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(60,11,10,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(61,11,10,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(62,11,10,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(63,11,10,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(64,11,10,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(65,11,10,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(66,12,11,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(67,12,11,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(68,12,11,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(69,12,11,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(70,12,11,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(71,12,11,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(72,12,11,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(73,13,5,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(74,13,5,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(75,13,5,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(76,13,5,'XII',NULL,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(77,14,8,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(78,14,8,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(79,14,8,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(80,14,8,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(81,14,8,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(82,14,8,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(83,14,8,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(84,14,8,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(85,14,8,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(86,14,8,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(87,14,8,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(88,14,8,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(89,15,7,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(90,15,7,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(91,15,7,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(92,15,7,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(93,15,7,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(94,15,7,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(95,16,9,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(96,16,9,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(97,16,9,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(98,16,9,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(99,16,9,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(100,16,9,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(101,16,9,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(102,18,13,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(103,18,13,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(104,18,13,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(105,18,13,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(106,18,13,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(107,19,4,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(108,19,4,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(109,19,4,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(110,19,4,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(111,19,4,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(112,19,4,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(113,19,4,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(114,20,3,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(115,20,3,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(116,20,3,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(117,20,3,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(118,21,4,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(119,21,4,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(120,21,4,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(121,21,4,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(122,21,4,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(123,21,4,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(124,21,4,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(125,22,7,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(126,22,7,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(127,22,7,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(128,22,7,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(129,22,7,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(130,22,7,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(131,22,7,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(132,22,7,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(133,23,2,'XI',NULL,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(134,23,5,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(135,23,5,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(136,24,18,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(137,24,18,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(138,24,18,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(139,24,18,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(140,24,18,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(141,24,18,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(142,24,18,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(143,24,18,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(144,24,18,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(145,25,6,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(146,25,6,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:26','2026-07-14 07:52:26'),
(147,25,6,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(148,25,6,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(149,25,6,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(150,25,6,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(151,25,6,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(152,25,6,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(153,26,2,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(154,26,2,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(155,26,2,'XII',NULL,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(156,27,21,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(157,27,21,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(158,27,21,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(159,27,21,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(160,27,21,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(161,27,21,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(162,27,21,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(163,27,21,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(164,27,21,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(165,27,21,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(166,28,17,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(167,28,17,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(168,28,17,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(169,28,17,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(170,28,17,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(171,28,17,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(172,28,17,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(173,28,17,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(174,29,9,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(175,29,9,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(176,29,9,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(177,29,9,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(178,29,9,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(179,29,9,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(180,29,9,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(181,30,2,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(182,30,2,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(183,30,2,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(184,30,2,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(185,30,2,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(186,30,2,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(187,30,2,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(188,30,2,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(189,30,2,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(190,30,5,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(191,30,5,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(192,30,5,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(193,31,11,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(194,31,11,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(195,31,11,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(196,31,11,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(197,31,11,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(198,31,11,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(199,31,11,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(200,32,10,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(201,32,10,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(202,32,10,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(203,32,10,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(204,32,10,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(205,32,10,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(206,32,10,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(207,32,12,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(208,32,12,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(209,32,12,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(210,32,12,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(211,32,12,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(212,32,12,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(213,33,1,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(214,33,1,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(215,33,1,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(216,33,1,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(217,33,1,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(218,33,1,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(219,33,1,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(220,33,1,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(221,33,1,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(222,34,16,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(223,34,16,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(224,34,16,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(225,34,16,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(226,34,16,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(227,34,16,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(228,34,16,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(229,35,5,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(230,35,5,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(231,35,5,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(232,35,5,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(233,35,5,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(234,35,5,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(235,35,5,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(236,35,5,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(237,35,5,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(238,35,5,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(239,35,5,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(240,35,5,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(241,35,5,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(242,36,18,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(243,36,18,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(244,36,18,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(245,36,18,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(246,36,18,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(247,36,18,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(248,36,18,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(249,36,18,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(250,36,18,'XII',27,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(251,36,18,'XII',28,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(252,36,18,'XII',29,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(253,36,18,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(254,36,18,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(255,37,6,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(256,37,6,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(257,37,6,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(258,37,6,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(259,37,6,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(260,37,6,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(261,37,6,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(262,37,6,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(263,38,16,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(264,38,16,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(265,38,16,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(266,38,16,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(267,38,16,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(268,38,16,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(269,38,16,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(270,38,16,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(271,39,1,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(272,39,1,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(273,39,1,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(274,39,1,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(275,39,1,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(276,39,1,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(277,39,1,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(278,39,1,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(279,39,1,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(280,40,6,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(281,40,6,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(282,40,6,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(283,40,6,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(284,40,6,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(285,40,6,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(286,40,6,'XII',30,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(287,40,6,'XII',31,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(288,41,4,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(289,41,4,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(290,41,4,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(291,41,13,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(292,41,13,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(293,41,13,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(294,41,13,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(295,41,13,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(296,43,21,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(297,43,21,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(298,43,21,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(299,43,21,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(300,43,21,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(301,43,21,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(302,43,21,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(303,43,21,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(304,43,21,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(305,43,21,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(306,43,21,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(307,45,21,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(308,45,21,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(309,45,21,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(310,45,21,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(311,45,21,'XII',23,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(312,45,21,'XII',24,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(313,45,21,'XII',25,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(314,45,21,'XII',26,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(315,46,7,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(316,46,7,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(317,46,7,'XII',22,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(318,46,18,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(319,46,18,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(320,46,18,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(321,46,18,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(322,46,18,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(323,46,18,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(324,46,18,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(325,46,18,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(326,46,18,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(327,47,9,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(328,47,9,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(329,47,9,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(330,47,9,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(331,47,9,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(332,47,9,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(333,47,9,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(334,47,12,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(335,47,12,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(336,47,12,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(337,48,6,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(338,48,6,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(339,48,6,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(340,48,6,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(341,48,6,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(342,48,6,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(343,48,6,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(344,48,21,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(345,48,21,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(346,49,3,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(347,49,3,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(348,49,3,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(349,49,3,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(350,49,3,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(351,49,3,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(352,49,3,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(353,49,3,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(354,49,3,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(355,50,16,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(356,50,16,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(357,50,16,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(358,50,16,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(359,50,16,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(360,50,16,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(361,50,16,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(362,50,16,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(363,50,16,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(364,51,4,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(365,51,4,'X',11,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(366,51,4,'XI',17,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(367,51,4,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(368,51,4,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(369,51,4,'XI',20,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(370,51,4,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(371,52,11,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(372,52,11,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(373,52,11,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(374,52,11,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(375,52,11,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(376,52,11,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(377,52,11,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(378,52,12,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(379,52,12,'XI',19,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(380,53,4,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(381,53,4,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(382,53,4,'XI',12,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(383,53,4,'XI',13,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(384,53,4,'XI',14,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(385,53,4,'XI',15,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(386,53,4,'XI',16,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(387,54,12,'X',1,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(388,54,12,'X',2,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(389,54,12,'X',3,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(390,54,12,'X',4,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(391,54,12,'X',5,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(392,54,12,'X',6,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(393,54,12,'X',7,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(394,54,12,'X',8,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(395,54,12,'X',9,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(396,54,12,'X',10,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(397,54,17,'XI',18,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27'),
(398,54,17,'XI',21,1,'Auto-generated from jadwal import','2026-07-14 07:52:27','2026-07-14 07:52:27');
/*!40000 ALTER TABLE `tugas_guru` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `nip` varchar(191) DEFAULT NULL,
  `username` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `jenis_kelamin` varchar(191) DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `guru_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `siswa_id` bigint(20) unsigned DEFAULT NULL,
  `kepala_sekolah_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_siswa_id_foreign` (`siswa_id`),
  KEY `users_kepala_sekolah_id_foreign` (`kepala_sekolah_id`),
  CONSTRAINT `users_kepala_sekolah_id_foreign` FOREIGN KEY (`kepala_sekolah_id`) REFERENCES `kepala_sekolah` (`id`),
  CONSTRAINT `users_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(2,'Administrator',NULL,'admin','admin@simadis.sch.id','$2y$10$FVVGTyt8weamg34Wpe.73OPLumFHK56qrCe9OZVCO/dTAVW6b2snm','L',NULL,1,2,NULL,'2026-07-14 07:50:55','2026-07-14 07:50:55',NULL,NULL),
(3,'Asep Kurniawan',NULL,'198607092010011005','guru18@simadis.sch','$2y$10$oFpEyrcg8iEdkiTuZ7qQ9ugLmrkS/Y8Wydkp/eoJyTDa3XFBP4Xua','L',NULL,1,1,18,'2026-07-14 07:54:32','2026-07-14 18:56:22',NULL,NULL),
(4,'Ahmad Taufik Halaili',NULL,'197311062006041004','guru7@simadis.sch','$2y$10$ma2OhVDdjDE2mvR3FapWRuEU1/dAEw7a4ZHeJL77xdIWb8kKnBcpm','L',NULL,1,1,7,'2026-07-14 18:54:28','2026-07-14 18:56:21',NULL,NULL),
(5,'Ali Sudirman,S.Pd',NULL,'198812152025211038','guru37@simadis.sch','$2y$10$s3BFnOHc2OVd9cgQYQ0hLOz5QzAnhCuAOJ1.2Xu4nS1cALljCYTqW','P',NULL,1,1,37,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(6,'Amimah,SM',NULL,'199304212025212181','guru52@simadis.sch','$2y$10$DiDWDOom5EmSHs4SCBeikOG5wRpFFPiJCv/JYzlBL5LLDSTdYkUw2','P',NULL,1,1,52,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(7,'Anisatul Hatroh,S.Pd',NULL,'198608292025212035','guru34@simadis.sch','$2y$10$1gqxCnu64DklhSNhh0TbqOE/.vWegwS7ntIuo9zXzCsG4t/Eucpi.','P',NULL,1,1,34,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(8,'Arifuddin, S.Pd',NULL,'198008142009021002','guru14@simadis.sch','$2y$10$cx1b35DcM9HZXDW7G5kI7.K72wsgWW558ruiK3xrSqtMCcsykvRVW','P',NULL,1,1,14,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(9,'Aulia Rahmawati,S.Pd',NULL,'199209282025212040','guru41@simadis.sch','$2y$10$Jd4LE5rxC.VndQjh8OEjYemWgH6T0xIAzHYs81AXckoxOqNm1SUYe','P',NULL,1,1,41,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(10,'Dra.Hj.Isnaini Nasuka R,M.Pd',NULL,'196807281996012001','guru5@simadis.sch','$2y$10$eEwYQMDcH3NGnKqVhnTWWuBeFM/HHvnjlYDALtG2/l5VaTKiBQBLG','L',NULL,1,1,4,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(11,'Drs. H. Hawar',NULL,'196809052008011006','guru9@simadis.sch','$2y$10$cyVu.F9oGk01k1CqKeRR4OMWTIS8B5FWvtR0CT.NJsPDrKaAo1/yC','P',NULL,1,1,9,'2026-07-14 18:54:28','2026-07-14 18:56:22',NULL,NULL),
(12,'Drs. Hasan Basri ,M.Pd',NULL,'196909131994121002','guru3@simadis.sch','$2y$10$b64yq3gGqIAd/lqBI4LyteOXYZWBTXlaRK9Re9WGYvisXtTBMr4aa','L',NULL,1,1,3,'2026-07-14 18:54:29','2026-07-14 18:56:22',NULL,NULL),
(13,'Drs.H. Asep Sopiyandi ,M.Pd',NULL,'196705041994031016','guru2@simadis.sch','$2y$10$dfuXbCDKtsHNT5TRs5yHu.DS1dXqkTELwWHgvOJMIWL9g/Vw//HA2','L',NULL,1,1,2,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(14,'Dyah Kartika Sari',NULL,'199404212025212100','guru45@simadis.sch','$2y$10$2CzK6P7Z2bfQVhJXqhGHbORzfgcE0omJcGRYTwioyRm/R2xXr4QJG','P',NULL,1,1,45,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(15,'Elis MukhlisAh, S.Pd',NULL,'198206302009022003','guru15@simadis.sch','$2y$10$oQlVsOPO1sE6yublsPfzcOvArpqkNMGMMFJP2sSZt7jOKfy.rBA5q','P',NULL,1,1,15,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(16,'Enih Sulastri,S.Pd',NULL,'198508102010012005','guru21@simadis.sch','$2y$10$6VS0C3p/AAEYy.lA.yKaEOVYprbnXOyB/KqTsVgMgef.y4F68Ji.W','P',NULL,1,1,21,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(17,'Eva Muzdalifah,S.Psi',NULL,'199704132025212022','guru44@simadis.sch','$2y$10$LxYB3krV5e0vCX5GZqK8veu6YT6Yry03TQ8uNMTDBLJU8Vi8TgrbC','P',NULL,1,1,44,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(18,'Fahrudin, S.Pd',NULL,'199207252025211032','guru40@simadis.sch','$2y$10$M3C5cakhVLjBDM/sEWzpQ.rYzz4KC86ShiJqKXm8PefNoeIhwqhqu','P',NULL,1,1,40,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(19,'Fikri Burhani',NULL,'198310182022211004','guru27@simadis.sch','$2y$10$ZlkMyKvmr1UhYiyF26x3auzBC7fQso2PIbgtLW3NUUy.ThNBGn8Ui','L',NULL,1,1,27,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(20,'Fina Faelasufatunnajah,M.Pd',NULL,'199604152025212037','guru42@simadis.sch','$2y$10$iCvfr.KOs2BVD1Q8uu2vbe9H3WiNPLNhOvFKtgF5cZZd7v3xdorJC','P',NULL,1,1,42,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(21,'H. Kasir, S.Pd',NULL,'197004152008011006','guru10@simadis.sch','$2y$10$8aRZhMXafOhCrLHry0QLyuLkL346REPPM0cdTsQ/sE8RGNHkhKnlq','P',NULL,1,1,10,'2026-07-14 18:54:29','2026-07-14 18:56:23',NULL,NULL),
(22,'H. Mulyadi,S.Pd,MM',NULL,'196706022006041002','guru6@simadis.sch','$2y$10$77wTo0TqxTB14ZQRJe0Zfu08SJjEVwSqRmgZDEQ9mKzt/YnSe9rI.','P',NULL,1,1,6,'2026-07-14 18:54:30','2026-07-14 18:56:23',NULL,NULL),
(23,'Herni Wahyuni,S.Pd',NULL,'198410132011012001','guru19@simadis.sch','$2y$10$tTQA5xKJJGBtmzRqTKhPnuCF/id8Ngay1ddgoVuH0ePF/UHA5YdgK','P',NULL,1,1,19,'2026-07-14 18:54:30','2026-07-14 18:56:23',NULL,NULL),
(24,'Hj. Maria Ulfah, S.Psi',NULL,'198102212010012003','guru17@simadis.sch','$2y$10$MxaHGDKKf/WAHILdvTpRN.gaG2cV3Ll4B7phyln0RP7g3RYF65cxi','P',NULL,1,1,17,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(25,'Hj. Nur Emiliyah, SE',NULL,'197902162008012012','guru12@simadis.sch','$2y$10$yCTqxYNdjpvG4YFw7QdMh.J/il1i7t306MtI.ZkWmlzgtOLu/On4O','P',NULL,1,1,12,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(26,'Ifat Kasyifaturrohmah,M.Pd',NULL,'198803042025212072','guru50@simadis.sch','$2y$10$CRp95hGeKVIpbWXYMU0Tnu4TorMecZhQ44ibaucgkdV4b0uyk9CYG','P',NULL,1,1,50,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(27,'Iha Musliha, S.Sos',NULL,'198309172025212026','guru32@simadis.sch','$2y$10$gd3VHVMC/8GGg.aR/55xqOaQew.kZeUdfiVq1q9wT1TQABx4axLy2','P',NULL,1,1,32,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(28,'Ihsana Romadlon',NULL,'198406122011011001','guru20@simadis.sch','$2y$10$unKghBOEVVFwarcGwqn7TO/L0FgOfdWddHSbp2MZL61H1uxHz9L5u','L',NULL,1,1,20,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(29,'Iis Khaerunisah,S.Pd',NULL,'199409272020122018','guru23@simadis.sch','$2y$10$JC8JKEGMfcdQ1xhhiyqzHOhG1JO7ypIGVryUvpqjv6XYSk7Clgv6K','P',NULL,1,1,23,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(30,'Iri Setiawan',NULL,'197001141995121001','guru4@simadis.sch','$2y$10$XMQ4tTOxXxZUmipoud4aFuLtS490McheUJL94dZgHT4sj1MQ0M2kq','L',NULL,1,1,5,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(31,'Lutfah, ST',NULL,'197607272025212021','guru47@simadis.sch','$2y$10$b..6Z5Z2ViYIfMzGkca2Eux03pBMSXHNJ5WwXUma0aZ3qr9/Lh1em','P',NULL,1,1,47,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(32,'M Fatkhul Alam Ori',NULL,'198803222025211102','guru48@simadis.sch','$2y$10$MuEZfDVTzEl0FJqzJhG1huFqXxnlkSLdd2FYEOKtpfefbmAJI56ZS','L',NULL,1,1,48,'2026-07-14 18:54:30','2026-07-14 18:56:24',NULL,NULL),
(33,'Muhamad Sopiyudin,S.Pd',NULL,'199306062025211186','guru54@simadis.sch','$2y$10$BdFCI1uTwnXuel27mcqzk.ftgLutXPOhdDjbM2YvpU4U7gYQko.IG','P',NULL,1,1,54,'2026-07-14 18:54:31','2026-07-14 18:56:24',NULL,NULL),
(34,'Mukhlish,ST,M.Pd',NULL,'197803182010011013','guru16@simadis.sch','$2y$10$B1CJa56wBID9wgx9XD0tyuBJA67sRX4wtO3wvgROM4vTE9YbWEHIW','P',NULL,1,1,16,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(35,'Mukhlisin, SE',NULL,'199106062025211060','guru38@simadis.sch','$2y$10$kCUO3GYeozPgFdy/nHTXEeY8lu6hvrJ29wfc8uP93vVMoDiEvq1bS','P',NULL,1,1,38,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(36,'Naziyah,S.Pd,M.Pd',NULL,'197203072008012007','guru13@simadis.sch','$2y$10$MmRuAwCq7oe6lPkELhxK0OmeBigAGi.oVI5deH6KQ4gOsoWkEbWzS','P',NULL,1,1,13,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(37,'Neng Astry Mediana,S.Pd',NULL,'198905162019032014','guru22@simadis.sch','$2y$10$hViKiaues.51CTvPWS1xxOR9tNUyWRuywikk0dLSwNuYxGNSmNXye','P',NULL,1,1,22,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(38,'Nur Fairuz Fatin',NULL,'199711272025212048','guru46@simadis.sch','$2y$10$q.I9x6MQuB.wiisLfXCq7OgvTIkI4SjFUIRtCBMdF2zirjlwE0vGi','P',NULL,1,1,46,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(39,'Nur Kholifah,S.Pd,M.Pd',NULL,'198710272024212027','guru28@simadis.sch','$2y$10$tgNwwnFrjN7CEKnbntz9qed/cZtmkcCrrM/vrNyuoYBxYJjbQkMJa','P',NULL,1,1,28,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(40,'Nurjannah Triastuti Rahajeng, S.Hi',NULL,'198112012025212024','guru31@simadis.sch','$2y$10$1cJYh.YjV2dKObyAsHh5FeFwKEFJyYjbKCjW6R6LszmVk1imUHPbO','P',NULL,1,1,31,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(41,'Nurul Anwar,S.Pd',NULL,'198405062025211024','guru33@simadis.sch','$2y$10$mXizRZXFiojIWSvCnRaWT.ccs6dAhOP7P12vtLlWHtEwsIS8Xn4z2','P',NULL,1,1,33,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(42,'Patmawati,S.Pd',NULL,'199210122025212047','guru39@simadis.sch','$2y$10$RyAzDcWAg2LX03.2Z0JzyuExwqUL78RULK481aM7n3fgfwyZgADQy','P',NULL,1,1,39,'2026-07-14 18:54:31','2026-07-14 18:56:25',NULL,NULL),
(43,'Prayogo, S.Kom',NULL,'198101012022211017','guru24@simadis.sch','$2y$10$q2G3plEgCkJp676dLPoEoeqzbZIvKYsiPLiBMlNrgGiPLySbr9v0q','L',NULL,1,1,24,'2026-07-14 18:54:32','2026-07-14 18:56:25',NULL,NULL),
(44,'Rahmat Qurniawan, SE',NULL,'198706052025211060','guru36@simadis.sch','$2y$10$xEnIwN/zaKHFW1RgB66h9.bxjnjZ9uKDyyVxeVXzBBbtMIj2pWjE6','P',NULL,1,1,36,'2026-07-14 18:54:32','2026-07-14 18:56:25',NULL,NULL),
(45,'Rahmatullah,S.Pd',NULL,'198505012024211007','guru30@simadis.sch','$2y$10$Ymimz.QdR1CYBZz7dnMJPuhCRdpTIvqpXkW3UYYJPAFhMhRwCV8kS','P',NULL,1,1,30,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(46,'Rani Laetamani, S.Pd',NULL,'197403252006042002','guru8@simadis.sch','$2y$10$gLcLsDe32IAxrCJobvnfVeAB4EEYgfKM0D7mq6LuO3SqjPPtgQD92','P',NULL,1,1,8,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(47,'Rokilah, S.Hum',NULL,'198706072025212046','guru35@simadis.sch','$2y$10$z9IrpSZEQ8e.Qp7vQnXTyOo75Pxvr5x3Fw4KSNeu7/ZyHCjfl7OCG','P',NULL,1,1,35,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(48,'Rosalina,S.Pd',NULL,'199406242025212093','guru53@simadis.sch','$2y$10$wMVl5cshEH.eLcHSyNzk5u.sVysZQmHAQnRxt0ah5/N.Xzqj.kf/m','P',NULL,1,1,53,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(49,'Safili,S.Pd',NULL,'198408022022211016','guru25@simadis.sch','$2y$10$ZXCanWy1x/FOteVZca7LlO4bBypSdFtq6EFpF6KTRYeSjv8nNZdEC','P',NULL,1,1,25,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(50,'Sri Zuniar Ningsih,S.H',NULL,'196906102023212005','guru26@simadis.sch','$2y$10$HvH2o8yjzin40T/zSRWnk.tgSZ.8vOPMhY2CvrYAZWj3CPGcWHTcm','P',NULL,1,1,26,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(51,'Veni Sri Nurlita Sari,S.Pd',NULL,'199502122025212152','guru51@simadis.sch','$2y$10$FWTUvZvEcj0HnvBR14TVpuOu0V2DW5lU5EmzlhV0Heqpe5o6fdzwe','P',NULL,1,1,51,'2026-07-14 18:54:32','2026-07-14 18:56:26',NULL,NULL),
(52,'Widowati, S.Pd',NULL,'197103082024212002','guru29@simadis.sch','$2y$10$kNZSyDaMzrjIf0RXVvQ3SOS71n2Lxmrc7CQ4fE7g8M1.mkNHONqci','P',NULL,1,1,29,'2026-07-14 18:54:32','2026-07-14 18:56:27',NULL,NULL),
(53,'Yayad Ginting, S.IP',NULL,'197102252008011007','guru11@simadis.sch','$2y$10$hnjoj8NApLnu/PPxn3EgFu/reNP3twMdnriVD.bcDMNxOwKCLazI6','P',NULL,1,1,11,'2026-07-14 18:54:32','2026-07-14 18:56:27',NULL,NULL),
(54,'Yola Robihatul Azhar,S.Pd',NULL,'199607042025212039','guru43@simadis.sch','$2y$10$reZHHoql.SAcjpYpWBgr5OetSNsbu1a6qkuyoDJueYin827AP3ATi','P',NULL,1,1,43,'2026-07-14 18:54:33','2026-07-14 18:56:27',NULL,NULL),
(55,'Yunda Walida P,S.Pd',NULL,'199103102025212133','guru49@simadis.sch','$2y$10$UOIsje2TDwhWjN3klV6Tw.ljJBMtrQtrlxyiAUxjfKGBFZFd4SsuO','P',NULL,1,1,49,'2026-07-14 18:54:33','2026-07-14 18:56:27',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-07-15 11:28:27
