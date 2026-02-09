SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `ambulatorio` (
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `piano` int(11) NOT NULL
) ;

INSERT INTO `ambulatorio` (`codiceAmbulatorio`, `codiceReparto`, `piano`) VALUES
(101, 1, 0),
(102, 2, 1);

CREATE TABLE `esame` (
  `codiceEsame` int(11) NOT NULL,
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceMedico` varchar(10) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `referto` text DEFAULT NULL
) ;

INSERT INTO `esame` (`codiceEsame`, `codiceAmbulatorio`, `codiceMedico`, `codiceFiscale`, `diagnosi`, `referto`) VALUES
(1001, 101, 'M001', 'VRDLGU90B02F205Z', 'Controllo cuore', 'Tutto ok'),
(1002, 102, 'M002', 'RSSMRA80A01H501U', 'Controllo neurologico', 'Normale');

CREATE TABLE `fattura` (
  `codiceFattura` int(11) NOT NULL,
  `codicePagamento` int(11) NOT NULL
) ;

INSERT INTO `fattura` (`codiceFattura`, `codicePagamento`) VALUES
(9001, 5001),
(9002, 5002);

CREATE TABLE `medico` (
  `codiceMedico` varchar(10) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `orario` varchar(50) DEFAULT NULL,
  `codiceFiscale` varchar(16) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `primario` tinyint(1) DEFAULT NULL
) ;

INSERT INTO `medico` (`codiceMedico`, `codiceReparto`, `orario`, `codiceFiscale`, `nome`, `cognome`, `primario`) VALUES
('M001', 1, '08:00-16:00', 'RSSMRA80A01H501U', 'Mario', 'Rossi', 1),
('M002', 2, '09:00-17:00', 'VRDLGU90B02F205Z', 'Luigi', 'Verdi', 0);

CREATE TABLE `medico_orariolavoro` (
  `codiceMedico` varchar(10) NOT NULL,
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medico_orariolavoro` (`codiceMedico`, `giorno`, `oraInizio`) VALUES
('M001', 'L', 8),
('M002', 'M', 9);

CREATE TABLE `orariolavoro` (
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL
) ;

INSERT INTO `orariolavoro` (`giorno`, `oraInizio`, `oraFine`) VALUES
('L', 8, 16),
('M', 9, 17);
-

CREATE TABLE `pagamento` (
  `codicePagamento` int(11) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `dataPagamento` date NOT NULL,
  `ora` int(11) DEFAULT NULL,
  `minuti` int(11) DEFAULT NULL,
  `somma` decimal(10,2) NOT NULL,
  `metodo` varchar(20) NOT NULL
) ;

INSERT INTO `pagamento` (`codicePagamento`, `codiceFiscale`, `dataPagamento`, `ora`, `minuti`, `somma`, `metodo`) VALUES
(5001, 'VRDLGU90B02F205Z', '2026-01-20', 10, 30, 50.00, 'card'),
(5002, 'RSSMRA80A01H501U', '2026-01-21', 11, 45, 75.00, 'cash');

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
) ;

INSERT INTO `paziente` (`codiceFiscale`, `nome`, `cognome`, `dataNascita`, `anamnesi`, `ind_cap`, `ind_citta`, `ind_via`, `ind_civico`) VALUES
('RSSMRA80A01H501U', 'Mario', 'Rossi', '1980-01-01', 'Nessuna patologia nota', '00100', 'Roma', 'Via Roma', '10'),
('VRDLGU90B02F205Z', 'Luigi', 'Verdi', '1990-02-02', 'Allergia penicillina', '20100', 'Milano', 'Via Milano', '20');

CREATE TABLE `reparto` (
  `codiceReparto` int(11) NOT NULL,
  `nomeReparto` varchar(50) NOT NULL
) ;

INSERT INTO `reparto` (`codiceReparto`, `nomeReparto`) VALUES
(1, 'Cardiologia'),
(2, 'Neurologia'),
(3, 'Pediatria');

CREATE TABLE `specializzazione` (
  `codiceSpecializzazione` varchar(20) NOT NULL,
  `codiceMedico` varchar(10) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `titolo` varchar(50) DEFAULT NULL,
  `dataConseguimento` date DEFAULT NULL,
  `votoConseguimento` int(11) DEFAULT NULL
) ;

