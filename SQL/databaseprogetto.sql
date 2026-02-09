CREATE DATABASE `databaseprogetto`;
USE `databaseprogetto`;

CREATE TABLE `ambulatorio` (
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `piano` int(11) NOT NULL
);

CREATE TABLE `esame` (
  `codiceEsame` int(11) NOT NULL,
  `codiceAmbulatorio` int(11) NOT NULL,
  `codiceMedico` varchar(10) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `referto` text DEFAULT NULL
);

CREATE TABLE `fattura` (
  `codiceFattura` int(11) NOT NULL,
  `codicePagamento` int(11) NOT NULL
);

CREATE TABLE `medico` (
  `codiceMedico` varchar(10) NOT NULL,
  `codiceReparto` int(11) NOT NULL,
  `orario` varchar(50) DEFAULT NULL,
  `codiceFiscale` varchar(16) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `primario` tinyint(1) DEFAULT NULL
);

CREATE TABLE `medico_orariolavoro` (
  `codiceMedico` varchar(10) NOT NULL,
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL
);

CREATE TABLE `orariolavoro` (
  `giorno` varchar(2) NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL
);

CREATE TABLE `pagamento` (
  `codicePagamento` int(11) NOT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `dataPagamento` date NOT NULL,
  `ora` int(11) DEFAULT NULL,
  `minuti` int(11) DEFAULT NULL,
  `somma` decimal(10,2) NOT NULL,
  `metodo` varchar(20) NOT NULL
);

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
);

CREATE TABLE `reparto` (
  `codiceReparto` int(11) NOT NULL,
  `nomeReparto` varchar(50) NOT NULL
);

CREATE TABLE `specializzazione` (
  `codiceSpecializzazione` varchar(20) NOT NULL,
  `codiceMedico` varchar(10) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `titolo` varchar(50) DEFAULT NULL,
  `dataConseguimento` date DEFAULT NULL,
  `votoConseguimento` int(11) DEFAULT NULL
);

CREATE TABLE `storico` (
  `codiceEsame` int(11) NOT NULL,
  `data` date NOT NULL,
  `oraInizio` int(11) NOT NULL,
  `oraFine` int(11) DEFAULT NULL,
  `codiceFiscale` varchar(16) NOT NULL,
  `diagnosi` text DEFAULT NULL,
  `prescrizione` text DEFAULT NULL
);

CREATE TABLE `users` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codiceFiscale VARCHAR(16) NOT NULL,
  ruolo ENUM('paziente', 'medico', 'admin') NOT NULL,
  password VARCHAR(255) NOT NULL
);

-- ALTER TABLES

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
  ADD CONSTRAINT `fk_amb_reparto`
  FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `esame`
  ADD CONSTRAINT `fk_esame_amb`
  FOREIGN KEY (`codiceAmbulatorio`) REFERENCES `ambulatorio` (`codiceAmbulatorio`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_codiceFiscale`
  FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esame_medico`
  FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `fattura`
  ADD CONSTRAINT `fk_fattura_pagamento`
  FOREIGN KEY (`codicePagamento`) REFERENCES `pagamento` (`codicePagamento`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `medico`
  ADD CONSTRAINT `fk_medico_reparto`
  FOREIGN KEY (`codiceReparto`) REFERENCES `reparto` (`codiceReparto`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `medico_orariolavoro`
  ADD CONSTRAINT `fk_mol_medico`
  FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mol_orario`
  FOREIGN KEY (`giorno`,`oraInizio`) REFERENCES `orariolavoro` (`giorno`,`oraInizio`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pagamento`
  ADD CONSTRAINT `fk_pagamento_paziente`
  FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `specializzazione`
  ADD CONSTRAINT `fk_spec_medico`
  FOREIGN KEY (`codiceMedico`) REFERENCES `medico` (`codiceMedico`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `storico`
  ADD CONSTRAINT `fk_storico_esame`
  FOREIGN KEY (`codiceEsame`) REFERENCES `esame` (`codiceEsame`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_storico_paziente`
  FOREIGN KEY (`codiceFiscale`) REFERENCES `paziente` (`codiceFiscale`)
  ON DELETE CASCADE ON UPDATE CASCADE;
