TRUNCATE TABLE stats;
TRUNCATE TABLE account;
TRUNCATE TABLE vehicle;
TRUNCATE TABLE faction;

INSERT INTO faction(id, name, color, maxvehicles, maxmembers, bankfreezed, bank) VALUES
(-130420408, 'Capo dei capi', 0xFF000000, 5, 5, 0, 10000000),
(741558335,  'Corleone',      0x21212100, 10, 21, 0, 10000000),
(-472884113, 'Police',        0x1A799900, 20, 21, 0, 10000000);
/*UPDATE faction SET hq='301' WHERE name='Corleone';*/
UPDATE room SET fridge='3000' WHERE id='301';



INSERT INTO `account` (`id`, `name`, `password`, `email`, `adminlevel`, `last_login`, `last_ip`, `banned`, `banned_by`, `ban_date`, `ban_expiration`) VALUES 
(1, 'Alberto_Alonso', '098f6bcd4621d373cade4e832627b4f6', 'rydencillo@gmail.com', 5, 0, '192.168.1.7', NULL, NULL, NULL, NULL),
(2, 'Donny_Montana', 'df5ea29924d39c3be8785734f13169c6', 'azy@bla.com', 5, 0, '', NULL, NULL, NULL, NULL),
(3, 'Vincent_Moretti', 'd72fbbccd9fe64c3a14f85d225a046f4', 'spidergizmo101@hotmail.com', 5, 0, '', NULL, NULL, NULL, NULL),
(4, 'Scott_Banks', 'd36af402c7ee5c39ae319321dfc02f87', 'Kevin@Marvontgroup.com', 5, 0, '', NULL, NULL, NULL, NULL),
(5, 'Wolf_Lunardi', '624b00f6bfdce5a26024a5e7d6318ecd', 'grechtyrone@hotmail.com', 5, 0, '', NULL, NULL, NULL, NULL),
(6, 'Luigi_Tortelli', 'c52fef2e4907113c5bff0ce05c1a83fc', 'mtstvns@yahoo.com', 5, 0, '', NULL, NULL, NULL, NULL),
(7, 'Yeti_Abruzzi', 'fea0f1f6fede90bd0a925b4194deac11', 'littlewhitey@gmail.com', 5, 0, '', NULL, NULL, NULL, NULL),
(8, 'Peter_Beverloo', '23f89bb8a8d63596bd02997f5d6a471e', 'peter@dmx-network.com', 5, 0, '', NULL, NULL, NULL, NULL),
(9, 'Michael_Swarts', '827ccb0eea8a706c4c34a16891f84e7b', 'ffff@fa.cf', 5, 0, '86.83.21.234', NULL, NULL, NULL, NULL),
(10, 'Raffaele_Scalisi', '08cd706ca61375c02a47b6ea26c66aba', '', 5, 0, '86.82.167.195', NULL, NULL, NULL, NULL),
(11, 'Marco_Solerno', 'f1a2987a8ae3620ed2ed5462e0e80f5e', 'saf@fgg.cca', 5, 0, '', NULL, NULL, NULL, NULL);

INSERT INTO stats(acc) VALUES
(1), (2), (3), (4), (5), (6), (7), (8), (9), (10), (11);

/*UPDATE account SET adminlevel=1 WHERE id=1;*/
UPDATE account SET adminlevel='5' WHERE id=1;
UPDATE stats SET faction='741558335', skin='124', rank='0', bank='1000000', money='500000',strength='300',upgradepts='2' WHERE acc=1;
UPDATE stats SET faction=null, rank=null, bank='1000000', money='500000',age=50 WHERE acc=3;
UPDATE stats SET bank='1000000', money=3000,age=21 WHERE acc=15;
UPDATE stats SET skin='272',age=25,bank='10000000' WHERE acc=11;
UPDATE stats SET faction='-472884113', rank='0' WHERE acc=8;