INSERT INTO `specializzazione` (`codiceSpecializzazione`, `codiceMedico`, `tipo`, `titolo`, `dataConseguimento`, `votoConseguimento`) VALUES
('S001', 'M001', 'Cardiologia', 'Laurea Medicina', '2005-07-15', 110),
('S002', 'M002', 'Neurologia', 'Laurea Medicina', '2008-07-20', 108);

CREATE TABLE `storico` (
  `codiceEsame` int(11) NOT NULL,
  `data` date NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `prescrizione` text DEFAULT NULL
) ;

INSERT INTO `storico` (`codiceEsame`, `data`, `oraInizio`, `oraFine`, `codiceFiscale`, `diagnosi`, `prescrizione`) VALUES
(1001, '2026-01-20', 9, 10, 'VRDLGU90B02F205Z', 'Controllo cuore', 'Riposo'),
(1002, '2026-01-21', 10, 11, 'RSSMRA80A01H501U', 'Controllo neurologico', 'Nessuna');

ALTER TABLE `ambulatorio`
  ADD PRIMARY KEY (`codiceAmbulatorio`),
  ADD KEY `fk_amb_reparto` (`codiceReparto`);

ALTER TABLE `esame`
  ADD PRIMARY KEY (`codiceEsame`),
  ADD KEY `fk_esame_amb` (`codiceAmbulatorio`),
  ADD KEY `fk_esame_medico` (`codiceMedico`),
  ADD KEY `fk_esame_codiceFiscale` (`codiceFiscale`);

ALTER TABLE `fattura`
  ADD PRIMARY KEY (`codiceFattura`),
  ADD KEY `fk_fattura_pagamento` (`codicePagamento`);

ALTER TABLE `medico`
  ADD PRIMARY KEY (`codiceMedico`),
  ADD UNIQUE KEY `codiceFiscale` (`codiceFiscale`),
  ADD KEY `fk_medico_reparto` (`codiceReparto`);

ALTER TABLE `medico_orariolavoro`
  ADD PRIMARY KEY (`codiceMedico`,`giorno`,`oraInizio`),
  ADD KEY `fk_mol_orario` (`giorno`,`oraInizio`);

ALTER TABLE `orariolavoro`
  ADD PRIMARY KEY (`giorno`,`oraInizio`);

ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`codicePagamento`),
  ADD KEY `fk_pagamento_paziente` (`codiceFiscale`);

ALTER TABLE `paziente`
  ADD PRIMARY KEY (`codiceFiscale`);

ALTER TABLE `reparto`
  ADD PRIMARY KEY (`codiceReparto`);

ALTER TABLE `specializzazione`
  ADD PRIMARY KEY (`codiceSpecializzazione`),
  ADD KEY `fk_spec_medico` (`codiceMedico`);

ALTER TABLE `storico`
  ADD PRIMARY KEY (`codiceEsame`,`data`,`oraInizio`),
  ADD KEY `fk_storico_paziente` (`codiceFiscale`);

ALTER TABLE `ambulatorio`
  ADD CONSTRAINT `fk_amb_reparto` FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `esame`
  ADD CONSTRAINT `fk_esame_amb` FOREIGN KEY (`codiceAmbulatorio`) REFERENCES `ambulatorio` (`codiceAmbulatorio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_codiceFiscale` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `fattura`
  ADD CONSTRAINT `fk_fattura_pagamento` FOREIGN KEY (`codicePagamento`) REFERENCES `pagamento` (`codicePagamento`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `medico`
  ADD CONSTRAINT `fk_medico_reparto` FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `medico_orariolavoro`
  ADD CONSTRAINT `fk_mol_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mol_orario` FOREIGN KEY (`giorno`,`oraInizio`) REFERENCES `orariolavoro` (`giorno`, `oraInizio`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pagamento`
  ADD CONSTRAINT `fk_pagamento_paziente` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `specializzazione`
  ADD CONSTRAINT `fk_spec_medico` FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `storico`
  ADD CONSTRAINT `fk_storico_esame` FOREIGN KEY (`codiceEsame`) REFERENCES `esame` (`codiceEsame`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_storico_paziente` FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;