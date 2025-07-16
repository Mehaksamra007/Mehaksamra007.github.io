-- Create the database
DROP DATABASE IF EXISTS freshfields;
CREATE DATABASE freshfields;
USE freshfields;

-- Create categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL
);

-- Create products table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL,
    image_url VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Create users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    zip_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_city VARCHAR(50) NOT NULL,
    delivery_zip_code VARCHAR(10) NOT NULL,
    delivery_phone VARCHAR(20) NOT NULL,
    delivery_instructions TEXT,
    payment_method ENUM('Credit/Debit Card', 'PayPal', 'Cash on Delivery') NOT NULL,
    card_name VARCHAR(100),
    card_number VARCHAR(20),
    card_expiry VARCHAR(10),
    card_cvv VARCHAR(4),
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create order_items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'replied', 'resolved') DEFAULT 'unread',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert categories
INSERT INTO categories (category_name) VALUES
('Organic Fruits'), ('Organic Vegetables'), ('Fresh Meats'), ('Organic Dairy'),
('Alcohol'), ('Flowers'), ('Imported Spices'), ('Gym Supplements'),
('Personal Care'), ('Cosmetic Products'), ('Baby and Child'), ('Pet Care');

-- Insert 120 sample products (10 per category)
INSERT INTO products (category_id, product_name, description, price, stock_quantity, image_url) VALUES
-- Organic Fruits (10)
(1, 'Organic Apples', 'Fresh organic apples from local farms. Sweet and crispy.', 4.99, 100, 'images/products/organic-apples.jpg'),
(1, 'Organic Bananas', 'Ripe organic bananas perfect for snacking or smoothies.', 2.99, 150, 'images/products/organic-bananas.jpg'),
(1, 'Organic Strawberries', 'Sweet, juicy organic strawberries bursting with flavor.', 5.99, 80, 'images/products/organic-strawberries.jpg'),
(1, 'Organic Blueberries', 'Antioxidant-rich organic blueberries.', 6.49, 70, 'images/products/organic-blueberries.jpg'),
(1, 'Organic Grapes', 'Sweet seedless organic grapes.', 4.49, 100, 'images/products/organic-grapes.jpg'),
(1, 'Organic Oranges', 'Juicy organic oranges packed with vitamin C.', 3.99, 120, 'images/products/organic-oranges.jpg'),
(1, 'Organic Pineapple', 'Sweet tropical organic pineapple.', 5.99, 50, 'images/products/organic-pineapple.jpg'),
(1, 'Organic Mangoes', 'Sweet and juicy organic mangoes.', 4.99, 60, 'images/products/organic-mangoes.jpg'),
(1, 'Organic Pears', 'Delicate and sweet organic pears.', 3.99, 90, 'images/products/organic-pears.jpg'),
(1, 'Organic Kiwi', 'Tart and sweet organic kiwi fruit.', 4.49, 110, 'images/products/organic-kiwi.jpg'),

-- Organic Vegetables (10)
(2, 'Organic Spinach', 'Nutrient-rich organic spinach leaves perfect for salads.', 3.49, 140, 'images/products/organic-spinach.jpg'),
(2, 'Organic Carrots', 'Sweet, crunchy organic carrots grown locally.', 2.99, 130, 'images/products/organic-carrots.jpg'),
(2, 'Organic Bell Peppers', 'Colorful mix of organic bell peppers.', 4.99, 90, 'images/products/organic-bell-peppers.jpg'),
(2, 'Organic Broccoli', 'Fresh organic broccoli packed with nutrients.', 3.99, 100, 'images/products/organic-broccoli.jpg'),
(2, 'Organic Tomatoes', 'Juicy vine-ripened organic tomatoes.', 4.49, 110, 'images/products/organic-tomatoes.jpg'),
(2, 'Organic Cucumbers', 'Crisp organic cucumbers perfect for salads.', 2.49, 150, 'images/products/organic-cucumbers.jpg'),
(2, 'Organic Kale', 'Nutrient-dense organic kale leaves.', 3.99, 80, 'images/products/organic-kale.jpg'),
(2, 'Organic Zucchini', 'Fresh organic zucchini for versatile cooking.', 2.99, 100, 'images/products/organic-zucchini.jpg'),
(2, 'Organic Sweet Potatoes', 'Naturally sweet organic sweet potatoes.', 3.49, 90, 'images/products/organic-sweet-potatoes.jpg'),
(2, 'Organic Mushrooms', 'Fresh organic mushrooms for cooking.', 4.99, 70, 'images/products/organic-mushrooms.jpg'),

