<?php
if(!isset($_GET['codice']) || $_GET['codice'] == "") {
    echo "<h2 style='color:red;'>Codice medico non specificato!</h2>";
    exit;
}

$codiceMedico = $_GET['codice'];

try {
    $conn = new PDO(
        "mysql:host=127.0.0.1;dbname=databaseprogetto;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "SELECT me.codiceMedico, me.nome, me.cognome, me.codiceFiscale, me.primario,
                   r.nomeReparto, me.orario
            FROM MEDICO me
            JOIN REPARTO r ON me.codiceReparto = r.codiceReparto
            WHERE me.codiceMedico = :codice";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['codice' => $codiceMedico]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$medico) {
        echo "<h2 style='color:red;'>Medico non trovato!</h2>";
        exit;
    }

} catch(PDOException $e) {
    echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
    exit;
}
?>

<html>
<head>
    <title><?= $medico['nome']." ".$medico['cognome'] ?></title>
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
    <h1><?= $medico['nome']." ".$medico['cognome'] ?> [Medico]</h1>

    <table>
        <tr>
            <th>Codice</th>
            <td><?= $medico['codiceMedico'] ?></td>
        </tr>
        <tr>
            <th>Nome</th>
            <td><?= $medico['nome'] ?></td>
        </tr>
        <tr>
            <th>Cognome</th>
            <td><?= $medico['cognome'] ?></td>
        </tr>
        <tr>
            <th>Codice Fiscale</th>
            <td><?= $medico['codiceFiscale'] ?></td>
        </tr>
        <tr>
            <th>Primario</th>
            <td><?= $medico['primario'] ? "Sì" : "No" ?></td>
        </tr>
        <tr>
            <th>Reparto</th>
            <td><?= $medico['nomeReparto'] ?></td>
        </tr>
    </table>
</body>
</html>
