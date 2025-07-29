-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2025 at 10:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_movie`
--

CREATE TABLE `add_movie` (
  `movie_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `genre` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `release_date` date NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `poster` varchar(255) NOT NULL,
  `banner` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `add_movie`
--

INSERT INTO `add_movie` (`movie_id`, `name`, `description`, `genre`, `language`, `duration`, `release_date`, `rating`, `poster`, `banner`) VALUES
(1, 'Devara - Part 1', 'The film`s backdrop is centered around the far and forgotten coastal lands of India.The people,or rather the villains,in the film neither fear death nor god and there is no sense of humanity among them. Devara changes this scenario in his inimitable style.', 'Action, Drama', 'Telugu', 175, '2024-09-27', 4.0, 'poster2.png', 'banner2.png'),
(2, 'Court: State vs A Nobody', 'A determined lawyer takes on a high-stakes case to defend a 19-year-old boy, challenging a system that has already deemed him guilty.', 'Drama, Thriller', 'Telugu', 149, '2025-03-14', 4.0, 'poster3.png', 'banner3.png'),
(3, 'Chhaava', 'After Chhatrapati Shivaji Maharaj`s death, the Mughals aim to expand into the Deccan, only to face his fearless son, Chhatrapati Sambhaji Maharaj. Chhaava, inspired by Shivaji Sawant`s novel, chronicles Chhatrapati Sambhaji Maharaj`s unwavering resistance against Aurangzeb, marked by courage, strategy, and betrayal.', 'Action, Drama, Historical', 'Hindi', 161, '2025-02-14', 2.0, 'poster4.png', 'banner4.png'),
(4, 'Gladiator II', 'From legendary director Ridley Scott, Gladiator II continues the epic saga of power, intrigue, and vengeance set in Ancient Rome. Years after witnessing the death of the revered hero Maximus at the hands of his uncle, Lucius (Paul Mescal) is forced to enter the Colosseum after his home is conquered by the tyrannical Emperors who now lead Rome with an iron fist. With rage in his heart and the future of the Empire at stake, Lucius must look to his past to find strength and honor to return the glory of Rome to its people.', ' Action , Adventure , Drama', 'English', 150, '2024-11-15', 1.0, 'poster5.png', 'banner5.png'),
(5, 'Meiyazhagan', 'Meiyazhagan is a Tamil movie starring Karthi and Arvind Swamy in prominent roles. It is written and directed by C. Prem Kumar.', 'Comedy, Drama', 'Tamil', 177, '2024-09-27', 5.0, 'poster6.png', 'banner6.png');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `show_id` int(11) NOT NULL,
  `seats_selected` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `booking_date` date DEFAULT NULL,
  `no_of_seats_selected` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `id`, `movie_id`, `show_id`, `seats_selected`, `cost`, `status`, `booking_date`, `no_of_seats_selected`) VALUES
(1, 1, 1, 1, 'C15,E13,N13,A11,A10,A9', 1430.00, 'confirmed', '2024-09-28', 6),
(2, 1, 1, 1, 'F7,F8,O9,A5', 730.00, 'confirmed', '2024-09-28', 4),
(3, 1, 1, 1, 'J4,M5,A14', 580.00, 'confirmed', '2024-09-28', 3),
(4, 1, 1, 1, 'B6,M9', 230.00, 'confirmed', '2024-09-28', 2),
(5, 1, 1, 2, 'A8,A9,A10,A11,A12,D16,E15', 2050.00, 'confirmed', '2024-09-29', 7);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `mode_of_payment` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `total`, `mode_of_payment`, `payment_status`) VALUES
(1, 1, 1532.00, 'debit/credit card', 'paid'),
(2, 2, 797.00, 'debit/credit card', 'paid'),
(3, 4, 272.00, 'debit/credit card', 'paid'),
(4, 5, 2183.00, 'debit/credit card', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`id`, `name`, `email`, `phone`, `password`) VALUES
(1, 'saketh', 'saketh@gmail.com', '9490000736', '$2y$10$6Go9qeFVBH7bHrmQyLHCtOnLARfYV6Sgmb2r4M84gY3I.bhlogtOe'),
(2, 'sathvik', 'sathvik@gmail.com', '6305297853', '$2y$10$FC12mjMRA.gPr/NheRaZaeDovyYQK85Il9mp3eNW7lFEM8JfJLkNC');

-- --------------------------------------------------------

--
-- Table structure for table `shows`
--

CREATE TABLE `shows` (
  `show_id` int(11) NOT NULL,
  `movie_id` int(11) DEFAULT NULL,
  `theatre_id` int(11) DEFAULT NULL,
  `show_time` time DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shows`
--

INSERT INTO `shows` (`show_id`, `movie_id`, `theatre_id`, `show_time`, `start_date`, `end_date`) VALUES
(1, 1, 1, '11:30:00', '2024-09-28', '2025-05-16'),
(2, 1, 2, '11:00:00', '2024-09-28', '2024-10-04');

-- --------------------------------------------------------

--
-- Table structure for table `theatres`
--

CREATE TABLE `theatres` (
  `theatre_id` int(11) NOT NULL,
  `theatre_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theatres`
--

INSERT INTO `theatres` (`theatre_id`, `theatre_name`, `location`, `address`) VALUES
(1, 'Hollywood Theatre', 'Guntur', 'Lakshmipuram'),
(2, 'AMB Theatre', 'Hyderabad', 'Sarath City Capital Mall, Gachibowli Road, Kondapur, Hyderabad - 500084, Telangana, India'),
(3, 'Cinepolis Multiplex', 'Hyderabad', 'Namishree Mall\\ Mantra Mall,4th Floor, Pillar No- 184 Survey No- 30/P UpparaPally Village · 5051234\r\nOn-site service'),
(4, 'bollywood Theatre', 'Guntur', 'Lakshmipuram road'),
(5, 'V Celluloids ', 'Mangalagiri', 'mangalagiri'),
(6, 'Capital Cinemas', 'Vijayawada', 'Trendset Mall, 40/1/56, MG Rd, Kala Nagar, Benz Circle, Vijayawada, Andhra Pradesh 520010');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_movie`
--
ALTER TABLE `add_movie`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `id` (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `show_id` (`show_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shows`
--
ALTER TABLE `shows`
  ADD PRIMARY KEY (`show_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `theatre_id` (`theatre_id`);

--
-- Indexes for table `theatres`
--
ALTER TABLE `theatres`
  ADD PRIMARY KEY (`theatre_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_movie`
--
ALTER TABLE `add_movie`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shows`
--
ALTER TABLE `shows`
  MODIFY `show_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `theatres`
--
ALTER TABLE `theatres`
  MODIFY `theatre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id`) REFERENCES `registration` (`id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `add_movie` (`movie_id`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`show_id`) REFERENCES `shows` (`show_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`);

--
-- Constraints for table `shows`
--
ALTER TABLE `shows`
  ADD CONSTRAINT `shows_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `add_movie` (`movie_id`),
  ADD CONSTRAINT `shows_ibfk_2` FOREIGN KEY (`theatre_id`) REFERENCES `theatres` (`theatre_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
