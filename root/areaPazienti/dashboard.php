<?php
session_start();

if(!isset($_SESSION['codiceFiscale']) || $_SESSION['ruolo'] != 'paziente'){
    header("Location: ../index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "databaseprogetto");
if($conn->connect_error){ die("Connessione fallita: ".$conn->connect_error); }

$cf = $_SESSION['codiceFiscale'];

// Riepilogo: numero totale esami
$stmt = $conn->prepare("SELECT COUNT(*) as totaleEsami FROM storico WHERE codiceFiscale = ?");
$stmt->bind_param("s", $cf);
$stmt->execute();
$totaleEsami = $stmt->get_result()->fetch_assoc()['totaleEsami'];

// Ultimo esame
$stmt2 = $conn->prepare("SELECT diagnosi, data FROM storico WHERE codiceFiscale = ? ORDER BY data DESC LIMIT 1");
$stmt2->bind_param("s", $cf);
$stmt2->execute();
$ultimoEsame = $stmt2->get_result()->fetch_assoc();

// Dati per grafico: esami per reparto
$reparti = ['Cardiologia', 'Neurologia', 'Pediatria'];
$conteggi = [];

foreach($reparti as $rep){
    $stmt3 = $conn->prepare("
        SELECT COUNT(*) as tot FROM storico s 
        JOIN esame e ON s.codiceEsame = e.codiceEsame
        JOIN ambulatorio a ON e.codiceAmbulatorio = a.codiceAmbulatorio
        JOIN reparto r ON a.codiceReparto = r.codiceReparto
        WHERE s.codiceFiscale = ? AND r.nomeReparto = ?
    ");
    $stmt3->bind_param("ss", $cf, $rep);
    $stmt3->execute();
    $res = $stmt3->get_result()->fetch_assoc();
    $conteggi[] = $res['tot'];
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Paziente</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../CSS/theme.css">
</head>
<body>
<div class="container">
    <h1>Benvenuto, <?= $_SESSION['codiceFiscale'] ?></h1>

    <div class="riepilogo">
        <p>Esami totali: <?= $totaleEsami ?></p>
        <p>Ultimo esame (<?= $ultimoEsame['data'] ?>): <?= $ultimoEsame['diagnosi'] ?></p>
    </div>

    <canvas id="graficoEsami" width="400" height="200"></canvas>
</div>

<script>
const ctx = document.getElementById('graficoEsami').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($reparti) ?>,
        datasets: [{
            label: 'Esami per reparto',
            data: <?= json_encode($conteggi) ?>,
            backgroundColor: ['rgba(54, 162, 235, 0.6)','rgba(255, 99, 132, 0.6)','rgba(255, 206, 86, 0.6)'],
            borderColor: ['rgba(54, 162, 235, 1)','rgba(255, 99, 132, 1)','rgba(255, 206, 86, 1)'],
            borderWidth: 1
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
