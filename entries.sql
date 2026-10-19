-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 16, 2026 at 12:16 PM
-- Server version: 8.0.17
-- PHP Version: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cyberfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE `entries` (
  `id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `title` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `summary` varchar(300) DEFAULT '',
  `body` mediumtext,
  `image` varchar(120) DEFAULT '',
  `tags` varchar(200) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `level` varchar(20) DEFAULT '',
  `published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `entries`
--

INSERT INTO `entries` (`id`, `type`, `title`, `slug`, `summary`, `body`, `image`, `tags`, `link`, `level`, `published`, `created_at`) VALUES
(4, 'project', 'E-Commerce Platform for KD Clothing Pvt Ltd', 'e-commerce-platform-for-kd-clothing-pvt-ltd-cc1b', 'Web Dev', 'To support KD Clothing\'s digital transformation, this project developed a scalable, budget-friendly e-commerce platform using PHP and MySQL. Following the Waterfall SDLC model, the system automates sales, secures transactions, and streamlines data management through features like cart management and admin controls. Rigorous testing confirmed its reliability and effectiveness for small business success.', 'f1400ecaa6b34c54.jpg', '#HTML #CSS #JAVASCRIPT #PHP #SQL', '', '', 1, '2026-10-16 11:59:13'),
(5, 'project', 'QuizVerse - A Mobile Quiz Application', 'quizverse-a-mobile-quiz-application-ed20', 'App Dev', 'QuizVerse is an Android-based mobile application designed to gamify learning through timed, interactive quizzes across categories like Science, History, Sports, and Technology. Built with Java in Android Studio, it features personalized user tracking, instant visual feedback, high-score records, and celebratory animations, delivering an engaging, intuitive platform for lifelong learning.', '7c5228596415db9c.jpg', '#Android Studio #Flutter', '', '', 1, '2026-10-16 12:00:13'),
(6, 'project', 'Data Communication and Network System', 'data-communication-and-network-system-f38f', 'Networking', 'This study outlines a secure, scalable network design connecting a financial institution\'s SUNTEC City headquarters and Marina Square teaching facilities. Adhering to NIST and Cisco best practices, the architecture integrates multi-site VPN connectivity, IP subnetting, biometric attendance monitoring, and secure Wi-Fi for staff and students, ensuring robust and reliable campus-wide operations.', 'de5f03eadd269a93.jpg', '', '', '', 1, '2026-10-16 12:01:15'),
(7, 'project', 'Practical Vulnerability Testing and Report', 'practical-vulnerability-testing-and-report-20de', 'Cybersecurity', 'Ethical hacking and vulnerability assessments safeguard critical systems by identifying security flaws before malicious exploitation occurs. Using Kali Linux and Metasploitable 2 in a controlled virtual environment, this study evaluated the Samba CVE-2007-2447 vulnerability using Nmap reconnaissance and Metasploit. The findings highlight key operational risks and provide actionable mitigation strategies.', '8606f5215e324b3a.jpg', '#Metasploitable 2 #Nmap #Kali Linux', '', '', 1, '2026-10-16 12:02:13'),
(8, 'project', 'Project | Zero-Trust Hybrid Cloud Security via Identity-Aware Proxy', 'project-zero-trust-hybrid-cloud-security-via-identity-aware-proxy-e916', 'Cloud Security (Continue Final Project)', 'Addressing perimeter-based vulnerabilities, this project proposes replacing traditional castle-and-moat security with a Zero Trust Architecture (ZTA). Implemented in a hybrid AWS/Azure environment using Micro-segmentation and Identity-Aware Proxies (IAP), the system continuously authenticates user identity and device health per request, effectively neutralizing credential theft and preventing lateral network movement.', '0813e65f186373f6.jpg', '#AWS Verified Access #AWS/Azure Network Security', '', '', 1, '2026-10-16 12:02:53'),
(9, 'project', 'Design and Evaluation of a New Classical Encryption Cipher', 'design-and-evaluation-of-a-new-classical-encryption-cipher-ea3f', 'Network & Cloud Security', 'Cryptography protects sensitive information by transforming readable messages into encrypted data. This project developed and evaluated the Hybrid Matrix Shift Cipher (HMSC), an educational encryption system combining mathematical character transformation and matrix-based transposition. Using Python, Windows, Kali Linux, Wireshark, and a controlled Man-in-the-Middle (MITM) proxy, the study examined encryption, decryption, network traffic interception, and security limitations.', 'c1c9aa9cd6d4c2e0.png', '#Cybersecurity #Cryptography #Python #KaliLinux #Wireshark #NetworkSecurity', '', '', 1, '2026-10-16 12:10:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `type` (`type`,`published`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