-- Fresh Meats (10)
(3, 'Grass-fed Beef Steak', 'Premium grass-fed beef steaks, hormone-free.', 14.99, 50, 'images/products/grass-fed-beef.jpg'),
(3, 'Free-range Chicken', 'Fresh free-range chicken breast.', 9.99, 70, 'images/products/free-range-chicken.jpg'),
(3, 'Organic Ground Turkey', 'Lean organic ground turkey for healthy meals.', 8.99, 60, 'images/products/organic-turkey.jpg'),
(3, 'Pasture-raised Pork Chops', 'Juicy pasture-raised pork chops.', 11.99, 50, 'images/products/pork-chops.jpg'),
(3, 'Grass-fed Ground Beef', 'Lean grass-fed ground beef.', 10.99, 80, 'images/products/ground-beef.jpg'),
(3, 'Free-range Chicken Thighs', 'Flavorful free-range chicken thighs.', 7.99, 90, 'images/products/chicken-thighs.jpg'),
(3, 'Organic Lamb Chops', 'Tender organic lamb chops.', 18.99, 40, 'images/products/lamb-chops.jpg'),
(3, 'Grass-fed Beef Ribs', 'Flavorful grass-fed beef ribs.', 12.99, 50, 'images/products/beef-ribs.jpg'),
(3, 'Organic Chicken Wings', 'Juicy organic chicken wings.', 8.49, 80, 'images/products/chicken-wings.jpg'),
(3, 'Grass-fed Beef Roast', 'Premium grass-fed beef roast.', 15.99, 40, 'images/products/beef-roast.jpg'),

-- Organic Dairy (10)
(4, 'Organic Whole Milk', 'Fresh organic whole milk from grass-fed cows.', 4.99, 100, 'images/products/organic-milk.jpg'),
(4, 'Organic Greek Yogurt', 'Creamy organic Greek yogurt packed with protein.', 5.49, 80, 'images/products/organic-yogurt.jpg'),
(4, 'Organic Cheddar Cheese', 'Artisan organic cheese with rich flavor.', 8.99, 70, 'images/products/organic-cheese.jpg'),
(4, 'Organic Butter', 'Creamy organic butter from grass-fed cows.', 6.99, 90, 'images/products/organic-butter.jpg'),
(4, 'Organic Eggs (Dozen)', 'Free-range organic eggs from happy hens.', 7.99, 120, 'images/products/organic-eggs.jpg'),
(4, 'Organic Sour Cream', 'Rich and tangy organic sour cream.', 3.99, 90, 'images/products/organic-sour-cream.jpg'),
(4, 'Organic Cream Cheese', 'Smooth organic cream cheese.', 4.99, 100, 'images/products/organic-cream-cheese.jpg'),
(4, 'Organic Mozzarella', 'Fresh organic mozzarella cheese.', 7.49, 70, 'images/products/organic-mozzarella.jpg'),
(4, 'Organic Almond Milk', 'Creamy organic almond milk alternative.', 5.99, 80, 'images/products/organic-almond-milk.jpg'),
(4, 'Organic Cottage Cheese', 'Protein-rich organic cottage cheese.', 4.49, 100, 'images/products/organic-cottage-cheese.jpg'),

