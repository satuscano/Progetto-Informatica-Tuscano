<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

if(isset($_POST['prenota'])) {

    $codiceMedico = $_POST['codiceMedico'];
    $oraInizio = $_POST['oraInizio'];
    $data = date("Y-m-d");
    $oraFine = $oraInizio + 1;

    $conn->begin_transaction();

    try {

        // controllo slot occupato
        $stmtCheck = $conn->prepare("
            SELECT *
            FROM storico st
            JOIN esame e ON st.codiceEsame = e.codiceEsame
            WHERE e.codiceMedico = ?
            AND st.data = ?
            AND st.oraInizio = ?
        ");

        $stmtCheck->bind_param("ssi", $codiceMedico, $data, $oraInizio);
        $stmtCheck->execute();

        if($stmtCheck->get_result()->num_rows > 0) {
            throw new Exception("Slot occupato");
        }

        // inserisco esame
        $stmtEsame = $conn->prepare("
            INSERT INTO esame (codiceAmbulatorio, codiceMedico, codiceFiscale)
            VALUES (?, ?, ?)
        ");

        $codiceAmbulatorio = 101;
        $stmtEsame->bind_param("iss", $codiceAmbulatorio, $codiceMedico, $cf);
        $stmtEsame->execute();

        $codiceEsame = $conn->insert_id;

        // inserisco storico
        $stmtStorico = $conn->prepare("
            INSERT INTO storico (codiceEsame, data, oraInizio, oraFine, codiceFiscale)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtStorico->bind_param("isiis", $codiceEsame, $data, $oraInizio, $oraFine, $cf);
        $stmtStorico->execute();

        $conn->commit();

        echo "<p class='success-msg'>Prenotazione completata 🎉</p>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<p class='error-msg'>Slot già occupato o errore 😢</p>";
    }
}

$stmtInfo = $conn->prepare("
    SELECT nome, cognome
    FROM paziente
    WHERE codiceFiscale = ?
");
$stmtInfo->bind_param("s", $cf);
$stmtInfo->execute();
$paziente = $stmtInfo->get_result()->fetch_assoc();

$stmtSpecializzazioni = $conn->prepare("
    SELECT titolo, codiceSpecializzazione
    FROM specializzazione
");
$stmtSpecializzazioni->execute();
$spec = $stmtSpecializzazioni->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<html>
<head>
    <title>Prenota un Esame</title>
    <link rel="stylesheet" href="../CSS/theme.css">
</head>

<body>

<?php require_once("../components/menuPaziente.php"); ?>

<header class="top-bar">
    <h1>Prenota un Nuovo Esame</h1>
</header>

<?php
if(count($spec) > 0) {
    echo '<form method="POST">';
    echo '<label>Scegli una specializzazione:</label>';
    echo '<select name="specializzazione" onchange="this.form.submit()">';
    echo '<option disabled selected>Seleziona...</option>';

    foreach($spec as $s) {
        echo '<option value="'.$s['codiceSpecializzazione'].'">'.$s['titolo'].'</option>';
    }

    echo '</select>';
    echo '</form>';
}

if(isset($_POST['specializzazione'])) {

    $stmt = $conn->prepare("
        SELECT 
            m.codiceMedico,
            m.nome,
            m.cognome,
            mol.giorno,
            mol.oraInizio,
            o.oraFine
        FROM medico m
        JOIN specializzazione s 
            ON m.codiceMedico = s.codiceMedico
        JOIN medico_orariolavoro mol 
            ON m.codiceMedico = mol.codiceMedico
        JOIN orariolavoro o 
            ON mol.giorno = o.giorno 
            AND mol.oraInizio = o.oraInizio
        WHERE s.codiceSpecializzazione = ?
    ");

    $stmt->bind_param("s", $_POST['specializzazione']);
    $stmt->execute();
    $medici = $stmt->get_result();

    foreach($medici as $m) {

        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>";

        echo "<p><strong>Medico:</strong> {$m['nome']} {$m['cognome']}</p>";
        echo "<p>Giorno: {$m['giorno']}</p>";
        echo "<p>Orario: {$m['oraInizio']} - {$m['oraFine']}</p>";

        // FORM PRENOTAZIONE
        echo "
        <form method='POST'>
            <input type='hidden' name='codiceMedico' value='{$m['codiceMedico']}'>
            <input type='hidden' name='oraInizio' value='{$m['oraInizio']}'>
            <button type='submit' name='prenota'>Prenota</button>
        </form>
        ";

        echo "</div>";
    }

} else {
    echo "<div class='error-msg'>";
    echo "<p>Selezionare una specializzazione</p>";
    echo "</div>";
}
?>

</body>
</html>