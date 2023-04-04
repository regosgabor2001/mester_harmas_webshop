-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2023. Már 21. 10:32
-- Kiszolgáló verziója: 10.4.6-MariaDB
-- PHP verzió: 7.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `mester_harmas_webshop_db`
--
CREATE DATABASE IF NOT EXISTS `mester_harmas_webshop_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
USE `mester_harmas_webshop_db`;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `evi_bevetel`
--

CREATE TABLE `evi_bevetel` (
  `id` int(10) NOT NULL,
  `ev` int(100) NOT NULL,
  `honap` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
  `bevetel` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `evi_bevetel`
--

INSERT INTO `evi_bevetel` (`id`, `ev`, `honap`, `bevetel`) VALUES
(1, 2023, 'januar', 0),
(2, 2023, 'februar', 0),
(3, 2023, 'marcius', 444000),
(4, 2023, 'aprilis', 0),
(5, 2023, 'majus', 0),
(6, 2023, 'junius', 0),
(7, 2023, 'julius', 0),
(8, 2023, 'augusztus', 0),
(9, 2023, 'szeptember', 0),
(10, 2023, 'oktober', 0),
(11, 2023, 'november', 0),
(12, 2023, 'december', 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalo`
--

CREATE TABLE `felhasznalo` (
  `id` int(11) NOT NULL,
  `felhasznalonev` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `jelszo` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `vezeteknev` varchar(100) NOT NULL,
  `keresztnev` varchar(100) NOT NULL,
  `telefonszam` varchar(100) NOT NULL,
  `jogosultsag_id` int(11) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `sztatusz` int(11) NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`id`, `felhasznalonev`, `jelszo`, `vezeteknev`, `keresztnev`, `telefonszam`, `jogosultsag_id`, `email`, `sztatusz`, `token`) VALUES
(28, 'webshop', '$argon2id$v=19$m=12,t=3,p=1$NjN1a2s2cGI2MDYwMDAwMA$8oM4R7D85B8UrIH5xqqGTQ', '', '', '', 1, 'mester.harmas.webshop@gmail.com', 1, '036fde44ce'),
(38, 'RG', '$argon2id$v=19$m=65536,t=4,p=1$cGk1L213TnlGa1ZCUEo5Qg$0ZVJ2aFDp0C4JosSI3v3yfbEW3aAQFvcLerMmQDGvSw', 'Regős', 'Gábor', '06307562195', 2, 'regosgabor2001@gmail.com', 1, '38fb31963f');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalo_szallitasi_adatok`
--

CREATE TABLE `felhasznalo_szallitasi_adatok` (
  `id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `szallitasi_cim1` varchar(200) NOT NULL,
  `szallitasi_cim2` varchar(200) DEFAULT NULL,
  `varos` varchar(100) NOT NULL,
  `iranyitoszam` int(11) NOT NULL,
  `orszag` varchar(100) NOT NULL,
  `telefonszam` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `felhasznalo_szallitasi_adatok`
--

INSERT INTO `felhasznalo_szallitasi_adatok` (`id`, `felhasznalo_id`, `szallitasi_cim1`, `szallitasi_cim2`, `varos`, `iranyitoszam`, `orszag`, `telefonszam`) VALUES
(15, 38, 'Akácos út 71.', '', 'Sződliget', 2133, 'Magyarország', '06307562195');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `jogosultsagok`
--

