<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

// Calcola il numero totale di esami effettuati dal paziente con codice fiscale $cf
$stmt = $conn->prepare("SELECT COUNT(*) as totaleEsami
                                FROM storico
                                WHERE codiceFiscale = ?");
$stmt->bind_param("s", $cf); // Dove la query trova '?' sostituisci con il valore di $cf (codice fiscale del paziente, s -> stringa)
$stmt->execute(); // Esegui la query
$totaleEsami = $stmt->get_result()->fetch_assoc()['totaleEsami']; // Dal risultato della query, crea un array associativo e prendi il valore della chiave 'totaleEsami'

// La query seleziona la diagnosi più recente (LIMIT 1 -> ultima riga del risultato)
$stmt2 = $conn->prepare("SELECT diagnosi, data
                                FROM storico
                                WHERE codiceFiscale = ?
                                ORDER BY data DESC
                                LIMIT 1");

$stmt2->bind_param("s", $cf);
$stmt2->execute();
$ultimoEsame = $stmt2->get_result()->fetch_assoc(); // Salvo il risultato (diagnosi e data)

// Salvo in un array tutti i reparti
$reparti = $conn->query("SELECT nomeReparto
                                FROM reparto")->fetch_all(MYSQLI_ASSOC);

/*
fetch_all(MYSQLI_ASSOC) -> legge tutte le righe insieme e restituisce un array di array associativi
fetch_assoc() -> legge una singola riga dal risultato della query come array associativo (con while)
*/

$reparti = array_column($reparti, 'nomeReparto'); // Prende solo la colonna 'nomeReparto' dall'array associativo e restituisce un array semplice con i nomi dei reparti
$conteggi = []; // Array che conterrà il numero di esami per ogni reparto, da passare al grafico

// Per ogni reparto, conta il numero di esami effettuati dal paziente in quel reparto
foreach($reparti as $rep){
    // La query conta il numero di esami per ogni reparto
    $stmt3 = $conn->prepare("
        SELECT COUNT(*) as tot
        FROM storico s 
        JOIN esame e ON s.codiceEsame = e.codiceEsame
        JOIN ambulatorio a ON e.codiceAmbulatorio = a.codiceAmbulatorio
        JOIN reparto r ON a.codiceReparto = r.codiceReparto
        WHERE s.codiceFiscale = ? AND r.nomeReparto = ?
    ");
    $stmt3->bind_param("ss", $cf, $rep); // $rep è il nome del reparto, $cf è il codice fiscale del paziente
    $stmt3->execute();
    $res = $stmt3->get_result()->fetch_assoc(); // Il risultato è un array associativo con la chiave 'tot' che contiene il numero di esami per quel reparto
    $conteggi[] = $res['tot']; // Creo un array semplice con i conteggi, da passare al grafico (stesso ordine dei reparti)
}

// Prendo le informazioni del paziente per mostrarle nella dashboard
$stmtInfo = $conn->prepare("
    SELECT nome, cognome, dataNascita, ind_citta, ind_via, ind_civico, ind_cap 
    FROM paziente 
    WHERE codiceFiscale = ?
");
$stmtInfo->bind_param("s", $cf);
$stmtInfo->execute();
$paziente = $stmtInfo->get_result()->fetch_assoc();

// Prendo i pagamenti effettuati dal paziente per mostrarli nella dashboard
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
    </head>
    <body>
        <?php require_once("../components/menuPaziente.php"); ?>

        <!-- Overlay per chiudere il menu quando è aperto -->
        <div id="overlay" onclick="toggleMenu()"></div>
        <div id="mainContent"></div>

        <!-- Contenuto principale della dashboard -->
        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <h1>Benvenuto, <?= $paziente['nome'] ?></h1>
        </header>

        <!-- Creo il grafico -->
        <div class="chart-wrapper">
            <canvas id="graficoEsami" width="400" height="200"></canvas>
        </div>

        <!-- Sezione profilo e statistiche -->
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
                    <?php while($row = $pagamenti->fetch_assoc()): /*row contiene la riga dell'array, che scorre a ogni ciclo*/?>
                        <li>
                            <?= $row['dataPagamento'] ?> – <?= $row['somma'] ?> € (<?= $row['metodo'] ?>)
                            <?php
                                $stmtFattura = $conn->prepare("SELECT codiceFattura
                                                                        FROM fattura
                                                                        WHERE codicePagamento = ?");
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