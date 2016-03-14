-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 23, 2016 at 12:55 PM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `shipping_development`
--

-- --------------------------------------------------------

--
-- Table structure for table `ship_details`
--

CREATE TABLE IF NOT EXISTS `ship_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ShipName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `companyDetailsId` int(11) DEFAULT NULL,
  `Description` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7C0B3BE7FAE99B7` (`ShipName`),
  KEY `IDX_7C0B3BE7C8A54A33` (`companyDetailsId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `ship_details`
--

INSERT INTO `ship_details` (`id`, `ShipName`, `companyDetailsId`, `Description`) VALUES
(1, 'Azure', 3, 'sdfadsfas'),
(2, 'Spice', 3, 'About the spice ships'),
(3, 'Apple', 1, 'About the apple ship'),
(4, 'Rose', 5, 'dfsdfasdfa sdfsasd dsaf');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ship_details`
--
ALTER TABLE `ship_details`
  ADD CONSTRAINT `FK_7C0B3BE7C8A54A33` FOREIGN KEY (`companyDetailsId`) REFERENCES `company_details` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
