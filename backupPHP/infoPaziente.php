<?php
if(!isset($_GET['codiceFiscale']) || $_GET['codiceFiscale'] == "") {
    echo "<h2 style='color:red;'>Codice fiscale non specificato!</h2>";
    exit;
}

$codiceFiscale = $_GET['codiceFiscale'];

try {
    $conn = new PDO(
        "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "SELECT p.codiceFiscale, p.nome, p.cognome, p.dataNascita, p.anamnesi,
                   p.ind_cap, p.ind_citta, p.ind_via, p.ind_civico
            FROM PAZIENTE p
            WHERE p.codiceFiscale = :codiceFiscale";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['codiceFiscale' => $codiceFiscale]);
    $paziente = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$paziente) {
        echo "<h2 style='color:red;'>Paziente non trovato!</h2>";
        exit;
    }

} catch(PDOException $e) {
    echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
    exit;
}
?>

<html>
<head>
    <title>Paziente - <?= $paziente['nome']." ".$paziente['cognome'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f8ff; padding: 20px; }
        h1 { color: #0077b6; }
        table { border-collapse: collapse; width: 50%; margin-top: 20px; }
        th, td { border: 1px solid #555; padding: 8px 12px; }
        th { background-color: #9dd1ff; text-align: left; }
        td { background-color: #e6f2ff; }
    </style>
</head>
<body>
    <h1><?= $paziente['nome']." ".$paziente['cognome'] ?> [Paziente]</h1>

    <table>
        <tr>
            <th>Codice Fiscale</th>
            <td><?= $paziente['codiceFiscale'] ?></td>
        </tr>
        <tr>
            <th>Nome</th>
            <td><?= $paziente['nome'] ?></td>
        </tr>
        <tr>
            <th>Cognome</th>
            <td><?= $paziente['cognome'] ?></td>
        </tr>
        <tr>
            <th>Data di Nascita</th>
            <td><?= $paziente['dataNascita'] ?></td>
        </tr>
        <tr>
            <th>Residente in</th>
            <td><?= $paziente['ind_via']?> <?= $paziente['ind_civico']?>, <?= $paziente['ind_citta']?> <?= $paziente['ind_cap']?></td>
        </tr>
        <tr>
            <th>Anamnesi</th>
            <td><?= $paziente['anamnesi'] ?></td>
        </tr>
    </table>
</body>
</html>
