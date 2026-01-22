<?php
if(!isset($_GET['codiceEsame']) || $_GET['codiceEsame'] == "") {
    echo "<h2 style='color:red;'>Codice esame non specificato!</h2>";
    exit;
}

$codiceEsame = $_GET['codiceEsame'];

try {
    $conn = new PDO(
        "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "SELECT 
                s.codiceEsame,
                e.codiceAmbulatorio,
                e.codiceMedico,
                s.codiceFiscale AS pazienteCF,
                s.diagnosi,
                e.referto,
                s.prescrizione,
                r.nomeReparto,
                a.piano
            FROM STORICO s
            JOIN ESAME e ON s.codiceEsame = e.codiceEsame
            JOIN AMBULATORIO a ON e.codiceAmbulatorio = a.codiceAmbulatorio
            JOIN REPARTO r ON a.codiceReparto = r.codiceReparto
            WHERE s.codiceEsame = :codiceEsame";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['codiceEsame' => $codiceEsame]);
    $esame = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$esame) {
        echo "<h2 style='color:red;'>Esame non trovato!</h2>";
        exit;
    }

} catch(PDOException $e) {
    echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
    exit;
}
?>

<html>
<head>
    <title>Esame - <?= $esame['codiceEsame'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f8ff; padding: 20px; }
        h1 { color: #0077b6; }
        table { border-collapse: collapse; width: 70%; margin-top: 20px; }
        th, td { border: 1px solid #555; padding: 8px 12px; }
        th { background-color: #9dd1ff; text-align: left; }
        td { background-color: #e6f2ff; }
    </style>
</head>
<body>
    <h1>Esame <?= $esame['codiceEsame'] ?></h1>

    <table>
        <tr>
            <th>Codice Esame</th>
            <td><?= $esame['codiceEsame'] ?></td>
        </tr>
        <tr>
            <th>Ambulatorio</th>
            <td><?= $esame['codiceAmbulatorio'] ?> (<?= $esame['nomeReparto'] ?> - piano <?= $esame['piano'] ?>)</td>
        </tr>
        <tr>
            <th>Codice Medico</th>
            <td><a href="infoMedico.php?codice=<?= $esame['codiceMedico'] ?>" target="_blank"><?= $esame['codiceMedico'] ?></a></td>
        </tr>
        <tr>
            <th>Codice Fiscale Paziente</th>
            <td><a href="infoPaziente.php?codiceFiscale=<?= $esame['pazienteCF'] ?>" target="_blank"><?= $esame['pazienteCF'] ?></a></td>
        </tr>
        <tr>
            <th>Diagnosi</th>
            <td><?= $esame['diagnosi'] ?></td>
        </tr>
        <tr>
            <th>Referto</th>
            <td><?= $esame['referto'] ?></td>
        </tr>
        <tr>
            <th>Prescrizione</th>
            <td><?= $esame['prescrizione'] ?></td>
        </tr>
    </table>
</body>
</html>
