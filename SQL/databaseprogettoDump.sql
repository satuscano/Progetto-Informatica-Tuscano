-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2026 at 12:07 PM
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
-- Database: `databaseprogetto`
--

-- --------------------------------------------------------

--
-- Table structure for table `ambulatorio`
--

CREATE TABLE `ambulatorio` (
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `piano` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ambulatorio`
--

INSERT INTO `ambulatorio` (`codiceAmbulatorio`, `codiceReparto`, `piano`) VALUES
(101, 1, 1),
(102, 2, 2),
(103, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `esame`
--

CREATE TABLE `esame` (
  `codiceEsame` int(11) NOT NULL,
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceMedico` varchar(10) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `referto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esame`
--

INSERT INTO `esame` (`codiceEsame`, `codiceAmbulatorio`, `codiceMedico`, `codiceFiscale`, `diagnosi`, `referto`) VALUES
(201, 101, 'MED001', 'PLLMRC95D10H501A', 'Controllo pressione', 'Valori stabili'),
(202, 102, 'MED002', 'CNTLRA88E22H501B', 'Cefalea ricorrente', 'Consigliata RM'),
(203, 103, 'MED003', 'FRNSFN70A01H501C', 'Dolore articolare', 'Artrosi lieve'),
(204, 103, 'MED001', 'CNTLRA88E22H501B', 'Diagnosi di esempio', 'Referto di esempio'),
(205, 101, 'MED001', 'CNTLRA88E22H501B', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fattura`
--

CREATE TABLE `fattura` (
  `codiceFattura` int(11) NOT NULL,
  `codicePagamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fattura`
--

INSERT INTO `fattura` (`codiceFattura`, `codicePagamento`) VALUES
(401, 301),
(402, 302),
(403, 303);

-- --------------------------------------------------------

--
-- Table structure for table `medico`
--

CREATE TABLE `medico` (
  `codiceMedico` varchar(10) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `codiceSpecializzazione` varchar(20) DEFAULT NULL,
  `orario` varchar(50) DEFAULT NULL,
  `codiceFiscale` varchar(16) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `primario` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medico`
--

INSERT INTO `medico` (`codiceMedico`, `codiceReparto`, `codiceSpecializzazione`, `orario`, `codiceFiscale`, `nome`, `cognome`, `primario`) VALUES
('MED001', 1, 'SPEC001', '08-12', 'RSSMRA80A01H501Z', 'Mario', 'Rossi', 1),
('MED002', 2, 'SPEC002', '14-18', 'BNCLGI75B22H501X', 'Luigi', 'Bianchi', 0),
('MED003', 3, 'SPEC003', '08-12', 'VRDGNN82C15H501Y', 'Gianna', 'Verdi', 0);

-- --------------------------------------------------------

--
-- Table structure for table `medico_orariolavoro`
--

CREATE TABLE `medico_orariolavoro` (
  `codiceMedico` varchar(10) NOT NULL,
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medico_orariolavoro`
--

INSERT INTO `medico_orariolavoro` (`codiceMedico`, `giorno`, `oraInizio`) VALUES
('MED001', 'LU', 8),
('MED002', 'LU', 14),
('MED003', 'MA', 8);

-- --------------------------------------------------------

--
-- Table structure for table `orariolavoro`
--

CREATE TABLE `orariolavoro` (
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orariolavoro`
--

INSERT INTO `orariolavoro` (`giorno`, `oraInizio`, `oraFine`) VALUES
('LU', 8, 12),
('LU', 14, 18),
('MA', 8, 12),
('ME', 14, 18);

-- --------------------------------------------------------

--
-- Table structure for table `pagamento`
--

CREATE TABLE `pagamento` (
  `codicePagamento` int(11) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `dataPagamento` date NOT NULL,
  `ora` int(11) DEFAULT NULL,
  `minuti` int(11) DEFAULT NULL,
  `somma` decimal(10,2) NOT NULL,
  `metodo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pagamento`
--

INSERT INTO `pagamento` (`codicePagamento`, `codiceFiscale`, `dataPagamento`, `ora`, `minuti`, `somma`, `metodo`) VALUES
(301, 'PLLMRC95D10H501A', '2025-01-10', 9, 30, 50.00, 'Carta'),
(302, 'CNTLRA88E22H501B', '2025-01-11', 15, 0, 80.00, 'Contanti'),
(303, 'FRNSFN70A01H501C', '2025-01-12', 9, 15, 60.00, 'Carta');

-- --------------------------------------------------------

--
-- Table structure for table `paziente`
--

CREATE TABLE `paziente` (
  `codiceFiscale` varchar(16) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `dataNascita` date NOT NULL,
  `anamnesi` text DEFAULT NULL,
  `ind_cap` varchar(10) DEFAULT NULL,
  `ind_citta` varchar(50) DEFAULT NULL,
  `ind_via` varchar(50) DEFAULT NULL,
  `ind_civico` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paziente`
--

INSERT INTO `paziente` (`codiceFiscale`, `nome`, `cognome`, `dataNascita`, `anamnesi`, `ind_cap`, `ind_citta`, `ind_via`, `ind_civico`) VALUES
('CNTLRA88E22H501B', 'Laura', 'Conti', '1988-05-22', 'Emicrania', '11100', 'Aosta', 'Via Torino', '6'),
('FRNSFN70A01H501C', 'Stefano', 'Ferrari', '1970-01-01', 'Dolore ginocchio', '11100', 'Aosta', 'Via Milano', '20'),
('PLLMRC95D10H501A', 'Marco', 'Pellegrini', '1995-04-10', 'Ipertensione', '11100', 'Aosta', 'Via Roma', '10');

-- --------------------------------------------------------

--
-- Table structure for table `reparto`
--

CREATE TABLE `reparto` (
  `codiceReparto` int(11) NOT NULL,
  `nomeReparto` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reparto`
--

INSERT INTO `reparto` (`codiceReparto`, `nomeReparto`) VALUES
(1, 'Cardiologia'),
(2, 'Neurologia'),
(3, 'Ortopedia');

-- --------------------------------------------------------

--
-- Table structure for table `specializzazione`
--

CREATE TABLE `specializzazione` (
  `codiceSpecializzazione` varchar(20) NOT NULL,
  `codiceMedico` varchar(10) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `titolo` varchar(50) DEFAULT NULL,
  `dataConseguimento` date DEFAULT NULL,
  `votoConseguimento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specializzazione`
--

INSERT INTO `specializzazione` (`codiceSpecializzazione`, `codiceMedico`, `tipo`, `titolo`, `dataConseguimento`, `votoConseguimento`) VALUES
('SPEC001', 'MED001', 'Clinica', 'Cardiologia', '2008-06-20', 110),
('SPEC002', 'MED002', 'Clinica', 'Neurologia', '2005-07-15', 108),
('SPEC003', 'MED003', 'Chirurgica', 'Ortopedia', '2010-03-10', 105);

-- --------------------------------------------------------

--
-- Table structure for table `storico`
--

CREATE TABLE `storico` (
  `codiceEsame` int(11) NOT NULL,
  `data` date NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `prescrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storico`
--

INSERT INTO `storico` (`codiceEsame`, `data`, `oraInizio`, `oraFine`, `codiceFiscale`, `diagnosi`, `prescrizione`) VALUES
(201, '2025-01-10', 8, 9, 'PLLMRC95D10H501A', 'Ipertensione', 'Farmaci'),
(202, '2025-01-11', 14, 15, 'CNTLRA88E22H501B', 'Emicrania', 'RM encefalo'),
(203, '2025-01-12', 8, 9, 'FRNSFN70A01H501C', 'Artrosi', 'Fisioterapia'),
(204, '2026-03-03', 17, 10, 'CNTLRA88E22H501B', 'Diagnosi di esempio', 'Prescrizione di esempio'),
(205, '2026-03-15', 8, NULL, 'CNTLRA88E22H501B', NULL, 'Visita ortopedica');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `ruolo` enum('paziente','medico','admin') NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `codiceFiscale`, `ruolo`, `password`) VALUES
(1, 'CNTLRA88E22H501B', 'paziente', '$2y$10$tdvLKYyULZXqUmuNypVoC.dZXHjt3ZzykP9dip1cG5PvaOdpEJuda'),
(2, 'FRNSFN70A01H501C', 'paziente', '$2y$10$tdvLKYyULZXqUmuNypVoC.dZXHjt3ZzykP9dip1cG5PvaOdpEJuda'),
(3, 'PLLMRC95D10H501A', 'paziente', '$2y$10$tdvLKYyULZXqUmuNypVoC.dZXHjt3ZzykP9dip1cG5PvaOdpEJuda'),
(4, 'RSSMRA80A01H501Z', 'medico', '$2y$10$RxBj99WDmhoocbrs4zdNQ.XfhXe54AoYiojTjj/Oi25HqO0uC6mtK'),
(5, 'BNCLGI75B22H501X', 'medico', '$2y$10$RxBj99WDmhoocbrs4zdNQ.XfhXe54AoYiojTjj/Oi25HqO0uC6mtK'),
(6, 'VRDGNN82C15H501Y', 'medico', '$2y$10$RxBj99WDmhoocbrs4zdNQ.XfhXe54AoYiojTjj/Oi25HqO0uC6mtK'),
(7, 'admin', 'admin', '$2y$10$..gXbeD1QkoU8FYCXCVb1eSPnYzPRGGlrwITgxd8VUO9HsvIpSlK2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ambulatorio`
--
ALTER TABLE `ambulatorio`
  ADD PRIMARY KEY (`codiceAmbulatorio`),
  ADD KEY `fk_amb_reparto` (`codiceReparto`);

--
-- Indexes for table `esame`
--
ALTER TABLE `esame`
  ADD PRIMARY KEY (`codiceEsame`),
  ADD KEY `fk_esame_amb` (`codiceAmbulatorio`),
  ADD KEY `fk_esame_medico` (`codiceMedico`),
  ADD KEY `fk_esame_codiceFiscale` (`codiceFiscale`);

--
-- Indexes for table `fattura`
--
ALTER TABLE `fattura`
  ADD PRIMARY KEY (`codiceFattura`),
  ADD KEY `fk_fattura_pagamento` (`codicePagamento`);

--
-- Indexes for table `medico`
--
ALTER TABLE `medico`
  ADD PRIMARY KEY (`codiceMedico`),
  ADD UNIQUE KEY `codiceFiscale` (`codiceFiscale`),
  ADD KEY `fk_medico_reparto` (`codiceReparto`),
  ADD KEY `codiceSpecializzazione` (`codiceSpecializzazione`);

--
-- Indexes for table `medico_orariolavoro`
--
ALTER TABLE `medico_orariolavoro`
  ADD PRIMARY KEY (`codiceMedico`,`giorno`,`oraInizio`),
  ADD KEY `fk_mol_orario` (`giorno`,`oraInizio`);

--
-- Indexes for table `orariolavoro`
--
ALTER TABLE `orariolavoro`
  ADD PRIMARY KEY (`giorno`,`oraInizio`);

--
-- Indexes for table `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`codicePagamento`),
  ADD KEY `fk_pagamento_paziente` (`codiceFiscale`);

--
-- Indexes for table `paziente`
--
ALTER TABLE `paziente`
  ADD PRIMARY KEY (`codiceFiscale`);

--
-- Indexes for table `reparto`
--
ALTER TABLE `reparto`
  ADD PRIMARY KEY (`codiceReparto`);

--
-- Indexes for table `specializzazione`
--
ALTER TABLE `specializzazione`
  ADD PRIMARY KEY (`codiceSpecializzazione`),
  ADD KEY `fk_spec_medico` (`codiceMedico`);

--
-- Indexes for table `storico`
--
ALTER TABLE `storico`
  ADD PRIMARY KEY (`codiceEsame`,`data`,`oraInizio`),
  ADD KEY `fk_storico_paziente` (`codiceFiscale`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ambulatorio`
--
ALTER TABLE `ambulatorio`
  ADD CONSTRAINT `fk_amb_reparto` FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `esame`
--
ALTER TABLE `esame`
  ADD CONSTRAINT `fk_esame_amb` FOREIGN KEY (`codiceAmbulatorio`) REFERENCES `ambulatorio` (`codiceAmbulatorio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_codiceFiscale` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fattura`
--
ALTER TABLE `fattura`
  ADD CONSTRAINT `fk_fattura_pagamento` FOREIGN KEY (`codicePagamento`) REFERENCES `pagamento` (`codicePagamento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medico`
--
ALTER TABLE `medico`
  ADD CONSTRAINT `fk_medico_reparto` FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medico_orariolavoro`
--
ALTER TABLE `medico_orariolavoro`
  ADD CONSTRAINT `fk_mol_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mol_orario` FOREIGN KEY (`giorno`,`oraInizio`) REFERENCES `orariolavoro` (`giorno`, `oraInizio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pagamento`
--
ALTER TABLE `pagamento`
  ADD CONSTRAINT `fk_pagamento_paziente` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `specializzazione`
--
ALTER TABLE `specializzazione`
  ADD CONSTRAINT `fk_spec_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `storico`
--
ALTER TABLE `storico`
  ADD CONSTRAINT `fk_storico_esame` FOREIGN KEY (`codiceEsame`) REFERENCES `esame` (`codiceEsame`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_storico_paziente` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
