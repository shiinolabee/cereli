-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2015 at 08:52 AM
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
CREATE DATABASE IF NOT EXISTS `cereli_employee_time_manager` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `cereli_employee_time_manager`;

-- --------------------------------------------------------

--
-- Table structure for table `company_details`
--

CREATE TABLE IF NOT EXISTS `company_details` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(50) NOT NULL,
  `company_address` varchar(150) NOT NULL,
  `contact_no` varchar(40) NOT NULL,
  `system_version` text NOT NULL,
  `company_status` char(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `employee_activities`
--

CREATE TABLE IF NOT EXISTS `employee_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` text NOT NULL,
  `date_committed` datetime NOT NULL,
  `remarks` text NOT NULL,
  `status` char(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=428 ;

-- --------------------------------------------------------

--
-- Table structure for table `employee_reports`
--

CREATE TABLE IF NOT EXISTS `employee_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `emp_ids` text NOT NULL,
  `status` char(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `emp_time_record`
--

CREATE TABLE IF NOT EXISTS `emp_time_record` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(25) NOT NULL COMMENT 'foreign key for employee table',
  `date_attended` date NOT NULL,
  `time_1` time NOT NULL,
  `time_2` time NOT NULL,
  `time_3` time NOT NULL,
  `time_4` time NOT NULL,
  `time_5` time NOT NULL,
  `time_6` time NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `emp_id` (`emp_id`),
  KEY `date_attended` (`date_attended`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17747 ;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