-- Alcohol (10)
(5, 'Park Sesh Lager Craft Beer 6-Pack', 'Local craft beer selection with unique flavors.', 14.99, 60, 'images/products/craft-beer.jpg'),
(5, 'Royal Red Wine', 'Award-winning organic red wine.', 19.99, 50, 'images/products/red-wine.jpg'),
(5, 'Smirnoff Lime Flavoured Vodka', 'Premium imported vodka, triple distilled.', 24.99, 40, 'images/products/vodka.jpg'),
(5, 'Jack Daniel''s Tennessee Whisky', 'Aged single malt whiskey.', 29.99, 30, 'images/products/whiskey.jpg'),
(5, 'Réserve Riesling White Wine', 'Crisp organic white wine.', 16.99, 50, 'images/products/white-wine.jpg'),
(5, 'Beefeater London Dry Gin', 'Premium craft gin with botanicals.', 22.99, 40, 'images/products/gin.jpg'),
(5, 'Captain Morgan Spiced Rum', 'Aged Caribbean rum.', 19.99, 50, 'images/products/rum.jpg'),
(5, 'Tequila Reposado Teremana', 'Premium 100% agave tequila.', 27.99, 30, 'images/products/tequila.jpg'),
(5, 'Roscato Sparkling Sweet Red', 'Organic sparkling wine for celebrations.', 21.99, 50, 'images/products/sparkling-wine.jpg'),
(5, 'Maline Deuxieme Cuvee Cider', 'Crisp organic apple cider.', 8.99, 70, 'images/products/cider.jpg'),

-- Flowers (10)
(6, 'Fresh Roses Bouquet', 'Dozen fresh cut roses in various colors.', 19.99, 40, 'images/products/roses-bouquet.jpg'),
(6, 'Tulip Arrangement', 'Beautiful tulip arrangement perfect for gifts.', 15.99, 60, 'images/products/tulips.jpg'),
(6, 'Sunflower Bouquet', 'Cheerful sunflower bouquet.', 17.99, 50, 'images/products/sunflowers.jpg'),
(6, 'Mixed Flower Basket', 'Seasonal mixed flower basket.', 24.99, 50, 'images/products/mixed-flowers.jpg'),
(6, 'Orchid Plant', 'Elegant live orchid plant.', 29.99, 30, 'images/products/orchid.jpg'),
(6, 'Lily Bouquet', 'Fragrant lily bouquet.', 21.99, 40, 'images/products/lilies.jpg'),
(6, 'Daisy Arrangement', 'Cheerful daisy flower arrangement.', 14.99, 50, 'images/products/daisies.jpg'),
(6, 'Carnation Bouquet', 'Long-lasting carnation bouquet.', 12.99, 60, 'images/products/carnations.jpg'),
(6, 'Succulent Garden', 'Low-maintenance succulent garden.', 22.99, 40, 'images/products/succulents.jpg'),
(6, 'Peony Bouquet', 'Luxurious peony bouquet.', 27.99, 30, 'images/products/peonies.jpg'),

-- Imported Spices (10)
(7, 'Saffron', 'Premium imported saffron threads.', 29.99, 50, 'images/products/saffron.jpg'),
(7, 'Truffle Salt', 'Italian black truffle sea salt.', 12.99, 60, 'images/products/truffle-salt.png'),
(7, 'Vanilla Beans', 'Madagascar vanilla beans.', 14.99, 70, 'images/products/vanilla-beans.jpg'),
(7, 'Ceylon Cinnamon', 'True Ceylon cinnamon sticks.', 8.99, 80, 'images/products/cinnamon.jpg'),
(7, 'Sumac', 'Middle Eastern sumac spice.', 7.99, 90, 'images/products/sumac.jpg'),
(7, 'Cardamom Pods', 'Green cardamom pods from India.', 9.99, 60, 'images/products/cardamom.jpg'),
(7, 'Star Anise', 'Whole star anise pods.', 6.99, 70, 'images/products/star-anise.jpg'),
(7, 'Smoked Paprika', 'Spanish smoked paprika.', 7.49, 80, 'images/products/paprika.jpg'),
(7, 'Za''atar Blend', 'Authentic Middle Eastern za''atar.', 8.99, 90, 'images/products/zaatar.jpg'),
(7, 'Japanese Matcha', 'Premium ceremonial grade matcha.', 19.99, 50, 'images/products/matcha.jpg'),

