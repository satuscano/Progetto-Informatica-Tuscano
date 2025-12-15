---
title: "RELAZIONE TECNICA"
author: "Alessio Tuscano"
date: "A.S. 2025–2026"
toc: true
toc-depth: 10
---

---
# Progetto Applicazione Web

## Introduzione del Progetto
Il progetto prevede la realizzazione di un'applicazione web completa, sviluppata progressivamente attraverso diverse fasi. Ogni fase si concentra su un aspetto specifico dello sviluppo, costruendo gradualmente un sistema funzionante e professionale.

La  ***prima fase*** comprende:

1. **Analisi** dei requisiti
2. **Diagramma E-R** realizzato con Mermaid
3. **Schema logico**
4. **DDL**
5. **Dizionario** dei dati
6. **Cocnlusioni**
7. **Relazione tecnica** finale del progetto realizzata in markdown
8. **Conversione del documento in PDF** tramite *pandoc*

## Analisi dei Requisiti
Il sistema prevede un database per la gestione di un centro polispecialistico. Fornirà la digitalizzazione dei dati dei pazienti (anagrafici e clinici), la gestione dei medici con le rispettive specializzazioni e gli orari di lavoro, le prenotazioni e la conservazione storica degli esami diagnostici. Sono inoltre previste funzionalità per l’assegnazione delle sale ambulatoriali nei diversi reparti e per la gestione dei pagamenti e della fatturazione.

## Diagramma Entità-Relazione (E-R)
<!-- ||--o // }|--o -->

```Mermaid
    erDiagram
        FATTURA o|--|| PAGAMENTO : riferisce
        PAGAMENTO ||--o{ PAZIENTE : paga
        PRENOTAZIONE ||--|{ PAZIENTE : prenota
        STORICO ||--o{ PAZIENTE : prenota
        STORICO |{--o{ PRENOTAZIONE : prenota
        MEDICO o{--|| PRENOTAZIONE : chiamato
        MEDICO ||--|{ REPARTO : lavora
        REPARTO |{--o| AMBULATORIO : sta
        ESAME ||--o{ AMBULATORIO : svolto
        ESAME ||--o{ MEDICO : effettua
        MEDICO |{--|{ ORARIOLAVORO : turno

        FATTURA {
            int codiceFattura PK
            int FKPagamento FK
        }

        PAGAMENTO {
            int codicePagamento PK
            string codiceFiscale FK
            date data
            int ora
            int minuti
            double somma
            enum metodo "card / cash"
        }

        PAZIENTE {
            string codiceFiscale PK
            string nome
            string cognome
            date dataNascita
            string anamnesi
            string ind_cap
            string ind_citta
            string ind_via
            string ind_civico
        }

        STORICO {
            int codiceEsame PK "FK"
            date data PK
            int oraInizio PK
            string codiceFiscale FK
            int oraFine "NULL"
            string diagnosi
            string prescrizione
        }

        PRENOTAZIONE {
            int codicePrenotazione PK
            string codiceFiscale FK
            string codiceMedico FK
            date data
            int oraInizio
            int oraFine "NULL"
            string tipoVisita
        }

        MEDICO {
            string codiceMedico PK
            int codiceReparto FK
            string codiceFiscale "UNIQUE"
            string nome
            string cognome
            binary primario "NULL"
        }

        REPARTO {
            int codiceReparto PK
            string nomeReparto
        }

        AMBULATORIO {
            int codiceAmbulatorio PK
            int codiceReparto FK
            int piano
        }

        ESAME {
            int codiceEsame PK
            string diagnosi
            string referto "NULL"
        }

        ORARIOLAVORO {
            enum giorno PK "L / M / Me / G / V / S / D"
            int oraInizio PK
            int oraFine
        }

        SPECIALIZZAZIONE {
            string codiceSpecializzazione PK
            string codiceMedico FK
            enum tipo "Medica / Chirurgia / Clinica"
            string titolo
            date dataConseguimento
            int votoConseguimento
        }
        
```

## Schema Logico Relazionale
