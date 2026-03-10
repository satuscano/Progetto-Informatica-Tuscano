<?php
include("../inc/start.inc");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: esamiPrenotati.php');
    exit;
}

// Recupera e valida i dati inviati dal form
$codiceEsame = filter_input(INPUT_POST, 'codiceEsame', FILTER_VALIDATE_INT);
$data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
$oraRaw = filter_input(INPUT_POST, 'ora', FILTER_SANITIZE_STRING);
$cf = $_SESSION['codiceFiscale'];

if (!$codiceEsame || !$data || !$oraRaw) {
    header('Location: esamiPrenotati.php?error=1');
    exit;
}

// Estrai solo l'ora senza formato
$oraParts = explode(':', $oraRaw);
$oraInizio = intval($oraParts[0]);

$conn = new mysqli("localhost", "root", "", "databaseprogetto");
if ($conn->connect_error) {
    header('Location: esamiPrenotati.php?error=1');
    exit;
}

$conn->begin_transaction();

// Verifica che l'esame appartenga all'utente
$checkEsame = $conn->prepare("SELECT 1 FROM esame WHERE codiceEsame = ? AND codiceFiscale = ? LIMIT 1");
$checkEsame->bind_param("is", $codiceEsame, $cf);
$checkEsame->execute();
$checkEsame->store_result();

if ($checkEsame->num_rows === 0) {
    $checkEsame->close();
    $conn->rollback();
    $conn->close();
    header('Location: esamiPrenotati.php?error=1');
    exit;
}
$checkEsame->close();

// Proviamo ad aggiornare la riga in storico - se non esiste, la inseriamo
$upd = $conn->prepare("UPDATE storico SET data = ?, oraInizio = ? WHERE codiceEsame = ? AND codiceFiscale = ?");
$upd->bind_param("siss", $data, $oraInizio, $codiceEsame, $cf);
$upd->execute();

if ($upd->affected_rows > 0) {
    $upd->close();
    $conn->commit();
    $conn->close();
    header('Location: esamiPrenotati.php?modified=1');
    exit;
}
$upd->close();

// Inserimento se non esiste già una prenotazione per questo esame
$ins = $conn->prepare("INSERT INTO storico (codiceEsame, data, oraInizio, codiceFiscale) VALUES (?, ?, ?, ?)");
$ins->bind_param("isss", $codiceEsame, $data, $oraInizio, $cf);
$ins->execute();

if ($ins->affected_rows > 0) {
    $ins->close();
    $conn->commit();
    $conn->close();
    header('Location: esamiPrenotati.php?modified=1');
    exit;
} else {
    $ins->close();
    $conn->rollback();
    $conn->close();
    header('Location: esamiPrenotati.php?error=1');
    exit;
}
?>