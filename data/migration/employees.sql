-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2015 at 08:53 AM
-- Server version: 5.5.32
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cereli_employee_time_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(25) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `position` varchar(50) NOT NULL,
  `leave_limit` varchar(50) NOT NULL,
  `unpaid_leave_limit` varchar(50) NOT NULL,
  `absences` varchar(50) NOT NULL,
  `late` text NOT NULL,
  `overtime` text NOT NULL,
  `record_status` char(15) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=120 ;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `emp_id`, `fullname`, `position`, `leave_limit`, `unpaid_leave_limit`, `absences`, `late`, `overtime`, `record_status`) VALUES
(1, '280001', 'Cerwin M. Lavador', 'Manager', '1', '2', '1', '', '', 'Active'),
(2, '280002', 'Maricris A. Lavador', 'Company Nurse', '1', '2', '0', '0:10', '02:40', 'Active'),
(3, '280003', 'Teresa Navaro', 'IT Specialist', '1', '3', '2', '0:50', '01:30', 'Active'),
(4, '280004', 'Teresa1 Navaro', '', '', '', '', '', '', 'Inactive'),
(5, '28059', 'AbayaJasondup', '', '', '', '', '18:27:00', '08:00', 'Active'),
(6, '15083', 'AcostaPaul', '', '', '', '', '', '', 'Inactive'),
(7, '28014', 'BacolodMarlon', '', '', '', '', '', '', 'Active'),
(8, '28015', 'Baguio Nelson', '', '', '', '', '12:04', '09:27', 'Active'),
(9, '28011', 'BandalanEduardo', '', '', '', '', '02:54:00', '08:00', 'Active'),
(10, '15036', 'BendanilloRogeli', '', '', '', '', '04:48:00', '08:00', 'Active'),
(11, '15078', 'Bontilao Arnel', '', '', '', '', '00:00:00', '00:00', 'Active'),
(12, '28026', 'BuotFranklin', '', '', '', '', '', '', 'Active'),
(13, '28052', 'CelmarJaime', '', '', '', '', '', '', 'Active'),
(14, '28044', 'Cortes Aldwin', '', '', '', '', '00:02:00', '07:53', 'Active'),
(15, '28035', 'CuerboGeorge', '', '', '', '', '', '', 'Active'),
(16, '280059', 'DagatanRey', '', '', '', '', '', '', 'Active'),
(17, '15052', 'DampiosEric', '', '', '', '', '', '', 'Active'),
(18, '28024', 'DianoJoel', '', '', '', '', '', '', 'Active'),
(19, '28039', 'EyanaEric', '', '', '', '', '', '', 'Active'),
(20, '15049', 'IbayaEdmund', '', '', '', '', '', '', 'Active'),
(21, '28054', 'LanguidoRommel', '', '', '', '', '', '', 'Active'),
(22, '15101', 'CruzJoarie', '', '', '', '', '01:28:00', '08:00', 'Active'),
(23, '28008', 'LavadorNice', '', '', '', '', '', '', 'Active'),
(24, '28006', 'LavadorRaquel', '', '', '', '', '', '', 'Active'),
(25, '15098', 'LumagbasEujean', '', '', '', '', '', '', 'Active'),
(26, '15097', 'MadridArnel', '', '', '', '', '', '', 'Active'),
(27, '15076', 'MahilumTeodulo', '', '', '', '', '', '', 'Active'),
(28, '15070', 'Mahusay Abraham', '', '', '', '', '00:00:00', '07:51', 'Active'),
(29, '15059', 'MahusayArnel', '', '', '', '', '', '', 'Active'),
(30, '28060', 'MondidoAnmar', '', '', '', '', '', '', 'Active'),
(31, '28003', 'MondidoAnTon', '', '', '', '', '', '', 'Active'),
(32, '28067', 'MonteronJoevanie', '', '', '', '', '', '', 'Active'),
(33, '15093', 'MoraldeMarlon', '', '', '', '', '', '', 'Active'),
(34, '15030', 'PanalesProilezal', '', '', '', '', '', '', 'Active'),
(35, '28055', 'RojoRico', '', '', '', '', '', '', 'Active'),
(36, '15060', 'RosalesReY', '', '', '', '', '01:16:00', '08:00', 'Inactive'),
(37, '28045', 'SaberonRomel', '', '', '', '', '19:59:00', '08:00', 'Inactive'),
(38, '28027', 'SamblasinioRogel', '', '', '', '', '', '', 'Active'),
(39, '15065', 'SerdanReynante', '', '', '', '', '', '', 'Active'),
(40, '15068', 'TanggolJordan', '', '', '', '', '14:06:00', '08:00', 'Inactive'),
(41, '15039', 'TorresJeffrey', '', '', '', '', '', '', 'Active'),
(42, '15064', 'VillahermosaJonn', '', '', '', '', '03:15:00', '08:00', 'Inactive'),
(43, '28057', 'VillamorKenneth', '', '', '', '', '', '', 'Active'),
(44, '28030', 'ZabalaNelson', '', '', '', '', '', '', 'Active'),
(45, '15003', 'SemillaAnthony', '', '', '', '', '', '', 'Active'),
(46, '15048', 'LavadorBernary', '', '', '', '', '08:00:00', '08:00', 'Inactive'),
(47, '15032', 'ANTIVOLEO', '', '', '', '', '23:08:00', '08:00', 'Active'),
(48, '28007', 'GELIGCRISTOPHER', '', '', '', '', '', '', 'Active'),
(49, '21036', 'LavadorCrwn', '', '', '', '', '00:00:00', '00:00', 'Inactive'),
(50, '15085', 'MAGALEKIRBY', '', '', '', '', '', '', 'Active'),
(51, '28032', 'MARCAMANOLITO', '', '', '', '', '', '', 'Active'),
(52, '28033', 'MEJARESCHRISTOPH', '', '', '', '', '', '', 'Active'),
(53, '15001', 'SABALRICHARD', '', '', '', '', '', '', 'Active'),
(54, '29030', 'Celso', '', '', '', '', '00:00:00', '00:00', 'Inactive'),
(55, '15067', 'PEPITONOEL', '', '', '', '', '23:08:00', '08:00', 'Inactive'),
(56, '28047', 'BABATUANRODEN', '', '', '', '', '', '', 'Active'),
(57, '15094', 'CALISOBRIAN', '', '', '', '', '07:42:00', '08:00', 'Inactive'),
(58, '15069', 'ENCARGUEZJESSIE', '', '', '', '', '', '', 'Active'),
(59, '28064', 'GEMALBRIAN', '', '', '', '', '', '', 'Active'),
(60, '28020', 'SIOCOROBERTO', '', '', '', '', '', '', 'Active'),
(61, '15063', 'VILLAMORRAYMOND', '', '', '', '', '08:00:00', '08:00', 'Inactive'),
(62, '28002', 'LAVADORRICO', '', '', '', '', '', '', 'Active'),
(63, '1602', 'RecosanaPatrick', '', '', '', '', '', '', 'Active'),
(64, '1627', 'SuperableArnill', '', '', '', '', '', '', 'Active'),
(66, '15031', '', '', '', '', '', '00:00:00', '00:00', 'Inactive'),
(67, '30061', 'SASOJAZON', '', '', '', '', '', '', 'Active'),
(68, '1630', 'SerdanJude', '', '', '', '', '', '', 'Active'),
(69, '1600', 'OriasSammy', '', '', '', '', '02:56:00', '23:03', 'Active'),
(70, '1604', 'BaguhinAriel', '', '', '', '', '00:00:00', '00:00', 'Active'),
(71, '1611', 'MagalsoJoseph', '', '', '', '', '03:43:00', '08:00', 'Active'),
(72, '1615', 'MejioLimbird', '', '', '', '', '00:00:00', '08:00', 'Active'),
(73, '1619', 'MamoloEmmanuel', '', '', '', '', '13:37:00', '18:30', 'Active'),
(74, '1621', 'MierFelix', '', '', '', '', '00:00:00', '08:00', 'Active'),
(75, '1631', 'AlinoBrian', '', '', '', '', '09:27:00', '13:45', 'Active'),
(76, '1637', 'ArcenalRocky', '', '', '', '', '01:26:00', '08:00', 'Active'),
(77, '1649', 'CarulasanJerome', '', '', '', '', '04:01:00', '18:51', 'Active'),
(78, '1651', 'LuardoNiel', '', '', '', '', '00:00:00', '08:00', 'Active'),
(79, '1652', 'DapetillaCyril', '', '', '', '', '05:51:00', '08:00', 'Active'),
(80, '1653', 'TauthoNolly', '', '', '', '', '00:00:00', '08:00', 'Active'),
(81, '1654', 'PadayogdogAriel', '', '', '', '', '16:20:00', '16:00', 'Active'),
(82, '1655', 'PactoLouiege', '', '', '', '', '00:00:00', '08:00', 'Active'),
(83, '1656', 'NunezSamuelson', '', '', '', '', '00:40:00', '08:00', 'Active'),
(84, '1664', 'BoquilaFlorito', '', '', '', '', '12:02:00', '19:25', 'Active'),
(85, '5001', 'WenceslaoArnel', '', '', '', '', '06:37:00', '08:00', 'Active'),
(86, '5002', 'AbanganShiela', '', '', '', '', '05:03:00', '08:00', 'Active'),
(87, '15005', 'MagnoJohnpaul', '', '', '', '', '00:00:00', '07:09', 'Active'),
(88, '15015', 'PalisJames', '', '', '', '', '01:02:00', '17:08', 'Active'),
(89, '15028', 'GeonzonJuneRyan', '', '', '', '', '00:00:00', '08:00', 'Active'),
(90, '15029', 'GolbenRex', '', '', '', '', '22:30:00', '08:00', 'Active'),
(91, '15044', 'LozanoEusebio', '', '', '', '', '16:00:00', '08:00', 'Active'),
(92, '15050', 'NoynayRolando', '', '', '', '', '00:00:00', '16:29', 'Active'),
(93, '15057', 'SomosaVirgo', '', '', '', '', '17:07:00', '17:19', 'Active'),
(94, '15075', 'JuganMelvin', '', '', '', '', '04:59:00', '23:31', 'Active'),
(95, '15080', 'AguilarJeffrey', '', '', '', '', '00:23:00', '10:11', 'Active'),
(96, '15100', 'BorromeoJemson', '', '', '', '', '16:50:00', '19:51', 'Active'),
(97, '15104', 'BlazaSulpecio', '', '', '', '', '01:06:00', '15:29', 'Active'),
(98, '28004', 'GaradoGilbert', '', '', '', '', '00:10:00', '16:34', 'Active'),
(99, '28061', 'LamorinPablo', '', '', '', '', '08:00:00', '08:00', 'Active'),
(100, '28079', 'FuentesLeo', '', '', '', '', '03:12:00', '08:00', 'Active'),
(101, '30004', 'PianoJunbert', '', '', '', '', '11:49:00', '08:00', 'Active'),
(102, '30011', 'OlidJeffrey', '', '', '', '', '17:38:00', '08:00', 'Active'),
(103, '30028', 'AbayaJason', '', '', '', '', '00:57:00', '03:45', 'Active'),
(104, '30062', 'AguilarRuben', '', '', '', '', '01:32:00', '15:42', 'Active'),
(105, '3006300000', 'superableduplicate', '', '', '', '', '00:00:00', '00:00', 'Inactive'),
(106, '30064', 'TudtudPaul', '', '', '', '', '00:41:00', '15:07', 'Active'),
(107, '30065', 'SalembotLemuel', '', '', '', '', '03:12:00', '16:27', 'Active'),
(108, '30066', 'PalacioRamilo', '', '', '', '', '20:45:00', '18:10', 'Active'),
(109, '30067', 'MatugasEmman', '', '', '', '', '04:39:00', '04:50', 'Active'),
(110, '1647', 'AlivioGemwin', '', '', '', '', '', '', 'Active'),
(111, '1644', 'ArcillaNelmar', '', '', '', '', '', '', 'Active'),
(112, '30108', 'BarteHendrell', '', '', '', '', '', '', 'Active'),
(113, '30076', 'CaniasPeter', '', '', '', '', '', '', 'Active'),
(114, '30110', 'LabitadWendill', '', '', '', '', '', '', 'Active'),
(115, '30109', 'LavadorLester', '', '', '', '', '', '', 'Active'),
(116, '30107', 'VistoJoepet', '', '', '', '', '', '', 'Active'),
(117, '30060', 'NionesRomeo', '', '', '', '', '01:49:00', '08:00', 'Active'),
(118, '30009', 'SabrosoJije', '', '', '', '', '00:18:00', '17:02', 'Active'),
(119, '', '', '', '', '', '', '', '', 'Active');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
