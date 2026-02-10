<?php
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['codiceFiscale']) || !isset($_SESSION['ruolo'])) {
    header("Location: /PROGETTO-INFO/root/index.php");
    exit;
}

// Funzione per verificare il ruolo dell'utente
function requireRole($ruoloRichiesto) {
    if ($_SESSION['ruolo'] !== $ruoloRichiesto) {
        header("Location: /PROGETTO-INFO/root/index.php");
        exit;
    }
}
?>