-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Jun 2026 pada 12.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sis_com`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `title`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'Balasan Ticket', 'Hery Darda Sudyana membalas ticket: Bsnsn', 1, '2026-05-31 13:18:16', '2026-05-31 13:18:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}', 1780343498);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `clients`
--

INSERT INTO `clients` (`id`, `user_id`, `name`, `company`, `email`, `address`, `created_at`, `updated_at`, `phone`) VALUES
(1, 3, 'Hery Darda Sudyana', 'SMKS PERMATA BUNDA I JAKARTA', 'smkspermatabunda581@gmail.com', 'Jl. Jamblang Raya No. 2 LM, Jakarta', '2026-05-27 10:57:06', '2026-05-28 05:15:15', '62895414744032'),
(2, 4, 'Melati hanifah Asfihani (Bocil)', 'BOCIL PLENGER YUHUU', 'melatiasfihani@gmail.com', 'BOCILL sajdlkasjd', '2026-06-01 01:35:20', '2026-06-01 02:00:49', '6285781573840');

-- --------------------------------------------------------

--
-- Struktur dari tabel `company_settings`
--

CREATE TABLE `company_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_phone` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `bank_jakarta` varchar(255) DEFAULT NULL,
  `bank_jakarta_name` varchar(255) DEFAULT NULL,
  `bank_mandiri` varchar(255) DEFAULT NULL,
  `bank_mandiri_name` varchar(255) DEFAULT NULL,
  `bank_bca` varchar(255) DEFAULT NULL,
  `bank_bca_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `company_email`, `company_phone`, `company_address`, `bank_jakarta`, `bank_jakarta_name`, `bank_mandiri`, `bank_mandiri_name`, `bank_bca`, `bank_bca_name`, `created_at`, `updated_at`) VALUES