-- Gym Supplements (10)
(8, 'Ultimate Nutrition Prostar 100% Whey Protein', 'Premium whey protein isolate.', 24.99, 50, 'images/products/whey-protein.jpg'),
(8, 'Optimum Nutrition Instantized BCAA', 'Branch chain amino acids for recovery.', 19.99, 60, 'images/products/bcaa.jpg'),
(8, 'Cellucor C4 Ripped Preworkout', 'Energy-boosting pre-workout formula.', 29.99, 40, 'images/products/pre-workout.jpg'),
(8, 'Platinum 100% Creatine Monohydrate', 'Pure creatine for muscle performance.', 17.99, 70, 'images/products/creatine.jpg'),
(8, 'MusclePharm Combatxl 5.44 kg', 'High-calorie mass building formula.', 34.99, 50, 'images/products/mass-gainer.jpg'),
(8, 'OPTIMUM NUTRITION Gold Standard 100% Casein', 'Slow-digesting nighttime protein.', 27.99, 60, 'images/products/casein.jpg'),
(8, 'Platinum 100% Glutamine', 'Muscle recovery glutamine supplement.', 18.99, 70, 'images/products/glutamine.jpg'),
(8, 'PURE NUTRITION SUPER FAT BURNER', 'Thermogenic fat burning complex.', 22.99, 60, 'images/products/fat-burner.jpg'),
(8, 'Nuun Sport - Watermelon Electrolyte Tablets', 'Hydration electrolyte replacement.', 12.99, 80, 'images/products/electrolytes.jpg'),
(8, 'Great Lakes Wellness Collagen Peptides Powder', 'Joint and skin support collagen.', 21.99, 60, 'images/products/collagen.jpg'),

-- Personal Care (10)
(9, 'Rose Farmcrafted Soap', 'Natural organic soap with essential oils.', 6.99, 80, 'images/products/organic-soap.jpg'),
(9, 'Bamboo Charcoal Soft Toothbrush', 'Eco-friendly bamboo toothbrush.', 4.99, 100, 'images/products/bamboo-toothbrush.jpg'),
(9, 'Nivea Men Protect & Care 48h Deodorant', 'Aluminum-free natural deodorant.', 8.99, 80, 'images/products/deodorant.jpg'),
(9, 'Shampoo Bar – Om Naturale Herbal Care', 'Plastic-free shampoo bar.', 9.99, 90, 'images/products/shampoo-bar.jpg'),
(9, 'Dove Daily Moisture Hydration Conditioner', 'Solid conditioner for all hair types.', 9.99, 80, 'images/products/conditioner-bar.jpg'),
(9, 'Nivea, Essentially Enriched Body Lotion,', 'Moisturizing organic body lotion.', 12.99, 70, 'images/products/body-lotion.jpg'),
(9, 'Studio OH! Lip Balm & Lotion Set', 'Natural lip balm in various flavors.', 7.99, 120, 'images/products/lip-balm.jpg'),
(9, 'Gillette Double Edge Safety Razor', 'Zero-waste metal safety razor.', 24.99, 50, 'images/products/safety-razor.jpg'),
(9, 'Moroccanoil: Dry Shampoo Dark Tones', 'Natural powder dry shampoo.', 10.99, 80, 'images/products/dry-shampoo.jpg'),
(9, 'Purell® Advanced Hand Sanitizer Gel', 'Alcohol-based hand sanitizer gel.', 5.99, 100, 'images/products/hand-sanitizer.jpg'),

-- Cosmetic Products (10)
(10, 'Birch Babe Hydrating Face Cream', 'Anti-aging organic face cream.', 29.99, 50, 'images/products/face-cream.jpg'),
(10, 'Matte & Satin Lip Color Lipstick', 'Vegan natural lipstick in various shades.', 14.99, 60, 'images/products/lipstick.jpg'),
(10, 'Jane Iredale Amazing Base Loose Mineral Powder', 'Natural mineral powder foundation.', 24.99, 40, 'images/products/foundation.jpg'),
(10, 'Clinique Lash Power Long Wearing Mascara Black Onyx', 'Natural lengthening mascara.', 16.99, 60, 'images/products/mascara.jpg'),
(10, 'Ultimate Utopia Shadow Palette', 'Neutral eye shadow palette.', 22.99, 40, 'images/products/eyeshadow.jpg'),
(10, 'NYX Pro Makeup Pro Fix Stick Correcting Concealer 09 Neutral Tan', 'Creamy natural concealer.', 18.99, 60, 'images/products/concealer.jpg'),
(10, 'Garnier Skin Active Eye Makeup Remover', 'Gentle oil-based makeup remover.', 12.99, 70, 'images/products/makeup-remover.jpg'),
(10, 'It Cosmetics Glow With Confidence Blush 30-Medium Tan 18g', 'Natural cream blush.', 15.99, 50, 'images/products/blush.jpg'),
(10, 'Makeup Revolution Highlighter Reloaded Dare To Divulge | CDON', 'Luminous natural highlighter.', 19.99, 50, 'images/products/highlighter.jpg'),
(10, 'AIRBRUSH FLAWLESS SETTING SPRAY - ORIGINAL 100 ML', 'Makeup setting spray with aloe.', 17.99, 60, 'images/products/setting-spray.jpg'),

