<?php
session_start();

// Verifica se l'utente è autenticato verificando se sono definite le variabili di sessione
if (!isset($_SESSION['codiceFiscale']) || !isset($_SESSION['ruolo'])) {
    header("Location: /PROGETTO-INFO/root/index.php");
    exit;
}

// Verifica se il ruolo dell'utente coincide con quello richiesto per accedere alla pagina
function requireRole($ruoloRichiesto) {
    if ($_SESSION['ruolo'] !== $ruoloRichiesto) {
        header("Location: /PROGETTO-INFO/root/index.php");
        exit;
    }
}
?>