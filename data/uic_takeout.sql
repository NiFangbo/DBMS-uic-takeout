SET GLOBAL max_allowed_packet = 2 * 1024 * 1024 * 10;

DROP DATABASE IF EXISTS `uic_takeout`;
CREATE DATABASE `uic_takeout`;
USE `uic_takeout`;

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `user`(
    `user_id` int NOT NULL AUTO_INCREMENT,
    `user_type` varchar(40) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`user_id`)
);

INSERT INTO `user` VALUES
(1, 'manager', 'Smith', '666666'),
(2, 'deliveryman', 'Neo', '666666'),
(3, 'customer', 'cascade_k', '888888'),
(4, 'customer', 'luck_star', '888888');

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `customer`(
    `customer_id` int NOT NULL,
    `customer_name` varchar(255) NOT NULL,
    `nickname` varchar(255),
    `phone` varchar(20),
    `identity` varchar(20),
    `balance` float(2) NOT NULL,
    PRIMARY KEY(`customer_id`)
);

INSERT INTO `customer` VALUES
(3, 'cascade_k', 'Cascade Kobayashi', '12345678910', 'Student', 127.21),
(4, 'luck_star', 'Superbear', '10987654321', 'Staff', 1145.14);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `deliveryman`(
    `deliveryman_id` int NOT NULL,
    `deliveryman_name` varchar(255) NOT NULL,
    `phone` varchar(20),
    `identity` varchar(20),
    `balance` float(2) NOT NULL,
    PRIMARY KEY(`deliveryman_id`)
);

INSERT INTO `deliveryman` VALUES
(2, 'Neo', '12345654321', 'Delivery Person', 293.00);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `order`(
    `order_id` int NOT NULL AUTO_INCREMENT,
    `customer_id` int NOT NULL,
    `order_time` datetime NOT NULL,
    `order_address` varchar(255) NOT NULL,
    `deliveryman_id` int,
    `check_time` datetime,
    `delivery_status` varchar(40),
    `complete_time` datetime,
    PRIMARY KEY(`order_id`)
);

INSERT INTO `order` VALUES
(1, 3, '2024-12-1 6:00:12', "Teaching Building T29 Ground Floor", 2, '2024-12-1 6:15:17', 'Completed', '2024-12-1 6:30:07'),
(2, 3, '2024-12-1 7:20:25', "Teaching Building T8-307", 2, '2024-12-1 7:30:12', 'Cancelled', NULL),
(3, 4, '2024-12-1 8:15:31', "Dormitory V25 Ground Floor", 2, '2024-12-1 8:23:37', 'Delivering', NULL),
(4, 4, '2024-12-1 10:17:43', "Dormitory V22 Ground Floor", NULL, NULL, 'Pending', NULL);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `category`(
    `category_id` int NOT NULL AUTO_INCREMENT,
    `category_name` varchar(255) NOT NULL,
    PRIMARY KEY(`category_id`)
);

INSERT INTO `category` VALUES
(1, 'Stationary'),
(2, 'Snack'),
(3, 'Instant Noodle'),
(4, 'Drink');

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `product`(
    `product_id` int NOT NULL AUTO_INCREMENT,
    `product_name` varchar(255) NOT NULL,
    `product_price` float(2) NOT NULL,
    `description` varchar(255) NOT NULL,
    `image` LONGBLOB,
    `status` varchar(40) NOT NULL,
    PRIMARY KEY(`product_id`)
);