(1, 'SIS.COM', NULL, NULL, NULL, '300-23-31341-1', 'TIO IRFAN ANTONI', '11800-1378-13222', 'TIO IRFAN ANTONI', '5310-74114-2', 'TIO IRFAN ANTONI', '2026-05-29 07:42:49', '2026-05-29 07:43:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` enum('unpaid','paid','partial','cancelled') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `vat_percent` decimal(5,2) NOT NULL DEFAULT 11.00,
  `vat` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cashback` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `invoices`
--

INSERT INTO `invoices` (`id`, `client_id`, `project_id`, `invoice_number`, `amount`, `due_date`, `status`, `notes`, `created_at`, `updated_at`, `subtotal`, `vat_percent`, `vat`, `service_fee`, `cashback`, `grand_total`) VALUES
(6, 1, 2, '#31052026-8226', 0.00, '2026-06-03', 'partial', NULL, '2026-05-31 11:59:23', '2026-05-31 12:03:09', 700000.00, 11.00, 77000.00, 10000.00, 10.00, 787000.00),
(13, 1, 6, '#01062026-7624', 0.00, '2026-06-09', 'unpaid', 'ooooo', '2026-05-31 22:58:30', '2026-06-01 00:22:47', 1980000.00, 20.00, 396000.00, 10000.00, 10.00, 2386000.00),
(15, 2, NULL, '#01062026-1179', 0.00, '2026-06-03', 'unpaid', 'BOCILLL PLENGERRR', '2026-06-01 01:40:53', '2026-06-01 01:40:53', 1000000.00, 11.00, 110000.00, 10000.00, 20.00, 1120000.00),
(16, 1, NULL, '#01062026-2689', 0.00, '2026-06-04', 'unpaid', 'OKEEE', '2026-06-01 02:39:43', '2026-06-01 02:39:43', 1000000.00, 11.00, 110000.00, 10000.00, 10.00, 1120000.00),
(17, 1, 6, '#01062026-9584', 0.00, '2026-06-04', 'unpaid', 'lajdlkajsd', '2026-06-01 02:49:40', '2026-06-01 02:49:40', 1231230.00, 11.00, 135435.30, 10000.00, 10.00, 1376665.30),
(18, 1, 2, '#01062026-9866', 0.00, '2026-06-08', 'unpaid', 'asdjaoidsh', '2026-06-01 02:56:32', '2026-06-01 02:56:32', 9138409.00, 11.00, 1005224.99, 10000.00, 10.00, 10153633.99),
(19, 2, 8, '#01062026-6805', 0.00, '2026-06-03', 'unpaid', 'PLENGERRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR', '2026-06-01 03:07:47', '2026-06-01 03:07:47', 100000.00, 11.00, 11000.00, 10000.00, 10.00, 121000.00),
(21, 1, 2, '#01062026-3114', 0.00, '2026-06-02', 'unpaid', 'hjgjhgjh', '2026-06-01 03:16:30', '2026-06-01 03:16:30', 5435431.00, 11.00, 597897.41, 10000.00, 0.00, 6043328.41),
(22, 1, 2, '#01062026-6930', 0.00, '2026-06-10', 'unpaid', 'uygjy', '2026-06-01 03:27:10', '2026-06-01 03:27:10', 53543.00, 11.00, 5889.73, 10000.00, 10.00, 69432.73);

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `duration_type` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `description`, `qty`, `price`, `total`, `created_at`, `updated_at`, `duration`, `duration_type`, `start_date`, `end_date`) VALUES
(6, 6, 'Domain smkspermatabunda', 1, 700000.00, 700000.00, '2026-05-31 11:59:23', '2026-05-31 11:59:23', 1, 'Tahun', '2026-06-01', '2027-06-01'),
(25, 13, 'Domain smkspermatabundadsa', 1, 1980000.00, 1980000.00, '2026-06-01 00:22:47', '2026-06-01 00:22:47', 1, 'Bulan', '2026-06-01', '2026-07-01'),
(26, 15, 'Domain sibocil.com', 1, 1000000.00, 1000000.00, '2026-06-01 01:40:53', '2026-06-01 01:40:53', 1, 'Tahun', '2026-06-01', '2027-06-01'),
(27, 16, 'BOCIL TEST', 1, 1000000.00, 1000000.00, '2026-06-01 02:39:43', '2026-06-01 02:39:43', 1, 'Tahun', '2026-06-02', '2027-06-02'),
(28, 17, '123123', 1, 1231230.00, 1231230.00, '2026-06-01 02:49:40', '2026-06-01 02:49:40', 1, 'Tahun', NULL, NULL),
(29, 18, 'p32u409283', 1, 9138409.00, 9138409.00, '2026-06-01 02:56:32', '2026-06-01 02:56:32', 1, 'Hari', NULL, NULL),
(30, 19, 'BOCILL CEK', 1, 100000.00, 100000.00, '2026-06-01 03:07:47', '2026-06-01 03:07:47', 1, 'Bulan', '2026-06-03', '2026-07-03'),
(32, 21, 'ituygh', 1, 5435431.00, 5435431.00, '2026-06-01 03:16:30', '2026-06-01 03:16:30', 1, 'Hari', NULL, NULL),
(33, 22, 'hjgjhg', 1, 53543.00, 53543.00, '2026-06-01 03:27:10', '2026-06-01 03:27:10', 1, 'Hari', '2026-06-03', '2026-06-04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_27_171942_create_permission_tables', 2),
(5, '2026_05_27_175224_create_clients_table', 3),
(6, '2026_05_27_175850_create_projects_table', 4),
(7, '2026_05_27_180843_create_invoices_table', 5),
(8, '2026_05_27_184931_create_payments_table', 6),
(9, '2026_05_27_191844_add_user_id_to_clients_table', 7),
(10, '2026_05_28_031514_create_project_files_table', 8),
(11, '2026_05_28_041033_add_phone_to_clients_table', 9),
(12, '2026_05_28_045055_create_invoice_items_table', 10),
(13, '2026_05_28_045204_add_tax_fields_to_invoices_table', 11),
(14, '2026_05_28_045832_add_duration_fields_to_invoice_items_table', 12),
(15, '2026_05_28_071840_add_cashback_to_invoices_table', 13),
(16, '2026_05_28_122701_create_notifications_table', 14),
(17, '2026_05_28_153409_create_tickets_table', 14),
(18, '2026_05_28_160734_create_ticket_messages_table', 15),
(19, '2026_05_29_143449_create_company_settings_table', 16),
(20, '2026_05_29_160314_add_sender_name_to_ticket_messages_table', 17),
(21, '2026_05_31_133055_create_admin_notifications_table', 18),
(22, '2026_06_01_060211_add_progress_to_projects_table', 19),
(23, '2026_06_01_063242_add_vat_percent_to_invoices_table', 20);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `client_id`, `title`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'Balasan Ticket', 'Ticket \"Bsnsn\" telah mendapatkan balasan dari Admin.', 1, '2026-05-31 10:38:52', '2026-05-31 10:39:06'),
(2, 1, 'Balasan Ticket', 'Ticket \"Bsnsn\" telah mendapatkan balasan dari Admin.', 1, '2026-05-31 10:43:10', '2026-05-31 10:43:11'),
(3, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 787.000', 1, '2026-05-31 11:59:23', '2026-05-31 12:52:09'),
(4, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.376.777', 1, '2026-05-31 12:28:01', '2026-05-31 12:52:09'),
(5, 1, 'Pembayaran Baru', 'Pembayaran invoice #31052026-7300 sebesar Rp 123.123', 1, '2026-05-31 12:31:59', '2026-05-31 12:52:09'),
(6, 1, 'Balasan Ticket', 'Ticket \"Bsnsn\" telah mendapatkan balasan dari Admin.', 1, '2026-05-31 13:00:44', '2026-05-31 13:00:54'),
(7, 1, 'Project Baru', 'Project baru dibuat: SMKS', 0, '2026-05-31 13:19:51', '2026-05-31 13:19:51'),
(8, 1, 'Project Baru', 'Project baru dibuat: SMKS', 0, '2026-05-31 13:20:37', '2026-05-31 13:20:37'),
(9, 1, 'Project Baru', 'Project baru dibuat: dqwd', 0, '2026-05-31 13:22:30', '2026-05-31 13:22:30'),
(10, 1, 'Project Baru', 'Project baru dibuat: dqwdq', 0, '2026-05-31 13:36:31', '2026-05-31 13:36:31'),
(11, 1, 'Pembayaran Dihapus', 'Data pembayaran untuk invoice #31052026-7300 telah diperbarui.', 0, '2026-05-31 14:13:26', '2026-05-31 14:13:26'),
(12, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.120.000', 0, '2026-05-31 14:46:00', '2026-05-31 14:46:00'),
(13, 1, 'Project Diperbarui', 'Project \"dqwdq\" telah diperbarui.', 0, '2026-05-31 22:48:18', '2026-05-31 22:48:18'),
(14, 1, 'Project Diperbarui', 'Project \"dqwdq\" telah diperbarui.', 0, '2026-05-31 22:51:30', '2026-05-31 22:51:30'),
(15, 1, 'Project Diperbarui', 'Project \"dqwdq\" telah diperbarui.', 0, '2026-05-31 22:53:14', '2026-05-31 22:53:14'),
(16, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.097.800', 0, '2026-05-31 22:58:30', '2026-05-31 22:58:30'),
(17, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 138.450', 0, '2026-05-31 23:15:57', '2026-05-31 23:15:57'),
(18, 2, 'Project Baru', 'Project baru dibuat: BOCIL PROJECT', 0, '2026-06-01 01:39:45', '2026-06-01 01:39:45'),
(19, 2, 'Project Diperbarui', 'Project \"BOCIL PROJECT\" telah diperbarui.', 0, '2026-06-01 01:40:02', '2026-06-01 01:40:02'),
(20, 2, 'Project Diperbarui', 'Project \"BOCIL PROJECT\" telah diperbarui.', 0, '2026-06-01 01:40:11', '2026-06-01 01:40:11'),
(21, 2, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.120.000', 0, '2026-06-01 01:40:53', '2026-06-01 01:40:53'),
(22, 2, 'Project Baru', 'Project baru dibuat: alsdaklsj', 0, '2026-06-01 02:14:08', '2026-06-01 02:14:08'),
(23, 2, 'Project Diperbarui', 'Project \"alsdaklsj\" telah diperbarui.', 0, '2026-06-01 02:14:37', '2026-06-01 02:14:37'),
(24, 2, 'Project Diperbarui', 'Project \"alsdaklsj\" telah diperbarui.', 0, '2026-06-01 02:15:13', '2026-06-01 02:15:13'),
(25, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.120.000', 0, '2026-06-01 02:39:43', '2026-06-01 02:39:43'),
(26, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 1.376.665', 0, '2026-06-01 02:49:40', '2026-06-01 02:49:40'),
(27, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 10.153.634', 0, '2026-06-01 02:56:32', '2026-06-01 02:56:32'),
(28, 2, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 121.000', 0, '2026-06-01 03:07:47', '2026-06-01 03:07:47'),
(29, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 9.694.730', 0, '2026-06-01 03:11:51', '2026-06-01 03:11:51'),
(30, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 6.043.328', 0, '2026-06-01 03:16:30', '2026-06-01 03:16:30'),
(31, 1, 'Invoice Baru', 'Invoice baru telah dibuat dengan nominal Rp 69.433', 0, '2026-06-01 03:27:10', '2026-06-01 03:27:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `amount`, `payment_date`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(2, 6, 700000.00, '2026-05-31', 'Transfer Bank', 'kkk', '2026-05-31 12:03:09', '2026-05-31 12:03:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deadline` date DEFAULT NULL,
  `status` enum('pending','progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `progress` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `projects`
--

INSERT INTO `projects` (`id`, `client_id`, `title`, `description`, `budget`, `deadline`, `status`, `progress`, `created_at`, `updated_at`) VALUES
(2, 1, 'SPMB Online', 'Pendaftara SPMB Online untuk sekolah Gratis pada SMKS PERMATA BUNDA I', 3000000.00, '2026-06-05', 'progress', 0, '2026-05-29 08:27:41', '2026-05-29 08:27:41'),
(6, 1, 'dqwdq', 'oke progress', 3000000.00, '2026-06-04', 'progress', 0, '2026-05-31 13:36:31', '2026-05-31 22:48:18'),
(8, 2, 'alsdaklsj', ';lasjldj', 30000000.00, '2026-06-04', 'completed', 100, '2026-06-01 02:14:08', '2026-06-01 02:15:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `project_files`
--

CREATE TABLE `project_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `project_files`
--

INSERT INTO `project_files` (`id`, `project_id`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
(3, 8, 'Kop Surat PB.png', 'project-files/LjoYkc1u09UkuiKZF8NgBupaSjISRfMTcVltT8Sf.png', '2026-06-01 02:14:56', '2026-06-01 02:14:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-05-27 10:22:59', '2026-05-27 10:22:59'),
(2, 'admin', 'web', '2026-05-27 10:22:59', '2026-05-27 10:22:59'),
(3, 'client', 'web', '2026-05-27 10:22:59', '2026-05-27 10:22:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0exihAQGlI3FCJOhnpdMBHau9ZxM9wjau2EV6N3g', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHJ5RzB5aERPcWNBdzdybVVGRUxQOW9jdXBmdExUazBoOU50dTdFSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHA6Ly9uZWNrbGFjZS1tYW55LWhvcGUtYmluZ28udHJ5Y2xvdWRmbGFyZS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1779966260),
('0upsf1vzNnYH20mmDXgDQxIRbQooIUawaKumpieU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWdHNGZXSFBUVTdDRzJjVFdFekJSZHNZTjRsTVFjVDRVT0lJYnhxMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly9zdHVjay1jcnVpc2VzLWphbnVhcnktY29udGFpbmVyLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779966000),
('c30mbDqYgRIwt0z5QqnKuGlLP4WuKtxcB9OSvzog', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidHdvN1hGdk9KcGlTWkNlMEdacHBZY3g4dHhUczMzUHBsR0xYTWZTciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1779966823),
('Tg2KlBIcP99JDP3V5HaiHVMzkqj788DqVn2HSnPp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZWhpNk9Ocjd5NFJ5S2hPZ2ZmVFBQckdaTUZ6cXNvYkpSSzZmbVZHTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9uZWNrbGFjZS1tYW55LWhvcGUtYmluZ28udHJ5Y2xvdWRmbGFyZS5jb20vbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO319', 1779966781),
('WYkfBg5hDD8wLbmN7E1Vlh0dXLLxzPlV4BY8VzKJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoidEhFTkp4S2REZ3NkYmRoa3dFU3Z6bHQ4dnl4N1A2eEt0VFRTQko4OSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779966791),
('XhAn125CVvGPkZkuf8UuLfhmU5mOhJJmtJNH1Rmm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiaXRzcWR1a0YwcVA1ckk3a3JlVW1zd0oxdjVkUnc5S1JoYmUyd0lpciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779966914),
('XsuQRuzP90eCUblroUkkMYhweoardy6cJzP20anD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkRXbjVsSTRGMEU4bUc4MGdLdG1McFF6TnVlZXZRSk85eUwyR1QySSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9uZWNrbGFjZS1tYW55LWhvcGUtYmluZ28udHJ5Y2xvdWRmbGFyZS5jb20vbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779966763);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `client_id`, `project_id`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Revisi disini', 'Tolong revisi untuk inputan TKA', 'progress', '2026-05-28 09:04:05', '2026-05-28 09:28:15'),
(2, 1, 2, 'OKE', 'Lanjutkan', 'closed', '2026-05-29 08:46:02', '2026-05-29 11:13:47'),
(3, 1, 2, 'Bsnsn', 'Jsnsn', 'open', '2026-05-29 11:07:57', '2026-05-31 13:04:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `sender_type` varchar(255) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `sender_type`, `sender_name`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', NULL, 'baik segera', '2026-05-28 09:28:15', '2026-05-28 09:28:15'),
(2, 2, 'client', NULL, 'OKE', '2026-05-29 09:00:19', '2026-05-29 09:00:19'),
(3, 2, 'client', 'Hery Darda Sudyana', 'OKE', '2026-05-29 09:09:07', '2026-05-29 09:09:07'),
(4, 2, 'admin', 'Super Admin', 'Siap apa', '2026-05-29 09:10:38', '2026-05-29 09:10:38'),
(5, 2, 'admin', 'Super Admin', 'gassken', '2026-05-29 09:11:11', '2026-05-29 09:11:11'),
(6, 2, 'client', 'Hery Darda Sudyana', 'hkjhjh', '2026-05-29 09:21:09', '2026-05-29 09:21:09'),
(7, 2, 'admin', 'Super Admin', 'asdlkasjd', '2026-05-29 09:34:30', '2026-05-29 09:34:30'),
(8, 2, 'client', 'Hery Darda Sudyana', 'hgchgc', '2026-05-29 09:46:21', '2026-05-29 09:46:21'),
(9, 2, 'client', 'Hery Darda Sudyana', 'asdkmalksdm', '2026-05-29 09:58:10', '2026-05-29 09:58:10'),
(10, 2, 'admin', 'Super Admin', 'amsldkmkldm', '2026-05-29 10:04:20', '2026-05-29 10:04:20'),
(11, 2, 'admin', 'Super Admin', 'paskdpok', '2026-05-29 10:05:15', '2026-05-29 10:05:15'),
(12, 2, 'admin', 'Super Admin', 'asdas', '2026-05-29 10:07:50', '2026-05-29 10:07:50'),
(13, 2, 'admin', 'Super Admin', 'asdasd', '2026-05-29 10:08:42', '2026-05-29 10:08:42'),
(14, 2, 'client', 'Hery Darda Sudyana', 'Ggfd', '2026-05-29 10:27:48', '2026-05-29 10:27:48'),
(15, 2, 'client', 'Hery Darda Sudyana', 'Ffx', '2026-05-29 10:28:52', '2026-05-29 10:28:52'),
(16, 2, 'client', 'Hery Darda Sudyana', 'Hchc', '2026-05-29 10:40:33', '2026-05-29 10:40:33'),
(17, 2, 'client', 'Hery Darda Sudyana', 'Yfx', '2026-05-29 10:43:04', '2026-05-29 10:43:04'),
(18, 3, 'admin', 'Super Admin', 'Oke', '2026-05-29 11:08:52', '2026-05-29 11:08:52'),
(19, 3, 'client', 'Hery Darda Sudyana', 'Gxgx', '2026-05-29 11:09:07', '2026-05-29 11:09:07'),
(20, 3, 'client', 'Hery Darda Sudyana', 'Ggff', '2026-05-29 12:17:21', '2026-05-29 12:17:21'),
(21, 3, 'admin', 'Super Admin', 'askdmlkasmd', '2026-05-29 12:17:41', '2026-05-29 12:17:41'),
(22, 3, 'client', 'Hery Darda Sudyana', 'Vgyg', '2026-05-29 12:19:17', '2026-05-29 12:19:17'),
(23, 3, 'admin', 'Super Admin', 'askldnalnsd', '2026-05-29 12:22:11', '2026-05-29 12:22:11'),
(24, 3, 'admin', 'Super Admin', 'asdnalksdn', '2026-05-29 13:22:46', '2026-05-29 13:22:46'),
(25, 3, 'admin', 'Super Admin', 'jknknln', '2026-05-31 05:09:14', '2026-05-31 05:09:14'),
(26, 3, 'admin', 'Super Admin', 'lknlkn', '2026-05-31 05:09:34', '2026-05-31 05:09:34'),
(27, 3, 'admin', 'Super Admin', 'asldnaslkdn', '2026-05-31 06:43:43', '2026-05-31 06:43:43'),
(28, 3, 'admin', 'Super Admin', 'asndkans', '2026-05-31 06:44:00', '2026-05-31 06:44:00'),
(29, 3, 'client', 'Hery Darda Sudyana', 'Hshsh', '2026-05-31 06:45:13', '2026-05-31 06:45:13'),
(30, 3, 'client', 'Hery Darda Sudyana', 'Hshsh', '2026-05-31 06:45:14', '2026-05-31 06:45:14'),
(31, 3, 'admin', 'Super Admin', 'asdas', '2026-05-31 09:56:17', '2026-05-31 09:56:17'),
(32, 3, 'admin', 'Super Admin', 'nkjnkjn', '2026-05-31 09:56:56', '2026-05-31 09:56:56'),
(33, 3, 'admin', 'Super Admin', 'askdnaksjdn', '2026-05-31 10:09:04', '2026-05-31 10:09:04'),
(34, 3, 'admin', 'Super Admin', 'asdknaskj', '2026-05-31 10:09:26', '2026-05-31 10:09:26'),
(35, 3, 'admin', 'Super Admin', 'klxlcnvs', '2026-05-31 10:38:52', '2026-05-31 10:38:52'),
(36, 3, 'admin', 'Super Admin', 'asdnasdn', '2026-05-31 10:43:10', '2026-05-31 10:43:10'),
(37, 3, 'client', 'Hery Darda Sudyana', 'Kamss', '2026-05-31 11:01:24', '2026-05-31 11:01:24'),
(38, 3, 'client', 'Hery Darda Sudyana', '57rur', '2026-05-31 11:01:55', '2026-05-31 11:01:55'),
(39, 3, 'client', 'Hery Darda Sudyana', 'Kamsnas', '2026-05-31 12:51:56', '2026-05-31 12:51:56'),
(40, 3, 'admin', 'Super Admin', 'asddqwd', '2026-05-31 13:00:44', '2026-05-31 13:00:44'),
(41, 3, 'client', 'Hery Darda Sudyana', 'Chcbc', '2026-05-31 13:04:07', '2026-05-31 13:04:07'),
(42, 3, 'client', 'Hery Darda Sudyana', 'Chcbc', '2026-05-31 13:04:10', '2026-05-31 13:04:10'),
(43, 3, 'client', 'Hery Darda Sudyana', 'Ba. Zve', '2026-05-31 13:18:16', '2026-05-31 13:18:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'tioirfanantoni@gmail.com', NULL, '$2y$12$vUyeT1FnIze53rrA/jTWne7zX5nJMs.ax.oc4/iJmL40ssnJql63i', 'KMexQxlbqpTBVrxE3SHyz5MW3t7IWosCbHWVkEjmwWYHyHmvo1fmPRT3UWGe', '2026-05-27 10:33:45', '2026-05-27 10:33:45'),
(3, 'Hery Darda Sudyana', 'client1@sis.com', NULL, '$2y$12$hYbkzrRxcT4fM226my.spukdavlO1DkQ8mLV8QBmajStdWDJ.UzKe', NULL, '2026-05-27 11:54:35', '2026-05-27 11:54:35'),
(4, 'Melati hanifah Asfihani (Bocil)', 'melatiasfihani@gmail.com', NULL, '$2y$12$vjbQRA1TCkMYN/B5nKu0c.zNEgvub9jC6ykkFfkQccIBdQvYD95uG', NULL, '2026-05-31 23:37:56', '2026-06-01 00:45:15');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_client_id_foreign` (`client_id`),
  ADD KEY `invoices_project_id_foreign` (`project_id`);

--
-- Indeks untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_invoice_id_foreign` (`invoice_id`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_files_project_id_foreign` (`project_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_client_id_foreign` (`client_id`),
  ADD KEY `tickets_project_id_foreign` (`project_id`);

--
-- Indeks untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_messages_ticket_id_foreign` (`ticket_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `project_files`
--
ALTER TABLE `project_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `project_files_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
