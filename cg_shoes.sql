-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 05:22 PM
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
-- Database: `cg_shoes`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `timestamp`) VALUES
(32, 1, 'Cleared all admin logs', '2025-06-18 18:58:20'),
(33, 1, 'Updated order #11 status to delivered', '2025-06-18 19:01:03'),
(34, 1, 'Admin logged out', '2025-06-18 19:12:53'),
(35, 1, 'Admin logged in', '2025-06-18 19:21:43'),
(36, 1, 'Updated product: Classic Loafers', '2025-06-18 19:22:32'),
(37, 1, 'Updated product: Running Sports Shoes', '2025-06-18 19:23:36'),
(38, 1, 'Admin logged out', '2025-06-18 19:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Sneakers', '2025-06-18 15:20:15'),
(2, 'Formal Shoes', '2025-06-18 15:20:15'),
(3, 'Boots', '2025-06-18 15:20:15'),
(4, 'Sandals', '2025-06-18 15:20:15'),
(5, 'Sports Shoes', '2025-06-18 15:20:15'),
(6, 'Casual Shoes', '2025-06-18 15:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 2, 179.98, 'delivered', '2025-06-18 15:20:15'),
(2, 2, 89.99, 'processing', '2025-06-18 15:20:15'),
(3, 1, 649.95, 'delivered', '2025-06-18 16:55:22'),
(5, 1, 1200.00, 'pending', '2025-06-18 18:13:00'),
(6, 1, 1200.00, 'pending', '2025-06-18 18:14:24'),
(7, 1, 1200.00, 'pending', '2025-06-18 18:14:26'),
(8, 1, 1200.00, 'pending', '2025-06-18 18:14:32'),
(9, 1, 1200.00, 'pending', '2025-06-18 18:14:35'),
(10, 1, 1200.00, 'delivered', '2025-06-18 18:15:09'),
(11, 1, 999.00, 'delivered', '2025-06-18 18:44:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `size`) VALUES
(1, 1, 1, 2, 89.99, NULL),
(2, 2, 4, 1, 49.99, NULL),
(3, 2, 6, 1, 79.99, NULL),
(4, 3, 2, 5, 129.99, NULL),
(6, 10, 2, 1, 1200.00, ''),
(7, 11, 1, 1, 999.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sizes` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `price`, `description`, `image`, `stock_quantity`, `sizes`, `created_at`) VALUES
(1, 'Classic Loafers', 2, 999.00, 'Comfortable and stylish white sneakers perfect for everyday wear. Made with premium materials for durability and comfort.', '6852a7aea3363.jpg', 25, '6,7,8,9,10', '2025-06-18 15:20:15'),
(2, 'Nike Sports Shoes', 5, 1200.00, 'Elegant black oxford shoes for formal occasions. Professional design with superior craftsmanship.', '6852b0261aab1.jpg', 15, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(3, 'Running Sports Shoes', 5, 800.00, 'Perfect Sports shoes for Running,Exercise,Gym,etc.in the soothing color Blue', '6852a904cddde.jpg', 10, '5,6,7,8', '2025-06-18 15:20:15'),
(4, 'Casual Sneakers', 1, 899.00, 'Comfortable Casual Sneakers with beautiful lysis. Lightweight and breathable design.', '6852ad508d591.jpg', 30, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(5, 'Nike Sports Shoes', 5, 1500.00, 'High-performance running shoes with advanced cushioning technology. Ideal for athletes and fitness enthusiasts.', '6852ada01381a.jpg', 20, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(6, 'Casual Loafers', 6, 899.99, 'Stylish casual loafers perfect for relaxed occasions. Comfortable fit with modern design.', '6852b04e99685.jpg', 18, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(7, 'Basketball Sneakers', 1, 1200.00, 'Professional basketball sneakers with ankle support and superior grip. Designed for optimal performance on the court.', '6852add828770.jpg', 12, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(8, 'Brown Derby Shoes', 2, 999.00, 'Classic brown derby shoes with timeless design. Perfect for business and formal events.', '6852ae05a5ad8.jpg', 10, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(9, 'White Sneakers', 1, 800.00, 'Warm and waterproof White Sneakers with insulated lining. Perfect for cold weather conditions.', '6852af52becc6.jpg', 20, '6,7,8,9,10,11', '2025-06-18 15:20:15'),
(10, 'Nike Special Sneakers', 1, 3000.00, 'Nike Special Edition Sneakers. Comfortable for beach and casual wear. Lightweight and easy to wear.', '6852aff989307.jpg', 40, '6,7,8,9,10,11', '2025-06-18 15:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `is_admin`, `created_at`) VALUES
(1, 'Admin User', 'admin@cgshoes.com', '$2y$10$k0tdGRCwvFyQUNBsOD1oQ.SWJxLJMFClGLRizUyLyo27zx0sAQQ6.', '+1234567890', '123 Admin Street, City, State', 1, '2025-06-18 15:20:15'),
(2, 'John Customer', 'customer@cgshoes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891', '456 Customer Avenue, City, State', 0, '2025-06-18 15:20:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
