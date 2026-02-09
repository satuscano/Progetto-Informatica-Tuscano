<?php
    $conn = new mysqli("localhost", "root", "", "databaseprogetto");
    if($conn->connect_error){ die("Connessione fallita: ".$conn->connect_error); }
?>