INSERT INTO `product` VALUES
(1, "Uni M5-559 Mechanical Pencil", 5.99, "Uni KURU TOGA Advance, Auto Rotating Mechanical Pencil. 0.5mm Lead.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/pencil-m5-559.jpg'), "Available"),
(2, "Lay's Classic Potato Chip 180g", 1.99, "Lays's Classic Potato Chip, Classic Salted Favour.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/lays-classic.jpg'), "Available"),
(3, "Full Size Bulk Candy Assortment", 31.99, "Include: M&M'S, SNICKERS, 3 MUSKETEERS, SKITTLES & STARBURST", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/1.jpg'), "Available"),
(4, "Doritos Flavored Tortilla Chips", 15.70, "Nacho Cheese, 1 Ounce (Pack of 40)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/2.jpg'), "Available"),
(5, "Dole Fruit Bowls Diced Peaches", 26.82, "100% Juice Snacks, 4oz 36 Total Cups, Gluten & Dairy Free", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/3.jpg'), "Available"),
(6, "SkinnyPop Original Popcorn", 15.30, "Skinny Pop, Healthy Popcorn Snacks, Gluten Free, 0.65 Ounce (Pack of 30)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/4.jpg'), "Available"),
(7, "Lotus Biscoff Cookies", 7.68, "Caramelized Biscuit Cookie Snack, Dispenser Box (20 sleeves of 2 extra large cookies) ", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/5.jpg'), "Available"),
(8, "OREO Chocolate Sandwich Cookies", 6.58, "30 Snack Packs (4 Cookies Per Pack)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/6.jpg'), "Available"),
(9, "PLANTERS Apple Cider Donut Cashews", 8.09, "Cooking & Baking Nuts & Seeds, Flavored Cashews, 12.5 oz Canister", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/7.jpg'), "Available"),
(10, "Pringles Potato Crisps Chips", 12.00, "Sour Cream & Onion, 2.5oz", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/8.jpg'), "Available"),
(11, "Wonderful Pistachios No Shells", 5.49, "Gluten Free, Stocking Stuffers, Healthy Snacks, Sweet Christmas Snacks", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/9.jpg'), "Available"),
(12, "KIND Nut Bars Favorites Variety Pack", 16.99, "Dark Chocolate Nuts and Sea Salt, Peanut Butter Dark Chocolate", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/10.jpg'), "Available"),
(13, "Momofuku Ramen Noodle Not-So-Spicy Variety Pack", 55.99, "Include: 20 Count of Air-Dried Vegan Instant Noodles", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_1.jpg'), "Available"),
(14, "Momofuku Ramen Noodle Variety Pack", 45.99, "Include: 15 Count (Pack of 3) of Air-Dried Vegan Instant Noodles", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_2.jpg'), "Available"),
(15, "Japanese popular Ramen 'ICHIRAN' instant noodles tonkotsu", 26.99, "Include: 22.7 Ounce (Pack of 1)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_3.jpg'), "Available"),
(16, "Indomie Mi Goreng Instant Stir Fry Noodles, Halal Certified", 22.99, "Include: Original Flavor (Pack of 30)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_4.jpg'), "Available"),
(17, "Nongshim Gourmet Spicy Shin Instant Ramen Noodle Cup", 7.38, "2.64 Ounce (Pack of 6)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_5.jpg'), "Available"),
(18, "Maruchan Ramen Pork, Instant Ramen Noodles", 9.36, "america's favorite ramen brand. up to 24g protein, 5g Net Carbs", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_6.jpg'), "Available"),
(19, "Nongshim Soon Instant Vegan Ramen Noodle Soup Cup", 6.99, "6 Pack, Microwaveable Safe Cup, Vegan Meatless Ramen", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_7.jpg'), "Available"),
(20, "JML INSTANT NOODLE", 6.99, "STEWED BEEF FLAVOR-5bags, made in china", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_8.jpg'), "Available"),
(21, "Nissin Demae Black Garlic Oil Instant Ramen Noodles", 30.99, "3.5 Ounce (Pack of 30), lodized Salt", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_9.jpg'), "Available"),
(22, "Nissin RAOH Ramen Noodle Soup", 16.08, "Tonkotsu, 3.53 Ounce (Pack of 6)", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/noodle_10.jpg'), "Available"),
(23, "Red Bull energy drink", 34.50, "80 mg caffeine with taurine and B vitamins, 8.4 liquid ounces, 24 cans", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d1.jpg'), "Available"),
(24, "Coca-Cola", 16.08, "Coke soda, 12 ounces", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d2.jpg'), "Available"),
(25, "Pure leaf vase", 23.76, "Multiple packaging (sweet). No artificial sweeteners. No artificial flavors.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d3.jpg'), "Available"),
(26, "Propel", 8.52, "Kiwi strawberries, zero calorie exercise drinking water contains electrolytes and vitamins", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d4.jpg'), "Available"),
(27, "POPPI", 30.00, "Sparkling Prebiotic sodas, drinks with apple cider vinegar, Seltzer Water and juices, short list varieties", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d5.jpg'), "Available"),
(28, "Monster Energy Ultra Vice", 30.00, "Guava, sugar-free energy drink, 16 ounces", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d6.jpg'), "Available"),
(29, "Jumex", 8.52, "Guava and Strawberry Banana Nectar Refrigerator Pack 11.3 fl oz", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d7.jpg'), "Available"),
(30, "Carnation Breakfast Essentials", 29.88, "High protein Fiber ready-to-drink, 8 fl oz carton, rich Milk chocolate 8 fl oz", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d8.jpg'), "Available"),
(31, "Amazon Fresh", 2.79, "Lemonade concentrate, 64 liquid ounces", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d9.jpg'), "Available"),
(32, "Ocean Spray Cran", 4.98, "Apple juice drink, 10 ounces", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/d10.jpg'), "Available"),
(33, "Colored Pencil Set", 19.99, "Contains 56 vibrant colored pencils, perfect for drawing and coloring.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/s1.jpg'), 'Available'),
(34, "Sticky Notes Set", 12.99, "Includes a variety of colors and sizes of sticky notes for reminders and inspirations.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/s2.jpg'), 'Available'),
(35, "Sketchbook", 14.99, "A high-quality sketchbook with 100 pages of thick, ideal for drawing and sketching.", LOAD_FILE('D:/xampp/htdocs/uic_takeout/static/products/s3.jpg'), 'Available');

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `transaction`(
    `order_id` int NOT NULL,
    `product_id` int NOT NULL,
    `single_price` float(2) NOT NULL,
    `quantity` int NOT NULL,
    PRIMARY KEY(`order_id`, `product_id`)
);

INSERT INTO `transaction` VALUES
(1, 1, 5.99, 2),
(1, 2, 1.99, 4),
(2, 2, 1.99, 8),
(3, 1, 5.99, 3),
(3, 2, 1.99, 1),
(4, 1, 5.99, 13),
(4, 2, 1.99, 7);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `comment`(
    `comment_id` int NOT NULL AUTO_INCREMENT,
    `order_id` int NOT NULL,
    `comment_date` date NOT NULL,
    `content` varchar(255) NOT NULL,
    `star_level` int NOT NULL,
    `status` varchar(255) NOT NULL,
    PRIMARY KEY(`comment_id`)
);

INSERT INTO `comment` VALUES
(1, 1, '2024-12-5', "A really good pencil, perfectly fits my requirements.", 4, "Shown");

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `product_category`(
    `product_id` int NOT NULL,
    `category_id` int NOT NULL,
    PRIMARY KEY(`product_id`)
);

INSERT INTO `product_category` VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(21, 3),
(22, 3),
(23, 4),
(24, 4),
(25, 4),
(26, 4),
(27, 4),
(28, 4),
(29, 4),
(30, 4),
(31, 4),
(32, 4),
(33, 1),
(34, 1),
(35, 1);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `comment_product`(
    `comment_id` int NOT NULL,
    `product_id` int NOT NULL,
    PRIMARY KEY(`comment_id`)
);

INSERT INTO `comment_product` VALUES
(1, 1);

-- -------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `cart`(
    `customer_id` int NOT NULL,
    `product_id` int NOT NULL,
    `quantity` int NOT NULL,
    PRIMARY KEY(`customer_id`, `product_id`)
);

INSERT INTO `cart` VALUES
(3, 1, 2),
(3, 2, 4);

-- -------------------------------------------------------------------------------------------------------------------------

ALTER TABLE `transaction` ADD FOREIGN KEY(`product_id`) REFERENCES `product`(`product_id`);

ALTER TABLE `product_category` ADD FOREIGN KEY(`product_id`) REFERENCES `product`(`product_id`);
ALTER TABLE `product_category` ADD FOREIGN KEY(`category_id`) REFERENCES `category`(`category_id`);

ALTER TABLE `comment_product` ADD FOREIGN KEY(`product_id`) REFERENCES `product`(`product_id`);

ALTER TABLE `cart` ADD FOREIGN KEY(`customer_id`) REFERENCES `customer`(`customer_id`);
ALTER TABLE `cart` ADD FOREIGN KEY(`product_id`) REFERENCES `product`(`product_id`);

-- -------------------------------------------------------------------------------------------------------------------------

ALTER TABLE `customer` ADD CONSTRAINT `customer_user`
FOREIGN KEY (`customer_id`) REFERENCES `user`(`user_id`)
ON DELETE CASCADE;


ALTER TABLE `deliveryman` ADD CONSTRAINT `deliveryman_user`
FOREIGN KEY(`deliveryman_id`) REFERENCES `user`(`user_id`)
ON DELETE CASCADE;


ALTER TABLE `order` ADD CONSTRAINT `order_customer`
FOREIGN KEY(`customer_id`) REFERENCES `customer`(`customer_id`)
ON DELETE CASCADE;
ALTER TABLE `order` ADD CONSTRAINT `order_deliveryman`
FOREIGN KEY(`deliveryman_id`) REFERENCES `deliveryman`(`deliveryman_id`)
ON DELETE CASCADE;


ALTER TABLE `transaction` ADD CONSTRAINT `transaction_order`
FOREIGN KEY(`order_id`) REFERENCES `order`(`order_id`)
ON DELETE CASCADE;


ALTER TABLE `comment` ADD CONSTRAINT `comment_order`
FOREIGN KEY(`order_id`) REFERENCES `order`(`order_id`)
ON DELETE CASCADE;


ALTER TABLE `comment_product` ADD CONSTRAINT `comment_product_comment`
FOREIGN KEY(`comment_id`) REFERENCES `comment`(`comment_id`)
ON DELETE CASCADE;


DELIMITER |
CREATE TRIGGER add_user
AFTER INSERT ON `user`
FOR EACH ROW
BEGIN
    IF new.`user_type` = 'customer'
    THEN
        INSERT INTO `customer` VALUES(new.`user_id`, new.`username`, new.`username`, NULL, 'Student', 100.00);
    END IF;
    IF new.`user_type` = 'deliveryman'
    THEN
        INSERT INTO `deliveryman` VALUES(new.`user_id`, new.`username`, NULL, 'Delivery Person', 100.00);
    END IF;
END;|
DELIMITER ;

-- -------------------------------------------------------------------------------------------------------------------------

ALTER TABLE `customer` ADD CONSTRAINT `customer_identity_domain`
CHECK(`identity` = NULL OR `identity` = 'Student' OR `identity` = 'Staff');


ALTER TABLE `deliveryman` ADD CONSTRAINT `deliveryman_identity_domain`
CHECK(`identity` = NULL OR `identity` = 'Part-Time Student' OR `identity` = 'Part-Time Staff' OR `identity` = 'Delivery Person');


ALTER TABLE `order` ADD CONSTRAINT `order_status_domain`
CHECK(`delivery_status` = 'Pending' OR `delivery_status` = 'Delivering' OR `delivery_status` = 'Completed' OR `delivery_status` = 'Cancelled');


ALTER TABLE `product` ADD CONSTRAINT `product_status_domain`
CHECK(`status` = 'Available' OR `status` = 'Unavailable');


ALTER TABLE `comment` ADD CONSTRAINT `star_level_domain`
CHECK(`star_level` >= 1 AND `star_level` <= 5);
ALTER TABLE `comment` ADD CONSTRAINT `comment_status_domain`
CHECK(`status` = 'shown' OR `status` = 'hidden');

-- -------------------------------------------------------------------------------------------------------------------------