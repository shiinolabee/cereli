-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2015 at 08:55 AM
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
-- Table structure for table `time_record_rules`
--

CREATE TABLE IF NOT EXISTS `time_record_rules` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `department_id` mediumint(9) NOT NULL COMMENT 'foreign key to departments table',
  `start_shift_time` time NOT NULL,
  `end_shift_time` time NOT NULL,
  `required_hours_rendered` time NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` char(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `time_record_rules`
--

INSERT INTO `time_record_rules` (`id`, `department_id`, `start_shift_time`, `end_shift_time`, `required_hours_rendered`, `type`, `status`) VALUES
(1, 1, '08:00:00', '17:30:00', '08:00:00', 'default', 'Active');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
