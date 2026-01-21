CREATE TABLE PAZIENTE (
    codiceFiscale VARCHAR(16) PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    dataNascita DATE NOT NULL,
    anamnesi TEXT,
    ind_cap VARCHAR(10),
    ind_citta VARCHAR(50),
    ind_via VARCHAR(50),
    ind_civico VARCHAR(10),

    CONSTRAINT chk_nome CHECK (nome <> ''),
    CONSTRAINT chk_cognome CHECK (cognome <> '')
);

CREATE TABLE REPARTO (
    codiceReparto INT PRIMARY KEY,
    nomeReparto VARCHAR(50) NOT NULL,

    CONSTRAINT chk_codiceReparto CHECK (codiceReparto > 0),
    CONSTRAINT chk_nomeReparto CHECK (nomeReparto <> '')
);

CREATE TABLE MEDICO (
    codiceMedico VARCHAR(10) PRIMARY KEY,
    codiceReparto INT NOT NULL,
    orario VARCHAR(50),
    codiceFiscale VARCHAR(16) UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    primario BOOLEAN,

    CONSTRAINT fk_medico_reparto
        FOREIGN KEY (codiceReparto)
        REFERENCES REPARTO(codiceReparto)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_nomeMedico CHECK (nome <> ''),
    CONSTRAINT chk_cognomeMedico CHECK (cognome <> '')
);

CREATE TABLE AMBULATORIO (
    codiceAmbulatorio INT PRIMARY KEY,
    codiceReparto INT NOT NULL,
    piano INT NOT NULL,

    CONSTRAINT fk_amb_reparto
        FOREIGN KEY (codiceReparto)
        REFERENCES REPARTO(codiceReparto)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_piano CHECK (piano >= 0)
);

CREATE TABLE ESAME (
    codiceEsame INT PRIMARY KEY,
    codiceAmbulatorio INT NOT NULL,
    codiceMedico VARCHAR(10) NOT NULL,
    diagnosi TEXT,
    referto TEXT,

    CONSTRAINT fk_esame_amb
        FOREIGN KEY (codiceAmbulatorio)
        REFERENCES AMBULATORIO(codiceAmbulatorio)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_esame_medico
        FOREIGN KEY (codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_codiceEsame CHECK (codiceEsame > 0)
);

CREATE TABLE PAGAMENTO (
    codicePagamento INT PRIMARY KEY,
    codiceFiscale VARCHAR(16) NOT NULL,
    dataPagamento DATE NOT NULL,
    ora INT,
    minuti INT,
    somma DECIMAL(10,2) NOT NULL,
    metodo VARCHAR(20) NOT NULL,

    CONSTRAINT fk_pagamento_paziente
        FOREIGN KEY (codiceFiscale)
        REFERENCES PAZIENTE(codiceFiscale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_somma CHECK (somma > 0),
    CONSTRAINT chk_ora CHECK (ora BETWEEN 0 AND 23 OR ora IS NULL),
    CONSTRAINT chk_minuti CHECK (minuti BETWEEN 0 AND 59 OR minuti IS NULL),
    CONSTRAINT chk_metodo CHECK (metodo IN ('card', 'cash'))
);

CREATE TABLE FATTURA (
    codiceFattura INT PRIMARY KEY,
    codicePagamento INT NOT NULL,

    CONSTRAINT fk_fattura_pagamento
        FOREIGN KEY (codicePagamento)
        REFERENCES PAGAMENTO(codicePagamento)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_codiceFattura CHECK (codiceFattura > 0)
);

CREATE TABLE STORICO (
    codiceEsame INT,
    data DATE,
    oraInizio INT,
    oraFine INT,
    codiceFiscale VARCHAR(16),
    diagnosi TEXT,
    prescrizione TEXT,

    PRIMARY KEY (codiceEsame, data, oraInizio),

    CONSTRAINT fk_storico_esame
        FOREIGN KEY (codiceEsame)
        REFERENCES ESAME(codiceEsame)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_storico_paziente
        FOREIGN KEY (codiceFiscale)
        REFERENCES PAZIENTE(codiceFiscale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_oraInizio CHECK (oraInizio BETWEEN 0 AND 23),
    CONSTRAINT chk_oraFine CHECK (oraFine BETWEEN 0 AND 23)
);

CREATE TABLE ORARIOLAVORO (
    giorno VARCHAR(2),
    oraInizio INT,
    oraFine INT,

    PRIMARY KEY (giorno, oraInizio),

    CONSTRAINT chk_giorno CHECK (giorno IN ('L','M','Me','G','V','S','D')),
    CONSTRAINT chk_oraInizioOL CHECK (oraInizio BETWEEN 0 AND 23),
    CONSTRAINT chk_oraFineOL CHECK (oraFine BETWEEN 0 AND 23)
);

CREATE TABLE MEDICO_ORARIOLAVORO (
    codiceMedico VARCHAR(10),
    giorno VARCHAR(2),
    oraInizio INT,

    PRIMARY KEY (codiceMedico, giorno, oraInizio),

    CONSTRAINT fk_mol_medico
        FOREIGN KEY (codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_mol_orario
        FOREIGN KEY (giorno, oraInizio)
        REFERENCES ORARIOLAVORO(giorno, oraInizio)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE SPECIALIZZAZIONE (
    codiceSpecializzazione VARCHAR(20) PRIMARY KEY,
    codiceMedico VARCHAR(10),
    tipo VARCHAR(30),
    titolo VARCHAR(50),
    dataConseguimento DATE,
    votoConseguimento INT,

    CONSTRAINT fk_spec_medico
        FOREIGN KEY (codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_voto CHECK (votoConseguimento BETWEEN 0 AND 110),
    CONSTRAINT chk_tipo CHECK (tipo <> ''),
    CONSTRAINT chk_titolo CHECK (titolo <> '')
);
