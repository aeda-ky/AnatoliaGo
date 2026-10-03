-- ============================================================
-- AnatoliaGo - Tek Dosyalık Kurulum (Şema + Seed)
-- Bu dosya tek başına çalışır.
-- MySQL/MariaDB için hazırlanmıştır.
-- ============================================================

CREATE DATABASE IF NOT EXISTS turkey_routes
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_general_ci;

USE turkey_routes;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS route_details;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS route_favorites;
DROP TABLE IF EXISTS location_favorites;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS cities;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	first_name VARCHAR(100) NOT NULL,
	last_name VARCHAR(100) NOT NULL,
	email VARCHAR(255) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	role VARCHAR(20) NOT NULL DEFAULT 'user',
	total_routes_shared INT UNSIGNED NOT NULL DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE cities (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(150) NOT NULL,
	plate_code VARCHAR(10) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY ux_cities_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE locations (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(200) NOT NULL,
	category VARCHAR(100) NOT NULL,
	city_id INT UNSIGNED NOT NULL,
	lat DECIMAL(10, 8),
	lng DECIMAL(11, 8),
	description TEXT,
	image VARCHAR(255),
	avg_rating DECIMAL(3,2) DEFAULT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ratings (
	rating_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	location_id INT UNSIGNED,
	user_id INT UNSIGNED,
	star_count TINYINT CHECK (star_count BETWEEN 1 AND 5),
	rating_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE routes (
	route_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED,
	city_id INT UNSIGNED,
	route_name VARCHAR(255) NOT NULL,
	is_public BOOLEAN DEFAULT TRUE,
	creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY ux_user_route_name (user_id, route_name),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE route_details (
	detail_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	route_id INT UNSIGNED,
	location_id INT UNSIGNED,
	order_number INT NOT NULL,
	FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
	FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE route_saved (
	saved_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	route_id INT UNSIGNED NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY ux_user_route_saved (user_id, route_id),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE route_favorites (
	favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	route_id INT UNSIGNED NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY ux_user_route (user_id, route_id),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE location_favorites (
	favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	location_id INT UNSIGNED NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY ux_user_location (user_id, location_id),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users (first_name, last_name, email, password, role, total_routes_shared) VALUES
	('Kubra', 'Aras', 'kubra.aras@example.com', 'hashedpassword1', 'admin', 3),
	('Merve', 'Han', 'merve.han@example.com', 'hashedpassword2', 'user', 1),
	('Nisanur', 'Aydın', 'nisanur.aydin@example.com', 'hashedpassword3', 'user', 5),
	('Efe', 'Karabaş', 'efe.karabas@example.com', 'hashedpassword4', 'user', 2),
	('Nurcan', 'Karakoç', 'nurcan.karakoc@example.com', 'hashedpassword5', 'user', 4),
	('Eda', 'Kaya', 'eda.kaya@example.com', 'hashedpassword6', 'user', 0),
	('Salih', 'Ay', 'salih.ay@example.com', 'hashedpassword7', 'user', 2),
	('Mehmet', 'Sevi', 'mehmet.sevi@example.com', 'hashedpassword8', 'user', 1);

INSERT INTO cities (name, plate_code) VALUES
	('İstanbul', '34'), ('Ankara', '06'), ('İzmir', '35'), ('Sakarya', '54'), ('Antalya', '07');

-- 3. LOKASYONLAR (Her şehir için: 2 Doğa, 2 Tarihi Yer, 2 Müze, 2 Park)
INSERT INTO locations (id, name, category, city_id, lat, lng, description, image, avg_rating) VALUES
-- İSTANBUL (city_id: 1)
(1, 'Belgrad Ormanı', 'Doğa', 1, 41.1668, 29.0370, 'Yürüyüş ve koşu parkurlarıyla ünlü doğa harikası.', 'resimler/Belgrad Ormanı.jpg', 4.6),
(2, 'Şile Saklıgöl', 'Doğa', 1, 41.1737, 29.5550, 'Yapay bir baraj gölü etrafında huzurlu doğa alanı.', 'resimler/Şile Saklıgöl.jpeg', 4.3),
(3, 'Sultanahmet Camii', 'Tarihi Yer', 1, 41.0053, 28.9768, 'Mavi çinileriyle ünlü, Osmanlı döneminin ikonik camisi.', 'resimler/Sultanahmet Camii.webp', 4.8),
(4, 'Galata Kulesi', 'Tarihi Yer', 1, 41.0256, 28.9743, 'İstanbul panoramik manzarasını sunan tarihi kule.', 'resimler/Galata Kulesi.webp', 4.7),
(5, 'Topkapı Sarayı Müzesi', 'Müze', 1, 41.0128, 28.9833, 'Osmanlı padişahlarının asırlarca yaşadığı saray müze.', 'resimler/Topkapı Sarayı.jpg', 4.8),
(6, 'İstanbul Arkeoloji Müzeleri', 'Müze', 1, 41.0118, 28.9782, 'İskender Lahdi gibi eşsiz antik eserleri barındıran müze.', 'resimler/İstanbul Medeniyetler Müzesi.jpg', 4.7),
(7, 'Gülhane Parkı', 'Park', 1, 41.0139, 28.9764, 'Tarihi yarımadada saray bahçesinden halka açılan büyük park.', 'resimler/Gülhane Parkı.jpg', 4.5),
(8, 'Emirgan Korusu', 'Park', 1, 41.0698, 29.0368, 'Lale festivalleri ve boğaz manzarasıyla ünlü koru.', 'resimler/Emirgan Korusu.jpg', 4.6),

-- ANKARA (city_id: 2)
(9, 'Eymir Gölü', 'Doğa', 2, 39.8672, 32.7282, 'ODTÜ arazisinde yer alan bisiklet ve doğa yürüyüşü noktası.', 'resimler/Eymir Gölü.jpg', 4.5),
(10, 'Soğuksu Milli Parkı', 'Doğa', 2, 40.9244, 32.8128, 'Kızılcahamam’da çam ormanları ve temiz havasıyla bilinen park.', 'resimler/Soğuksu Milli Parkı.jpg', 4.4),
(11, 'Ankara Kalesi', 'Tarihi Yer', 2, 39.9417, 32.8597, 'Kentin en eski yapılarından, tarihi Ankara evlerine ev sahipliği yapar.', 'ankarakalesi.jpg', 4.5),
(12, 'Augustus Tapınağı', 'Tarihi Yer', 2, 39.9312, 32.8651, 'Roma döneminden kalan, Hacı Bayram Camii yanındaki antik tapınak.', 'augustus.jpg', 4.2),
(13, 'Anıtkabir', 'Müze', 2, 39.9250, 32.8369, 'Mustafa Kemal Atatürk’ün anıt mezarı ve tarihi müze kompleksi.', 'anitkabir.jpg', 4.9),
(14, 'Anadolu Medeniyetleri Müzesi', 'Müze', 2, 39.9235, 32.8598, 'Dünyanın en zengin Paleolitik ve Hitit koleksiyonlarından biri.', 'anadolu_med.jpg', 4.8),
(15, 'Kuğulu Park', 'Park', 2, 39.9265, 32.8588, 'Kuğuları ve yeşilliğiyle Tunalı Hilmi Caddesi’nin simgesi.', 'kugulu.jpg', 4.6),
(16, 'Gençlik Parkı', 'Park', 2, 39.9310, 32.8550, 'Cumhuriyetin ilk yıllarından beri kentin kalbinde olan lunaparklı park.', 'genclik.jpg', 4.1),

-- İZMİR (city_id: 3)
(17, 'Karagöl Tabiat Parkı', 'Doğa', 3, 38.5589, 27.0530, 'Yamanlar Dağı’nda yeşillikler içinde saklı mitolojik göl.', 'karagol.jpg', 4.4),
(18, 'Homeros Vadisi', 'Doğa', 3, 38.4553, 27.1671, 'Bornova’da doğayla iç içe, yürüyüş ve dinlenme alanı.', 'homeros.jpg', 4.2),
(19, 'Efes Antik Kenti', 'Tarihi Yer', 3, 37.9414, 27.3417, 'Dünyaca ünlü antik Roma kenti ve Celsus Kütüphanesi.', 'efes.jpg', 4.9),
(20, 'İzmir Tarihi Asansör', 'Tarihi Yer', 3, 38.4202, 27.1415, 'Mithatpaşa ile Halilrıfatpaşa’yı bağlayan tarihi yapı ve seyir terası.', 'asansor.jpg', 4.6),
(21, 'İzmir Arkeoloji ve Etnografya Müzesi', 'Müze', 3, 38.4193, 27.1388, 'Körfez kültürünü ve Ege antik buluntularını sergileyen müze.', 'izmir_arkeo.jpg', 4.5),
(22, 'Atatürk Evi Müzesi', 'Müze', 3, 38.4165, 27.1324, 'Kordon’da Atatürk’ün İzmir ziyaretlerinde kaldığı tarihi köşk.', 'ataturk_evi.jpg', 4.7),
(23, 'Kordon Boyu', 'Park', 3, 38.4260, 27.1434, 'Çimleri üzerinde körfez manzarasının keyfinin çıkarıldığı sahil parkı.', 'kordon.jpg', 4.7),
(24, 'Kültürpark (Fuar)', 'Park', 3, 38.4221, 27.1333, 'Geniş yeşil alanı, yürüyüş parkurları ve ağaçlarıyla kentin akciğeri.', 'kulturpark.jpg', 4.5),

-- SAKARYA (city_id: 4)
(25, 'Sapanca Gölü', 'Doğa', 4, 40.6578, 30.3475, 'Etrafındaki yeşillikler ve dinlenme tesisleriyle ünlü göl.', 'sapanca.jpg', 4.6),
(26, 'Poyrazlar Gölü Tabiat Parkı', 'Doğa', 4, 40.7067, 30.4607, 'Kuş gözlemciliği ve piknik için ideal doğal göl alanı.', 'poyrazlar.jpg', 4.4),
(27, 'Justinianus Köprüsü (Beşköprü)', 'Tarihi Yer', 4, 40.7890, 30.4104, 'Bizans döneminden günümüze ulaşan devasa tarihi köprü.', 'beskopru.jpg', 4.3),
(28, 'Sakarya Ulu Camii', 'Tarihi Yer', 4, 40.7772, 30.3981, 'Adapazarı merkezinde yer alan kentin sembolik tarihi camisi.', 'sakarya_ulu.jpg', 4.4),
(29, 'Sakarya Müzesi (Atatürk Evi)', 'Müze', 4, 40.7763, 30.3988, 'Arkeolojik eserler ve etnografik kültür ögelerinin sergilendiği müze.', 'sakarya_muzesi.jpg', 4.2),
(30, 'Deprem Kültür Müzesi', 'Müze', 4, 40.7718, 30.3978, '1999 depreminin anısını ve bilincini yaşatan anıt müze.', 'deprem_muzesi.jpg', 4.5),
(31, 'Aziz Duran Parkı (Kent Park)', 'Park', 4, 40.8040, 30.4040, 'Çark Deresi kenarında, geniş yeşil alanlara sahip şehir parkı.', 'kentpark.jpg', 4.5),
(32, 'Sakarya İl Ormanı Tabiat Parkı', 'Park', 4, 40.7050, 30.3800, 'Çam ağaçları arasında mangal, yürüyüş ve konaklama imkanı sunan park.', 'ilormani.jpg', 4.3),

-- ANTALYA (city_id: 5)
(33, 'Düden Şelalesi', 'Doğa', 5, 36.8960, 30.7482, 'Falezlerden Akdeniz’e dökülen doğa harikası şelale.', 'duden.jpg', 4.6),
(34, 'Kurşunlu Şelalesi Tabiat Parkı', 'Doğa', 5, 36.8567, 30.6178, 'Kanyon içindeki bitki tünelleri ve küçük şelaleleriyle saklı cennet.', 'kursunlu.jpg', 4.5),
(35, 'Kaleiçi', 'Tarihi Yer', 5, 36.8947, 30.7102, 'Tarihi Osmanlı evleri ve dar sokaklarıyla Antalya’nın kalbi.', 'kaleici.jpg', 4.8),
(36, 'Hadrian Kapısı (Üçkapılar)', 'Tarihi Yer', 5, 36.8846, 30.7070, 'Roma İmparatoru Hadrianus adına yapılan görkemli antik kapı.', 'hadrian.jpg', 4.7),
(37, 'Antalya Müzesi', 'Müze', 5, 36.8841, 30.7006, 'Perge Antik Kenti heykellerinin sergilendiği, ödüllü devasa müze.', 'antalya_muzesi.jpg', 4.9),
(38, 'Suna & İnan Kıraç Kaleiçi Müzesi', 'Müze', 5, 36.8890, 30.7045, 'Kaleiçi kültürünü yansıtan geleneksel etnografya müzesi.', 'kirac_muze.jpg', 4.4),
(39, 'Karaalioğlu Parkı', 'Park', 5, 36.8884, 30.7025, 'Körfez ve falez manzarasını yukarıdan gören tarihi büyük park.', 'karaalioglu.jpg', 4.6),
(40, 'Atatürk Parkı', 'Park', 5, 36.8806, 30.7064, 'Sahil şeridi boyunca uzanan, restoran ve yürüyüş yolları barındıran park.', 'antalya_ataturk_park.jpg', 4.5);


-- 4. RATINGS (Puanlamalar - Güncellenmiş ve Çeşitlendirilmiş)
INSERT INTO ratings (location_id, user_id, star_count) VALUES
(1, 2, 5), (5, 2, 5), (25, 2, 4), (26, 2, 5), -- Merve
(3, 1, 4), (5, 1, 5), (33, 1, 4), (35, 1, 5), -- Kübra
(13, 3, 5), (14, 3, 5), (39, 3, 4), (40, 3, 5), -- Nisanur
(25, 4, 4), (31, 4, 4), (9, 4, 3), (11, 4, 4),  -- Efe
(19, 5, 5), (20, 5, 4), (23, 5, 5), (24, 5, 4),  -- Nurcan
(4, 6, 4), (7, 6, 5),                            -- Eda 
(17, 7, 3), (20, 7, 4), (23, 7, 4),              -- Salih 
(13, 8, 5), (15, 8, 4), (10, 8, 3);              -- Mehmet 


-- 5. ROUTES (Rota Başlıkları - 14 Dengeli Rota)
INSERT INTO routes (user_id, city_id, route_name, is_public) VALUES
-- İstanbul Rotaları
(2, 1, 'Merve\'nin İstanbul Keşfi', TRUE),
(1, 1, 'Kübra\'nın Tarihi Yarımada Turu', TRUE),
(6, 1, 'Eda ile İstanbul Doğa ve Parkları', TRUE),
-- Ankara Rotaları
(3, 2, 'Nisanur\'un Ankara Kültür Rehberi', TRUE),
(8, 2, 'Mehmet\'in Ankara Tarih Yolculuğu', TRUE),
-- İzmir Rotaları
(5, 3, 'Nurcan\'ın Ege İncisi İzmir Turu', TRUE),
(7, 3, 'Salih\'in İzmir Parkları ve Doğa Gezisi', TRUE),
-- Sakarya Rotaları
(4, 4, 'Efe\'nin Sakarya Doğa ve Macera Rotası', TRUE),
(2, 4, 'Merve\'nin Hafta Sonu Sakarya Kaçamağı', FALSE),
(5, 4, 'Nurcan\'ın Sakarya Kültür Turu', TRUE),
-- Antalya Rotaları
(1, 5, 'Kübra\'nın Antalya Sahil ve Tarih Turu', TRUE),
(3, 5, 'Nisanur\'un Antalya Şelaleler ve Müzeler Gezisi', TRUE),
(4, 5, 'Efe\'nin Akdeniz Esintisi', TRUE),
(7, 5, 'Salih\'in Antik Antalya Rotası', TRUE);


-- 6. ROUTE_DETAILS (Rota Durakları - Yeni lokasyon kimliklerine göre)
INSERT INTO route_details (route_id, location_id, order_number) VALUES
-- Rota 1 (Merve - İstanbul Keşfi: Doğa, Tarih, Müze, Park karışık)
(1, 1, 1), (1, 3, 2), (1, 5, 3), (1, 7, 4),
-- Rota 2 (Kübra - İstanbul Tarihi Yarımada)
(2, 3, 1), (2, 4, 2), (2, 5, 3), (2, 6, 4),
-- Rota 3 (Eda - İstanbul Doğa ve Parklar)
(3, 1, 1), (3, 2, 2), (3, 7, 3), (3, 8, 4),

-- Rota 4 (Nisanur - Ankara Kültür Rehberi)
(4, 13, 1), (4, 14, 2), (4, 11, 3), (4, 15, 4),
-- Rota 5 (Mehmet - Ankara Tarih Yolculuğu)
(5, 11, 1), (5, 12, 2), (5, 13, 3), (5, 16, 4),

-- Rota 6 (Nurcan - İzmir İncisi: Karışık Klasik Tur)
(6, 19, 1), (6, 20, 2), (6, 22, 3), (6, 23, 4),
-- Rota 7 (Salih - İzmir Parkları ve Doğa)
(7, 17, 1), (7, 18, 2), (7, 23, 3), (7, 24, 4),

-- Rota 8 (Efe - Sakarya Doğa ve Macera)
(8, 25, 1), (8, 26, 2), (8, 31, 3), (8, 32, 4),
-- Rota 9 (Merve - Hafta Sonu Sakarya Kaçamağı - Gizli Rota)
(9, 25, 1), (9, 27, 2), (9, 29, 3), (9, 31, 4),
-- Rota 10 (Nurcan - Sakarya Kültür)
(10, 27, 1), (10, 28, 2), (10, 29, 3), (10, 30, 4),

-- Rota 11 (Kübra - Antalya Sahil ve Tarih)
(11, 35, 1), (11, 36, 2), (11, 39, 3), (11, 33, 4),
-- Rota 12 (Nisanur - Antalya Şelaleler ve Müzeler)
(12, 33, 1), (12, 34, 2), (12, 37, 3), (12, 38, 4),
-- Rota 13 (Efe - Akdeniz Esintisi)
(13, 33, 1), (13, 35, 2), (13, 36, 3), (13, 40, 4),
-- Rota 14 (Salih - Antik Antalya)
(14, 35, 1), (14, 36, 2), (14, 37, 3), (14, 39, 4);

-- Yabancı anahtar kontrollerini tekrar açmak için
SET FOREIGN_KEY_CHECKS = 1;

-- Mevcut veritabaninda resim esleme guncellemeleri (opsiyonel)
UPDATE locations SET image = 'resimler/Belgrad Ormanı.jpg' WHERE id = 1;
UPDATE locations SET image = 'resimler/Şile Saklıgöl.jpeg' WHERE id = 2;
UPDATE locations SET image = 'resimler/Sultanahmet Camii.webp' WHERE id = 3;
UPDATE locations SET image = 'resimler/Galata Kulesi.webp' WHERE id = 4;
UPDATE locations SET image = 'resimler/Topkapı Sarayı.jpg' WHERE id = 5;
UPDATE locations SET image = 'resimler/İstanbul Medeniyetler Müzesi.jpg' WHERE id = 6;
UPDATE locations SET image = 'resimler/Gülhane Parkı.jpg' WHERE id = 7;
UPDATE locations SET image = 'resimler/Emirgan Korusu.jpg' WHERE id = 8;
UPDATE locations SET image = 'resimler/Eymir Gölü.jpg' WHERE id = 9;
UPDATE locations SET image = 'resimler/Soğuksu Milli Parkı.jpg' WHERE id = 10;