-- Baby & Child (10)
(11, 'Gerber Organic Mango Apple Pear Puree Baby Food Pouch (128 ml)', 'Organic pureed baby food in various flavors.', 3.99, 100, 'images/products/baby-food.jpg'),
(11, 'Baby Shampoo & Body Wash – Vita Health Fresh Market', 'Tear-free organic baby shampoo.', 8.99, 90, 'images/products/baby-shampoo.jpg'),
(11, 'Hi Baby - Diapers Assorted Sizes - 100''s - The Happy Nappy', 'Hypoallergenic organic diapers.', 14.99, 80, 'images/products/diapers.jpg'),
(11, 'Original Baby Wipes - 60 Pack | Snuggle Bugz', 'Gentle organic baby wipes.', 5.99, 100, 'images/products/baby-wipes.jpg'),
(11, 'Johnson''s Baby Lotion', 'Moisturizing organic baby lotion.', 9.99, 80, 'images/products/baby-lotion.jpg'),
(11, 'Comotomo Silicone Baby Bottles', 'BPA-free baby bottles.', 12.99, 70, 'images/products/baby-bottles.jpg'),
(11, 'Nature Teething Ring', 'Natural rubber teething toys.', 7.99, 100, 'images/products/teething-toys.jpg'),
(11, '3 Piece onesies , Desert Set', 'Soft organic cotton onesies.', 11.99, 90, 'images/products/onesies.jpg'),
(11, 'Effe Bebe Newborn Infant Graphic Solid', 'Organic cotton baby blanket.', 19.99, 60, 'images/products/baby-blanket.jpg'),
(11, 'Baby Carriers - Gear - Activity & Gear', 'Ergonomic organic baby carrier.', 49.99, 40, 'images/products/baby-carrier.jpg'),

-- Pet Care (10)
(12, 'Only Natural Pet Raw Blends Digestive Care Dog Food', 'Grain-free organic dog food.', 12.99, 50, 'images/products/dog-food.jpg'),
(12, 'Catit Creamy Chicken & Liver Cat Treats', 'Natural cat treats with salmon.', 5.99, 90, 'images/products/cat-treats.jpg'),
(12, 'WEST PAW - Tux Tough Treat Dispensing Dog Chew Toy', 'Durable natural rubber chew toys.', 8.99, 70, 'images/products/dog-toys.jpg'),
(12, 'Selection Unscented Clumping Cat Litter', 'Natural biodegradable cat litter.', 14.99, 60, 'images/products/cat-litter.jpg'),
(12, 'Purodora Purodora Pet Shampoo for Curly Coats 500ml', 'Gentle organic pet shampoo.', 9.99, 80, 'images/products/pet-shampoo.jpg'),
(12, 'UTSÅDD Dog Bed, Light Gray', 'Orthopedic organic dog bed.', 39.99, 40, 'images/products/dog-bed.jpg'),
(12, 'Whisker City® 19-in Carpet Scratching Post', 'Natural sisal scratching post.', 24.99, 50, 'images/products/cat-post.jpg'),
(12, 'Paw Brothers Professional Grade Extra Long Slicker Brush', 'Gentle grooming brush for pets.', 12.99, 70, 'images/products/pet-brush.jpg'),
(12, 'Tetra GOLDFISH FLAKES 4.52 LB', 'Natural tropical fish flakes.', 6.99, 80, 'images/products/fish-food.jpg'),
(12, 'Kingsyard Squirrel Proof Bird Feeder for Outdoors', 'Eco-friendly wooden bird feeder.', 15.99, 50, 'images/products/bird-feeder.jpg');