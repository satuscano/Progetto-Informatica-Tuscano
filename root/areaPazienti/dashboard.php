<?php
require_once("../auth.php");
requireRole('paziente');
require_once("../components/menuPaziente.php");


$conn = new mysqli("localhost", "root", "", "databaseprogetto");
if($conn->connect_error){ die("Connessione fallita: ".$conn->connect_error); }

$cf = $_SESSION['codiceFiscale'];

$stmt = $conn->prepare("SELECT COUNT(*) as totaleEsami FROM storico WHERE codiceFiscale = ?");
$stmt->bind_param("s", $cf);
$stmt->execute();
$totaleEsami = $stmt->get_result()->fetch_assoc()['totaleEsami'];

$stmt2 = $conn->prepare("SELECT diagnosi, data FROM storico WHERE codiceFiscale = ? ORDER BY data DESC LIMIT 1");
$stmt2->bind_param("s", $cf);
$stmt2->execute();
$ultimoEsame = $stmt2->get_result()->fetch_assoc();

$reparti = $conn->query("SELECT nomeReparto FROM reparto")->fetch_all(MYSQLI_ASSOC);
$reparti = array_column($reparti, 'nomeReparto');
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

$stmtInfo = $conn->prepare("
    SELECT nome, cognome, dataNascita, ind_citta, ind_via, ind_civico, ind_cap 
    FROM paziente 
    WHERE codiceFiscale = ?
");
$stmtInfo->bind_param("s", $cf);
$stmtInfo->execute();
$paziente = $stmtInfo->get_result()->fetch_assoc();

$stmtPag = $conn->prepare("
    SELECT codicePagamento, dataPagamento, somma, metodo 
    FROM pagamento 
    WHERE codiceFiscale = ? 
    ORDER BY dataPagamento DESC
");
$stmtPag->bind_param("s", $cf);
$stmtPag->execute();
$pagamenti = $stmtPag->get_result();

?>

<html>
    <head>
        <title>Dashboard Paziente</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="../CSS/theme.css">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; }
            .card { background: #fff; padding: 20px; margin: 10px 0; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
            .stats { display: flex; gap: 20px; flex-wrap: wrap; }
            .stat-card { flex: 1; min-width: 150px; text-align: center; }
            ul { padding-left: 20px; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <?php require_once("../components/menuPaziente.php"); ?>
        <div id="overlay" onclick="toggleMenu()"></div>
        <div id="mainContent"></div>

        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <h1>Benvenuto, <?= $paziente['nome'] ?></h1>
        </header>
     

        <div class="chart-wrapper">
            <canvas id="graficoEsami" width="400" height="200"></canvas>
        </div>

        <div class="card">
            <h3>PROFILO</h3>
            <p><strong>Nome:</strong> <?= $paziente['nome'] ?></p>
            <p><strong>Cognome:</strong> <?= $paziente['cognome'] ?></p>
            <p><strong>Nascita:</strong> <?= $paziente['dataNascita'] ?></p>
            <p><strong>Indirizzo:</strong> <?= $paziente['ind_via'] ?>, <?= $paziente['ind_civico'] ?>, <?= $paziente['ind_cap'] ?> <?= $paziente['ind_citta'] ?></p>
        </div>

        <div class="card">
            <h3>ULTIME VISITE</h3>
            <ul>
                <?php 
                $stmtUltimi = $conn->prepare("
                    SELECT data, diagnosi 
                    FROM storico 
                    WHERE codiceFiscale = ?
                    ORDER BY data DESC
                    LIMIT 5
                ");
                $stmtUltimi->bind_param("s", $cf);
                $stmtUltimi->execute();
                $ultimi = $stmtUltimi->get_result();
                while($row = $ultimi->fetch_assoc()): ?>
                    <li><?= $row['data'] ?> – <?= $row['diagnosi'] ?></li>
                <?php endwhile;
                ?>
            </ul>
        </div>

        <div class="stats">
            <div class="card">
                <h4>PAGAMENTI</h4>
                <ul>
                    <?php while($row = $pagamenti->fetch_assoc()): ?>
                        <li>
                            <?= $row['dataPagamento'] ?> – <?= $row['somma'] ?> € (<?= $row['metodo'] ?>)
                            <?php
                                $stmtFattura = $conn->prepare("SELECT codiceFattura FROM fattura WHERE codicePagamento = ?");
                                $stmtFattura->bind_param("i", $row['codicePagamento']);
                                $stmtFattura->execute();
                                $resultFattura = $stmtFattura->get_result();
                                if ($resultFattura->num_rows > 0) {
                                    echo '<a href="visualizzaFattura.php?id=' . $row['codicePagamento'] . '">[Vedi fattura]</a>';
                                }
                            ?>
                        </li>
                    <?php endwhile;
                    ?>
                </ul>
            </div>
            <div class="card">
                <h4>ESAMI TOTALI</h4>
                <p><?= $totaleEsami ?></p>
            </div>
        </div>

        <!-- Grafico esami per reparto -->
        <script>
            const ctx = document.getElementById('graficoEsami').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($reparti) ?>,
                    datasets: [{
                        label: 'Numero di esami',
                        data: <?= json_encode($conteggi) ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Esami per reparto',
                            font: {
                                size: 18
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        </body>
        <script src="../js/menu.js" defer></script>
    </body>
</html>

<?php
    $conn->close();
?>