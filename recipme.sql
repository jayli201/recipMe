-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2020 at 09:10 PM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recipme`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergens`
--

CREATE TABLE `allergens` (
  `recipeID` int(11) NOT NULL,
  `allergen` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `allergens`
--

INSERT INTO `allergens` (`recipeID`, `allergen`) VALUES
(1, 'peanut');

-- --------------------------------------------------------

--
-- Table structure for table `areasofexperience`
--

CREATE TABLE `areasofexperience` (
  `username` varchar(40) NOT NULL,
  `area` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `areasofexperience`
--

INSERT INTO `areasofexperience` (`username`, `area`) VALUES
('mds2cf', 'baking');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `recipeID` int(11) NOT NULL,
  `category` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`recipeID`, `category`) VALUES
(1, 'lunch');

-- --------------------------------------------------------

--
-- Table structure for table `cookpincount`
--

CREATE TABLE `cookpincount` (
  `username` varchar(40) NOT NULL,
  `cookPinCount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dietaryrestrictions`
--

CREATE TABLE `dietaryrestrictions` (
  `recipeID` int(11) NOT NULL,
  `restriction` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dietaryrestrictions`
--

INSERT INTO `dietaryrestrictions` (`recipeID`, `restriction`) VALUES
(1, 'vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `foodies`
--

CREATE TABLE `foodies` (
  `username` varchar(40) NOT NULL,
  `favoriteFood` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `foodies`
--

INSERT INTO `foodies` (`username`, `favoriteFood`) VALUES
('alicehan', 'egg tarts'),
('jasminli', 'pineapple buns'),
('monicasandoval', 'tacos'),
('rebeccazhou', 'fried eggs and tomatoes');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `recipeID` int(11) NOT NULL,
  `ingredient` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`recipeID`, `ingredient`) VALUES
(1, 'bread'),
(1, 'jelly'),
(1, 'peanut butter');

-- --------------------------------------------------------

--
-- Table structure for table `pin`
--

CREATE TABLE `pin` (
  `recipeID` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `attempted` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pin`
--

INSERT INTO `pin` (`recipeID`, `username`, `attempted`) VALUES
(1, 'mds2cf', b'0'),
(1, 'rmz6yx', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipeID` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `recipeName` varchar(40) NOT NULL,
  `instructions` text NOT NULL,
  `instructionCount` int(11) NOT NULL,
  `country` varchar(40) NOT NULL,
  `cookingTime` int(11) NOT NULL,
  `recipePinCount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `recipeID` int(11) NOT NULL,
  `reviews` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`recipeID`, `reviews`) VALUES
(1, 'This is a mediocre PB&J recipe, rip my tastebuds'),
(1, 'Wow this is a really great PB&J recipe, 10/10 would recommend, a delectable snack');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `firstName` varchar(40) NOT NULL,
  `lastName` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `firstName`, `lastName`) VALUES
('alicehan', 'alicehan@gmail.com', 'dataBase123$', 'Alice', 'Han'),
('amh2sd', 'amh2sd@virginia.edu', 'abcD!123', 'Alice', 'Han'),
('jasminli', 'jasminli@gmail.com', 'pa$Sword123', 'Jay', 'Li'),
('jl6ww', 'jl6ww@virginia.edu', 'Password$', 'Jay', 'Li'),
('mds2cf', 'mds2cf@virginia.edu', '123%Pass', 'Monica', 'Sandoval-Vasquez'),
('monicasandoval', 'monicasandoval@gmail', '1#34pw56', 'Monica', 'Sandoval-Vasquez'),
('rebeccazhou', 'rebeccazhou@gmail.com', 'cS47^40', 'Rebecca', 'Zhou'),
('rmz6yx', 'rmz6yx@virginia.edu', 'pW@12340', 'Rebecca', 'Zhou');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allergens`
--
ALTER TABLE `allergens`
  ADD PRIMARY KEY (`recipeID`,`allergen`);

--
-- Indexes for table `areasofexperience`
--
ALTER TABLE `areasofexperience`
  ADD PRIMARY KEY (`username`,`area`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`recipeID`,`category`);

--
-- Indexes for table `cookpincount`
--
ALTER TABLE `cookpincount`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `dietaryrestrictions`
--
ALTER TABLE `dietaryrestrictions`
  ADD PRIMARY KEY (`recipeID`,`restriction`);

--
-- Indexes for table `foodies`
--
ALTER TABLE `foodies`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`recipeID`,`ingredient`);

--
-- Indexes for table `pin`
--
ALTER TABLE `pin`
  ADD PRIMARY KEY (`recipeID`,`username`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipeID`,`username`),
  ADD UNIQUE KEY `instructions` (`instructions`) USING HASH;

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`recipeID`,`reviews`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
