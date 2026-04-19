<?php
    require_once("../auth.php");
    requireRole('paziente');
    require_once("../components/menuPaziente.php");

    $conn = new mysqli("localhost", "root", "", "databaseprogetto");
    if($conn->connect_error){ die("Connessione fallita: ".$conn->connect_error); }
?>

la require_once serve a 