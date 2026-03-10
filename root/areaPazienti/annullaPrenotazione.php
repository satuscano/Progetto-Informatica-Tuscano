<?php
include("../inc/start.inc");

// Questo script gestisce la cancellazione di una prenotazione esame da parte del paziente.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: esamiPrenotati.php');
    exit;
}

// Recupera e valida i dati inviati dal form
$codiceEsame = filter_input(INPUT_POST, 'codiceEsame', FILTER_VALIDATE_INT);
$data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
$oraInizio = filter_input(INPUT_POST, 'oraInizio', FILTER_VALIDATE_INT);
$cf = $_SESSION['codiceFiscale'];

// Validazione dei dati di input
if (!$codiceEsame || !$data || $oraInizio === false) {
    header('Location: esamiPrenotati.php?error=1');
    exit;
}

$conn = new mysqli("localhost", "root", "", "databaseprogetto");
if ($conn->connect_error) {
    header('Location: esamiPrenotati.php?error=1');
    exit;
}

// Avvia transazione: verifichiamo che la prenotazione appartenga all'utente
$conn->begin_transaction();

// Controlla se la prenotazione esiste e appartiene all'utente
$check = $conn->prepare("SELECT 1 FROM storico WHERE codiceEsame = ? AND data = ? AND oraInizio = ? AND codiceFiscale = ? LIMIT 1");
$check->bind_param("isis", $codiceEsame, $data, $oraInizio, $cf);
$check->execute();
$check->store_result();

// Se la prenotazione non esiste o non appartiene all'utente, rollback e mostra errore
if ($check->num_rows === 0) {
    $check->close();
    $conn->rollback();
    $conn->close();
    header('Location: esamiPrenotati.php?error=1');
    exit;
}
$check->close();

// Se la prenotazione è valida, procediamo con la cancellazione dell'esame
$del = $conn->prepare("DELETE FROM esame WHERE codiceEsame = ? AND codiceFiscale = ?");
$del->bind_param("is", $codiceEsame, $cf);
$del->execute();

// Se la cancellazione dell'esame ha successo, eliminiamo anche la prenotazione dallo storico
if ($del->affected_rows > 0) {
    $del->close();
    $conn->commit();
    $conn->close();
    header('Location: esamiPrenotati.php?deleted=1');
    exit;
} else { // Se la cancellazione dell'esame fallisce, rollback e mostra errore
    $del->close();
    $conn->rollback();
    $conn->close();
    header('Location: esamiPrenotati.php?error=1');
    exit;
}
?>