<?php
    $conn = new PDO(
        "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
?>