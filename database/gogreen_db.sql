-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 07:53 PM
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
-- Database: `gogreen_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(3, 'admin', '$2y$10$QMUpxz0mIDlmvBXa28U5HO/PQ.scitraDaU/10.didPdAnv.4D5Ta');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(13, 4, 16, 1, '2026-04-24 08:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `type` enum('flowers','vegetables','fruits') NOT NULL,
  `season` enum('summer','winter','rainy','all') DEFAULT 'all',
  `location` enum('indoor','outdoor','both') DEFAULT 'both'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `type`, `season`, `location`) VALUES
(1, 'Winter Flowers', NULL, 'flowers', 'winter', 'both'),
(2, 'Summer Flowers', NULL, 'flowers', 'summer', 'both'),
(3, 'Rainy Flowers', NULL, 'flowers', 'rainy', 'both'),
(4, 'Indoor Flowers', NULL, 'flowers', 'all', 'indoor'),
(5, 'Outdoor Flowers', NULL, 'flowers', 'all', 'outdoor'),
(6, 'Leafy Vegetables', NULL, 'vegetables', 'all', 'both'),
(7, 'Plant Vegetables', NULL, 'vegetables', 'all', 'both'),
(8, 'Creeper Vegetables', NULL, 'vegetables', 'all', 'both'),
(9, 'Seasonal Fruits', NULL, 'fruits', 'all', 'both'),
(10, 'Exotic Fruits', NULL, 'fruits', 'all', 'both'),
(11, 'Throns plants', NULL, 'flowers', 'all', 'outdoor');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `alt_mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `order_status` enum('placed','processing','shipped','delivered') DEFAULT 'placed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `discount`, `final_amount`, `full_name`, `address`, `city`, `pincode`, `mobile`, `alt_mobile`, `email`, `payment_id`, `payment_status`, `order_status`, `created_at`) VALUES
(1, 1, 200.00, 0.00, 200.00, 'K P Neha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'honnavar', '581334', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69E95C4D2D51C', 'paid', 'delivered', '2026-04-22 23:39:57'),
(2, 3, 75.00, 0.00, 75.00, 'misbha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'udupi', '581334', '9844488669', '', 'misbhafathima986@gmail.com', 'DUMMY_69E9D71B6741E', 'paid', 'delivered', '2026-04-23 08:23:55'),
(3, 1, 420.00, 0.00, 420.00, 'K P Neha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'honnavar', '581334', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69E9F29F5E3CA', 'paid', 'delivered', '2026-04-23 10:21:19'),
(4, 1, 90.00, 0.00, 90.00, 'K P Neha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'honnavar', '331224', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69E9F4637B929', 'paid', 'placed', '2026-04-23 10:28:51'),
(5, 4, 75.00, 0.00, 75.00, 'Suparna', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'udupi', '581336', '9742311226', '', 'suparnanidhi21@gmail.com', 'DUMMY_69EB28BCEDC81', 'paid', 'shipped', '2026-04-24 08:24:28'),
(6, 1, 60.00, 0.00, 60.00, 'K P Neha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'Honnavar', '581335', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69EB2EE1EF8F8', 'paid', 'delivered', '2026-04-24 08:50:41'),
(7, 5, 42.00, 0.00, 42.00, 'Kavya', 'Manipal,Parkala', 'Udupi', '576107', '6361104759', '', 'kavyashankarkulal@gmail.com', 'DUMMY_69EB3E15A5D24', 'paid', 'processing', '2026-04-24 09:55:33'),
(8, 1, 680.00, 68.00, 612.00, 'K P Neha', 'KADLE\nHEBBARNAKERI\nHONNAVAR', 'Honnavar', '581335', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69EB469C5F296', 'paid', 'shipped', '2026-04-24 10:31:56'),
(9, 1, 35.00, 0.00, 35.00, 'K P Neha', 'xyz', 'Honnavar', '574116', '9482081208', '', 'kpneha77@gmail.com', 'DUMMY_69EB4C5514DFD', 'paid', 'processing', '2026-04-24 10:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 15, 1, 55.00),
(2, 1, 17, 1, 90.00),
(3, 1, 5, 1, 55.00),
(4, 2, 16, 1, 75.00),
(5, 3, 17, 3, 90.00),
(6, 3, 16, 2, 75.00),
(7, 4, 6, 3, 30.00),
(8, 5, 16, 1, 75.00),
(9, 6, 17, 1, 60.00),
(10, 7, 13, 1, 42.00),
(11, 8, 17, 4, 60.00),
(12, 8, 15, 1, 55.00),
(13, 8, 13, 1, 42.00),
(14, 8, 14, 1, 60.00),
(15, 8, 12, 1, 38.00),
(16, 8, 6, 1, 30.00),
(17, 8, 7, 1, 50.00),
(18, 8, 8, 1, 25.00),
(19, 8, 3, 1, 45.00),
(20, 8, 4, 1, 40.00),
(21, 8, 5, 1, 55.00),
(22, 9, 2, 1, 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `otp` varchar(10) NOT NULL,
  `type` enum('register','login') DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `email`, `mobile`, `otp`, `type`, `expires_at`, `is_used`, `created_at`) VALUES
(19, 4, 'suparnanidhi21@gmail.com', '9742311226', '504109', 'login', '2026-04-24 10:25:55', 1, '2026-04-24 08:20:55'),
(25, 1, 'kpneha77@gmail.com', '9482081208', '745757', 'login', '2026-04-24 12:33:23', 1, '2026-04-24 10:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image`, `created_at`) VALUES
(1, 'Rose Seeds', 'Beautiful red rose seeds, perfect for winter gardens. Easy to grow.', 49.00, 100, 1, 'flowers/rose.jpg', '2026-04-20 23:59:14'),
(2, 'Marigold Seeds', 'Bright orange marigold seeds. Great for winter season.', 35.00, 149, 1, 'flowers/marigold.jpg', '2026-04-20 23:59:14'),
(3, 'Sunflower Seeds', 'Tall sunflower seeds perfect for summer outdoor gardens.', 45.00, 119, 2, 'flowers/sunflower.jpg', '2026-04-20 23:59:14'),
(4, 'Zinnia Seeds', 'Colorful zinnia flowers that bloom in summer.', 40.00, 99, 2, 'flowers/zinnia.jpg', '2026-04-20 23:59:14'),
(5, 'Jasmine Seeds', 'Fragrant jasmine perfect for rainy season planting.', 55.00, 78, 3, 'flowers/jasmine.jpg', '2026-04-20 23:59:14'),
(6, 'Money Plant Seeds', 'Popular indoor decorative plant. Very easy to maintain.', 30.00, 196, 4, 'flowers/moneyplant.jpg', '2026-04-20 23:59:14'),
(7, 'Mogra Seeds', 'White fragrant mogra flowers for outdoor gardens.', 50.00, 89, 5, 'flowers/mogra.jpg', '2026-04-20 23:59:14'),
(8, 'Spinach Seeds', 'Fresh leafy spinach seeds. Ready to harvest in 30 days.', 25.00, 199, 6, 'vegetables/spinach.jpg', '2026-04-20 23:59:14'),
(9, 'Coriander Seeds', 'Fresh coriander seeds for your kitchen garden.', 20.00, 250, 6, 'vegetables/coriander.jpg', '2026-04-20 23:59:14'),
(10, 'Tomato Seeds', 'Hybrid tomato seeds with high yield. Great for home gardens.', 40.00, 180, 7, 'vegetables/tomato.jpg', '2026-04-20 23:59:14'),
(11, 'Brinjal Seeds', 'Purple brinjal seeds, seasonal variety with good yield.', 35.00, 160, 7, 'vegetables/brinjal.jpg', '2026-04-20 23:59:14'),
(12, 'Cucumber Seeds', 'Creeper cucumber seeds perfect for fences and trellises.', 38.00, 0, 8, 'vegetables/cucumber.jpg', '2026-04-20 23:59:14'),
(13, 'Bitter Gourd Seeds', 'Healthy bitter gourd creeper seeds for home garden.', 42.00, 128, 8, 'vegetables/bittergourd.jpg', '2026-04-20 23:59:14'),
(14, 'Papaya Seeds', 'Fast growing papaya seeds. Fruits within 9-10 months.', 60.00, 99, 9, 'fruits/papaya.jpg', '2026-04-20 23:59:14'),
(15, 'Red Watermelon Seeds', 'Sweet watermelon seeds for summer season.', 55.00, 108, 9, 'fruits/watermelon.jpg', '2026-04-20 23:59:14'),
(16, 'Pomegranate Seeds', 'Exotic pomegranate seeds with high nutritional value.', 75.00, 76, 10, 'fruits/pomegranate.jpg', '2026-04-20 23:59:14'),
(17, 'Red Dragon Fruit Seeds', 'Exotic dragon fruit seeds, unique and healthy.', 50.00, 8, 10, 'fruits/dragonfruit.jpg', '2026-04-20 23:59:14'),
(19, 'Cucumber Seeds', 'Creepy plant', 30.00, 15, 8, NULL, '2026-04-24 16:02:05'),
(20, 'Red Rose', 'beautiful red rbutton roses', 80.00, 25, 11, NULL, '2026-04-24 16:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `place` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `place`, `password`, `is_verified`, `created_at`) VALUES
(1, 'K P Neha', 'kpneha77@gmail.com', '9482081208', 'Honnavar', '$2y$10$qqa0omgXJmFpMsEPeXmELegbo7qyURQHrqCVtTLAG4djkdxDI2rV.', 1, '2026-04-20 05:57:32'),
(3, 'misbha', 'misbhafathima986@gmail.com', '9844488669', 'udupi', '$2y$10$Ni9xscSzcPeqWFv1/EWhCOnd/riqFg2TmjglewldD36cQ5nSpcMDq', 1, '2026-04-23 08:12:14'),
(4, 'Suparna', 'suparnanidhi21@gmail.com', '9742311226', 'udupi', '$2y$10$mwe85iihWnC5H.4mjfnenu9t/ScyhdoVWCwt.jj3hQeT1Jwz3KCkm', 1, '2026-04-24 08:19:41'),
(5, 'Kavya', 'kavyashankarkulal@gmail.com', '6361104759', 'Manglore', '$2y$10$eHEqLL/hkpwCQ/kXM1V1B.f1aubjzo2my6TX4DTGsqGtGx.CgGX7i', 1, '2026-04-24 09:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
