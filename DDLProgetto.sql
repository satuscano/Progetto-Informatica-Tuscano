CREATE TABLE PAGAMENTO (
    codicePagamento INT PRIMARY KEY,
    codiceFiscale VARCHAR(16),
    data DATE,
    ora INT,
    minuti INT,
    somma FLOAT,
    metodo VARCHAR(20),
    FOREIGN KEY (codiceFiscale) REFERENCES PAZIENTE(codiceFiscale),

    CONSTRAINT check_FKPaziente FOREIGN KEY(codiceFiscale)
        REFERENCES PAZIENTE(codiceFiscale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT check_somma CHECK(somma > 0),
    CONSTRAINT check_ora CHECK(ora BETWEEN 0 AND 23),
    CONSTRAINT check_minuti CHECK(minuti BETWEEN 0 AND 59),
    CONSTRAINT check_metodo CHECK(metodo IN ('card', 'cash')),
    CONSTRAINT check_data CHECK(data <= CURRENT_DATE),

    CONSTRAINT check_ora_minuti_consistency CHECK(
        (ora IS NULL AND minuti IS NULL) OR (ora IS NOT NULL AND minuti IS NOT NULL)
    )
);

CREATE TABLE FATTURA (
    codiceFattura INT PRIMARY KEY,
    codicePagamento INT,
    
    CONSTRAINT check_PKFattura PRIMARY KEY(codiceFattura),
    CONSTRAINT check_FKPAGAMENTO FOREIGN KEY(codicePagamento)
        REFERENCES PAGAMENTO(codicePagamento)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_codicePagamento_positive CHECK(codicePagamento > 0)
);

CREATE TABLE PAZIENTE (
    codiceFiscale VARCHAR(16) PRIMARY KEY,
    nome VARCHAR(50),
    cognome VARCHAR(50),
    dataNascita DATE,
    anamnesi TEXT,
    ind_cap VARCHAR(10),
    ind_citta VARCHAR(50),
    ind_via VARCHAR(50),
    ind_civico VARCHAR(10),

    CONSTRAINT check_PKPAZIENTE PRIMARY KEY(codiceFiscale),
    CONSTRAINT check_nome_not_empty CHECK(LENGTH(nome) > 0),
    CONSTRAINT check_cognome_not_empty CHECK(LENGTH(cognome) > 0),
    CONSTRAINT check_dataNascita CHECK(dataNascita <= CURRENT_DATE),
    CONSTRAINT check_ind_cap_positive CHECK(CAST(ind_cap AS UNSIGNED) >= 0),
    CONSTRAINT check_ind_civico_positive CHECK(CAST(ind_civico AS UNSIGNED) >= 0),
    CONSTRAINT check_ind_via_not_empty CHECK(LENGTH(ind_via) > 0),
    CONSTRAINT check_ind_citta_not_empty CHECK(LENGTH(ind_citta) > 0),
);

CREATE TABLE STORICO (
    codiceEsame INT,
    data DATE,
    oraInizio INT,
    codiceFiscale VARCHAR(16),
    oraFine INT,
    diagnosi TEXT,
    prescrizione TEXT,
    PRIMARY KEY (codiceEsame, data, oraInizio),
    FOREIGN KEY (codiceEsame) REFERENCES ESAME(codiceEsame),
    FOREIGN KEY (codiceFiscale) REFERENCES PAZIENTE(codiceFiscale),

    CONSTRAINT check_PKStorico PRIMARY KEY(codiceEsame, data, oraInizio),
    CONSTRAINT check_FKEsameStorico FOREIGN KEY(codiceEsame)
        REFERENCES ESAME(codiceEsame)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_FKPazienteStorico FOREIGN KEY(codiceFiscale)
        REFERENCES PAZIENTE(codiceFiscale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_oraInizioStorico CHECK(oraInizio BETWEEN 0 AND 23),
    CONSTRAINT check_oraFineStorico CHECK(oraFine BETWEEN 0 AND 23)
);

CREATE TABLE PRENOTAZIONE (
    codicePrenotazione INT PRIMARY KEY,
    codiceFiscale VARCHAR(16),
    codiceMedico VARCHAR(10),
    data DATE,
    oraInizio INT,
    oraFine INT,
    tipoVisita VARCHAR(50),
    FOREIGN KEY (codiceFiscale) REFERENCES PAZIENTE(codiceFiscale),
    FOREIGN KEY (codiceMedico) REFERENCES MEDICO(codiceMedico),

    CONSTRAINT check_PKPrenotazione PRIMARY KEY(codicePrenotazione),
    CONSTRAINT check_FKPazientePren FOREIGN KEY(codiceFiscale)
        REFERENCES PAZIENTE(codiceFiscale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_FKMedicoPren FOREIGN KEY(codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_ora_inizio_pren CHECK(oraInizio BETWEEN 0 AND 23),
    CONSTRAINT check_ora_fine_pren CHECK(oraFine BETWEEN 0 AND 23),
    CONSTRAINT check_codicePrenotazione_positive CHECK(codicePrenotazione > 0),
    CONSTRAINT check_tipoVisita_not_empty CHECK(LENGTH(tipoVisita) > 0)
);

CREATE TABLE MEDICO (
    codiceMedico VARCHAR(10) PRIMARY KEY,
    codiceReparto INT,
    orario VARCHAR(50),
    codiceFiscale VARCHAR(16) UNIQUE,
    nome VARCHAR(50),
    cognome VARCHAR(50),
    primario BOOLEAN,
    FOREIGN KEY (codiceReparto) REFERENCES REPARTO(codiceReparto),

    CONSTRAINT check_PKMedico PRIMARY KEY(codiceMedico),
    CONSTRAINT check_FKReparto FOREIGN KEY(codiceReparto)
        REFERENCES REPARTO(codiceReparto)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_codiceMedico_not_empty CHECK(LENGTH(codiceMedico) > 0),
    CONSTRAINT check_nomeMedico_not_empty CHECK(LENGTH(nome) > 0),
    CONSTRAINT check_cognomeMedico_not_empty CHECK(LENGTH(cognome) > 0)
);

CREATE TABLE REPARTO (
    codiceReparto INT PRIMARY KEY,
    nomeReparto VARCHAR(50),

    CONSTRAINT check_PKReparto PRIMARY KEY(codiceReparto),
    CONSTRAINT check_codiceReparto_positive CHECK(codiceReparto > 0),
    CONSTRAINT check_nomeReparto_not_empty CHECK(LENGTH(nomeReparto) > 0)
);

CREATE TABLE AMBULATORIO (
    codiceAmbulatorio INT PRIMARY KEY,
    codiceReparto INT,
    piano INT,
    FOREIGN KEY (codiceReparto) REFERENCES REPARTO(codiceReparto),

    CONSTRAINT check_PKAmbulatorio PRIMARY KEY(codiceAmbulatorio),
    CONSTRAINT check_FKRepartoAmb FOREIGN KEY(codiceReparto)
        REFERENCES REPARTO(codiceReparto)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_codiceAmbulatorio_positive CHECK(codiceAmbulatorio > 0),
    CONSTRAINT check_piano_positive CHECK(piano >= 0)
);

CREATE TABLE ESAME (
    codiceEsame INT PRIMARY KEY,
    codiceAmbulatorio INT,
    codiceMedico VARCHAR(10),
    diagnosi TEXT,
    referto TEXT,
    FOREIGN KEY (codiceAmbulatorio) REFERENCES AMBULATORIO(codiceAmbulatorio),
    FOREIGN KEY (codiceMedico) REFERENCES MEDICO(codiceMedico),

    CONSTRAINT check_PKEsame PRIMARY KEY(codiceEsame),
    CONSTRAINT check_FKAmbulatorio FOREIGN KEY(codiceAmbulatorio)
        REFERENCES AMBULATORIO(codiceAmbulatorio)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_FKMedicoEsame FOREIGN KEY(codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_codiceEsame_positive CHECK(codiceEsame > 0)
);

CREATE TABLE ORARIOLAVORO (
    giorno VARCHAR(2),
    oraInizio INT,
    oraFine INT,
    PRIMARY KEY (giorno, oraInizio),

    CONSTRAINT check_PKOrario PRIMARY KEY(giorno, oraInizio),
    CONSTRAINT check_oraInizio CHECK(oraInizio BETWEEN 0 AND 23),
    CONSTRAINT check_oraFine CHECK(oraFine BETWEEN 0 AND 23),
    CONSTRAINT check_giorno_valid CHECK(giorno IN ('L','M','Me','G','V','S','D'))
);

CREATE TABLE SPECIALIZZAZIONE (
    codiceSpecializzazione VARCHAR(20) PRIMARY KEY,
    codiceMedico VARCHAR(10),
    tipo VARCHAR(30),
    titolo VARCHAR(50),
    dataConseguimento DATE,
    votoConseguimento INT,
    FOREIGN KEY (codiceMedico) REFERENCES MEDICO(codiceMedico),

    CONSTRAINT check_PKSpecializzazione PRIMARY KEY(codiceSpecializzazione),
    CONSTRAINT check_FKMedicoSpecializzazione FOREIGN KEY(codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_voto CHECK(votoConseguimento >= 0 AND votoConseguimento <= 110),
    CONSTRAINT check_tipo_not_empty CHECK(LENGTH(tipo) > 0),
    CONSTRAINT check_titolo_not_empty CHECK(LENGTH(titolo) > 0)
);

CREATE TABLE MEDICO_ORARIOLAVORO (
    codiceMedico VARCHAR(10),
    giorno VARCHAR(2),
    oraInizio INT,
    PRIMARY KEY (codiceMedico, giorno, oraInizio),
    FOREIGN KEY (codiceMedico) REFERENCES MEDICO(codiceMedico),
    FOREIGN KEY (giorno, oraInizio) REFERENCES ORARIOLAVORO(giorno, oraInizio),

    CONSTRAINT check_PKMedicoOrario PRIMARY KEY(codiceMedico, giorno, oraInizio),
    CONSTRAINT check_FKMedico FOREIGN KEY(codiceMedico)
        REFERENCES MEDICO(codiceMedico)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT check_FKOrario FOREIGN KEY(giorno, oraInizio)
        REFERENCES ORARIOLAVORO(giorno, oraInizio)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);