CREATE TABLE `jogosultsagok` (
  `id` int(11) NOT NULL,
  `megnevezes` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `jogosultsagok`
--

INSERT INTO `jogosultsagok` (`id`, `megnevezes`) VALUES
(1, 'Admin'),
(2, 'Vásárló');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `log_kategoria_id` int(11) NOT NULL,
  `azonosito` varchar(100) NOT NULL,
  `idopont` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `log`
--

INSERT INTO `log` (`id`, `felhasznalo_id`, `log_kategoria_id`, `azonosito`, `idopont`) VALUES
(51, 28, 4, '-', '2023-03-21 09:32:41');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `log_kategoriak`
--

CREATE TABLE `log_kategoriak` (
  `id` int(11) NOT NULL,
  `esemeny` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `log_kategoriak`
--

INSERT INTO `log_kategoriak` (`id`, `esemeny`) VALUES
(1, 'regisztráció'),
(2, 'bejelentkezés'),
(3, 'vásárlás'),
(4, 'kijelentkezés'),
(5, 'termék törlése'),
(6, 'termék módosítása'),
(7, 'új termék'),
(8, 'kategória törlése'),
(9, 'kategória módosítása'),
(10, 'új kategória'),
(11, 'márka törlése'),
(12, 'márka módosítása'),
(13, 'új márka'),
(14, 'admin jogosultság adása'),
(15, 'felhasználó törlése'),
(16, 'rendelés küldése'),
(17, 'rendelés törlése');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `markak`
--

CREATE TABLE `markak` (
  `id` int(11) NOT NULL,
  `markanev` varchar(20) COLLATE utf8_hungarian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `markak`
--

INSERT INTO `markak` (`id`, `markanev`) VALUES
(1, 'Nike'),
(2, 'Adidas'),
(3, 'Calvin Klein'),
(4, 'Guess'),
(5, 'Tommy Hilfiger'),
(6, 'EA7 Emporio Armani'),
(7, 'HunkeMöller'),
(8, 'Polo Ralph Lauren'),
(9, 'ICHI'),
(10, 'CK');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `megrendeles`
--

CREATE TABLE `megrendeles` (
  `id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `megrendeles_datuma` date NOT NULL,
  `megrendeles_statusz` int(11) NOT NULL,
  `szallitasi_cim` varchar(100) NOT NULL,
  `varos` varchar(100) NOT NULL,
  `iranyitoszam` int(11) NOT NULL,
  `orszag` varchar(100) NOT NULL,
  `telefonszam` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `megrendelt_termekek`
--

CREATE TABLE `megrendelt_termekek` (
  `id` int(11) NOT NULL,
  `megrendeles_id` int(11) NOT NULL,
  `termek_id` int(11) NOT NULL,
  `meret_id` int(11) NOT NULL,
  `mennyiseg` int(11) NOT NULL,
  `ar` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `meretek`
--

CREATE TABLE `meretek` (
  `id` int(11) NOT NULL,
  `megnevezes` varchar(4) COLLATE utf8_hungarian_ci NOT NULL,
  `sorrend` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `meretek`
--

INSERT INTO `meretek` (`id`, `megnevezes`, `sorrend`) VALUES
(1, 'S', 2),
(2, 'M', 3),
(3, 'L', 4),
(4, 'XL', 5),
(5, 'XS', 1),
(6, 'XXL', 6);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `nem`
--

CREATE TABLE `nem` (
  `id` int(11) NOT NULL,
  `megnevezes` varchar(10) COLLATE utf8_hungarian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `nem`
--

INSERT INTO `nem` (`id`, `megnevezes`) VALUES
(2, 'Férfi'),
(3, 'Női'),
(4, 'Gyermek');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek`
--

CREATE TABLE `termek` (
  `id` int(11) NOT NULL,
  `nev` varchar(100) NOT NULL,
  `cikkszam` varchar(100) NOT NULL,
  `marka_id` int(11) NOT NULL,
  `leiras` text NOT NULL,
  `nem_id` int(11) NOT NULL,
  `ar` int(11) NOT NULL,
  `kep_nev` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `termek`
--

INSERT INTO `termek` (`id`, `nev`, `cikkszam`, `marka_id`, `leiras`, `nem_id`, `ar`, `kep_nev`) VALUES
(4, 'Fekete Nike Sportwear Póló', '01000123', 1, 'A Nike Sportswear póló hétköznapi pamutból, klasszikus szabással készült, így már az első viseléskor ismerős érzést nyújt. A klasszikus Nike logók gyűjteménye vintage hangulatot kölcsönöz ennek hétköznapi viseletként tökéletes alapdarabnak.', 2, 18000, 'nike_Sportwear1.jpg'),
(5, 'Fekete EA7 Emporio Armani Átmenetikabát', '00123467', 6, 'Pehelykabát 6LPB11 PNADZ 1200 Fekete Regular Fit', 2, 150000, 'emporioArmaniFeketeAtmenetiKabat.jpg'),
(6, 'Fekete Calvin Klein Ing', '01237864', 3, 'Ing', 2, 36000, 'CKFeketeIng.jpg'),
(7, 'Fekete HunkeMöller Melltartó \'Talia\'', '1003417', 7, 'Melltartó \'Talia\'', 3, 17000, 'feketeHunkeMollerMelltarto.jpg'),
(8, 'Szürke Melír GUESS Póló', '01114562', 4, 'Póló', 3, 14000, 'szurkeMelirGuessPolo.jpg'),
(11, 'Barna Nike Sportswear Tréning dzseki', '1984001', 1, 'Tréning dzseki', 2, 48000, 'BarnaNikeTech.jpg'),
(12, 'Kék Polo Ralph Lauren Póló', '003453215', 8, 'Póló', 2, 70990, 'poloRalphLaurenKekZold.jpg'),
(13, 'Fekete Nike Sportswear Nadrág', '3214210', 1, 'Nadrág', 2, 13990, 'feketeNikeSportswearNadrag.jpg'),
(14, 'Kék, Világoskék Polo Ralph Lauren Ing', '0178889', 8, 'Ing', 2, 51000, 'kekVilagosKekPoloRalphLaurenIng.jpg'),
(15, 'Fekete ICHI szoknya', '1000653', 9, 'Fekete ICHI Szoknyák\r\nDizájn & Extrák\r\nUniverzális színek\r\nÁtlapolós dizájn\r\nHajlított mandzsetta\r\nLevarrt szegély\r\nFarzsebek\r\nTon inTon tűzések\r\nSima szövet\r\nCikkszám. ICH1476001000001\r\nMéret & Szabá', 3, 24000, 'feketeIchiSzoknya.jpg'),
(17, 'Fekete Nike Sportswear Átmeneti Dzseki', 'NIS3956001000001', 1, 'Fekete Nike Sportswear Átmeneti dzseki\r\nDizájn & Extrák\r\nLogó nyomtatás\r\nSteppelt dzseki\r\nEgyenes alsó szegély\r\nKapucni bujtatott zsinórral\r\nOldalsó zsebek\r\nSteppelések\r\nTon inTon tűzések\r\nCímke nyomtatás\r\nEnyhén bélelt\r\nCipzár\r\nÚjrahasznosított poliészter\r\n', 3, 59000, 'feketeNikeSportswearAtmenetiKabat.jpg'),
(18, 'Fekete ADIDAS PERFORMANCE Sport top', 'ADI9404002000001', 2, 'Funkcionalitás\r\nSportág: Fitnesz\r\nSportág: Életmód\r\nFunkciók: légáteresztő\r\nFunkciók: Nedvességelvezető\r\nTechnológia: ClimaLite\r\nTechnológia: AEROREADY\r\nTechnológia: CLIMACHILL\r\n\r\nMéret & Szabás\r\nUjjhossz: Ujjatlan\r\nHossz: Normál hosszúságú\r\nFazon: normál fazon', 3, 14990, 'feketeAdidasPerformanceSportTop.jpg'),
(19, 'Sötét-Rózsaszín TOMMY HILFIGER Blézer', 'THS9bo0001000001', 5, 'Sötét-Rózsaszín TOMMY HILFIGER Blézer\r\nDizájn & Extrák\r\nUniverzális színek\r\nLevarrt szegély\r\nPaszpólos zsebek\r\nTon inTon tűzések\r\nRészben bélelt\r\nGombos lezárás\r\n\r\nMéret & Szabás\r\nUjjhossz: Hosszú ujj\r\nHossz: Normál hosszúságú\r\nFazon: normál fazon\r\nA modell 1.77m magas és 36-es méretet visel', 3, 119000, 'SotetRozsaszinTommyHilfigerBlezer.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek_kategoria`
--

CREATE TABLE `termek_kategoria` (
  `id` int(11) NOT NULL,
  `nev` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `termek_kategoria`
--

INSERT INTO `termek_kategoria` (`id`, `nev`) VALUES
(1, 'Összes termék'),
(2, 'Pólók'),
(3, 'Kapucnis pulóverek és melegítőfelsők'),
(4, 'Rövidnadrágok'),
(5, 'Tréningruhák'),
(6, 'Mezek'),
(7, 'Kabátok'),
(8, 'Nadrágok'),
(9, 'Legginsek'),
(10, 'Fehérneműk'),
(12, 'Ingek'),
(13, 'Alsóneműk'),
(14, 'Szoknyák'),
(15, 'Blézerek');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek_kepek`
--

CREATE TABLE `termek_kepek` (
  `id` int(11) NOT NULL,
  `termek_id` int(11) NOT NULL,
  `kep_nev` varchar(100) COLLATE utf8_hungarian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `termek_kepek`
--

INSERT INTO `termek_kepek` (`id`, `termek_id`, `kep_nev`) VALUES
(1, 4, 'nike_Sportwear2.jpg'),
(2, 4, 'nike_Sportwear3.jpg'),
(3, 4, 'nike_Sportwear4.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek_meretek`
--

CREATE TABLE `termek_meretek` (
  `termek_id` int(11) NOT NULL,
  `meret_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `termek_meretek`
--

INSERT INTO `termek_meretek` (`termek_id`, `meret_id`) VALUES
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(5, 2),
(5, 3),
(5, 4),
(4, 5),
(6, 5),
(6, 1),
(6, 2),
(6, 4),
(11, 1),
(11, 2),
(11, 3),
(11, 5),
(12, 1),
(12, 5),
(12, 2),
(12, 3),
(12, 4),
(13, 1),
(13, 2),
(13, 3),
(13, 4),
(13, 6),
(14, 1),
(14, 2),
(14, 3),
(14, 4),
(8, 5),
(8, 1),
(8, 2),
(8, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek_termek_kategoria`
--

CREATE TABLE `termek_termek_kategoria` (
  `termek_id` int(11) NOT NULL,
  `kategoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `termek_termek_kategoria`
--

INSERT INTO `termek_termek_kategoria` (`termek_id`, `kategoria_id`) VALUES
(4, 1),
(4, 2),
(5, 1),
(5, 7),
(6, 1),
(6, 12),
(7, 1),
(7, 10),
(8, 1),
(8, 2),
(11, 1),
(11, 3),
(11, 5),
(12, 1),
(12, 2),
(13, 1),
(13, 4),
(13, 5),
(14, 1),
(14, 12),
(15, 1),
(15, 14),
(17, 1),
(17, 7),
(18, 1),
(18, 5),
(19, 1),
(19, 15);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `evi_bevetel`
--
ALTER TABLE `evi_bevetel`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jogosultsag_id` (`jogosultsag_id`);

--
-- A tábla indexei `felhasznalo_szallitasi_adatok`
--
ALTER TABLE `felhasznalo_szallitasi_adatok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`);

--
-- A tábla indexei `jogosultsagok`
--
ALTER TABLE `jogosultsagok`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`),
  ADD KEY `log_kategoria_id` (`log_kategoria_id`);

--
-- A tábla indexei `log_kategoriak`
--
ALTER TABLE `log_kategoriak`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `markak`
--
ALTER TABLE `markak`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `megrendeles`
--
ALTER TABLE `megrendeles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`);

--
-- A tábla indexei `megrendelt_termekek`
--
ALTER TABLE `megrendelt_termekek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `megrendeles_id` (`megrendeles_id`),
  ADD KEY `termek_id` (`termek_id`),
  ADD KEY `meret_id` (`meret_id`);

--
-- A tábla indexei `meretek`
--
ALTER TABLE `meretek`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `nem`
--
ALTER TABLE `nem`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `termek`
--
ALTER TABLE `termek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nem_id` (`nem_id`),
  ADD KEY `nem_id_2` (`nem_id`),
  ADD KEY `ar_2` (`ar`),
  ADD KEY `marka_id` (`marka_id`);

--
-- A tábla indexei `termek_kategoria`
--
ALTER TABLE `termek_kategoria`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `termek_kepek`
--
ALTER TABLE `termek_kepek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `termek_id` (`termek_id`);

--
-- A tábla indexei `termek_meretek`
--
ALTER TABLE `termek_meretek`
  ADD KEY `termek_id` (`termek_id`),
  ADD KEY `meret_id` (`meret_id`);

--
-- A tábla indexei `termek_termek_kategoria`
--
ALTER TABLE `termek_termek_kategoria`
  ADD PRIMARY KEY (`termek_id`,`kategoria_id`),
  ADD KEY `kategoria_id` (`kategoria_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `evi_bevetel`
--
ALTER TABLE `evi_bevetel`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT a táblához `felhasznalo`
--
ALTER TABLE `felhasznalo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT a táblához `felhasznalo_szallitasi_adatok`
--
ALTER TABLE `felhasznalo_szallitasi_adatok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT a táblához `jogosultsagok`
--
ALTER TABLE `jogosultsagok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT a táblához `log_kategoriak`
--
ALTER TABLE `log_kategoriak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT a táblához `markak`
--
ALTER TABLE `markak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT a táblához `megrendeles`
--
ALTER TABLE `megrendeles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `megrendelt_termekek`
--
ALTER TABLE `megrendelt_termekek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT a táblához `meretek`
--
ALTER TABLE `meretek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `nem`
--
ALTER TABLE `nem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `termek`
--
ALTER TABLE `termek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT a táblához `termek_kategoria`
--
ALTER TABLE `termek_kategoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT a táblához `termek_kepek`
--
ALTER TABLE `termek_kepek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD CONSTRAINT `felhasznalo_ibfk_1` FOREIGN KEY (`jogosultsag_id`) REFERENCES `jogosultsagok` (`id`);

--
-- Megkötések a táblához `felhasznalo_szallitasi_adatok`
--
ALTER TABLE `felhasznalo_szallitasi_adatok`
  ADD CONSTRAINT `felhasznalo_szallitasi_adatok_ibfk_1` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`id`);

--
-- Megkötések a táblához `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`log_kategoria_id`) REFERENCES `log_kategoriak` (`id`),
  ADD CONSTRAINT `log_ibfk_2` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`id`);

--
-- Megkötések a táblához `megrendeles`
--
ALTER TABLE `megrendeles`
  ADD CONSTRAINT `megrendeles_ibfk_2` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`id`);

--
-- Megkötések a táblához `megrendelt_termekek`
--
ALTER TABLE `megrendelt_termekek`
  ADD CONSTRAINT `megrendelt_termekek_ibfk_1` FOREIGN KEY (`termek_id`) REFERENCES `termek` (`id`),
  ADD CONSTRAINT `megrendelt_termekek_ibfk_2` FOREIGN KEY (`megrendeles_id`) REFERENCES `megrendeles` (`id`),
  ADD CONSTRAINT `megrendelt_termekek_ibfk_3` FOREIGN KEY (`meret_id`) REFERENCES `meretek` (`id`);

--
-- Megkötések a táblához `termek`
--
ALTER TABLE `termek`
  ADD CONSTRAINT `termek_ibfk_1` FOREIGN KEY (`nem_id`) REFERENCES `nem` (`id`),
  ADD CONSTRAINT `termek_ibfk_2` FOREIGN KEY (`marka_id`) REFERENCES `markak` (`id`);

--
-- Megkötések a táblához `termek_kepek`
--
ALTER TABLE `termek_kepek`
  ADD CONSTRAINT `termek_kepek_ibfk_1` FOREIGN KEY (`termek_id`) REFERENCES `termek` (`id`);

--
-- Megkötések a táblához `termek_meretek`
--
ALTER TABLE `termek_meretek`
  ADD CONSTRAINT `termek_meretek_ibfk_1` FOREIGN KEY (`termek_id`) REFERENCES `termek` (`id`),
  ADD CONSTRAINT `termek_meretek_ibfk_2` FOREIGN KEY (`meret_id`) REFERENCES `meretek` (`id`);

--
-- Megkötések a táblához `termek_termek_kategoria`
--
ALTER TABLE `termek_termek_kategoria`
  ADD CONSTRAINT `termek_termek_kategoria_ibfk_1` FOREIGN KEY (`termek_id`) REFERENCES `termek` (`id`),
  ADD CONSTRAINT `termek_termek_kategoria_ibfk_2` FOREIGN KEY (`kategoria_id`) REFERENCES `termek_kategoria` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
