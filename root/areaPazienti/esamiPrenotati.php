<?php
require_once("../auth.php");
requireRole('paziente');
require_once("../components/menuPaziente.php");


$conn = new mysqli("localhost", "root", "", "databaseprogetto");
if($conn->connect_error){ die("Connessione fallita: ".$conn->connect_error); }

$cf = $_SESSION['codiceFiscale'];

$stmtInfo = $conn->prepare("
    SELECT nome, cognome, dataNascita, ind_citta, ind_via, ind_civico, ind_cap 
    FROM paziente 
    WHERE codiceFiscale = ?
");
$stmtInfo->bind_param("s", $cf);
$stmtInfo->execute();
$paziente = $stmtInfo->get_result()->fetch_assoc();

$stmtEsami = $conn->prepare("
    SELECT 
    esame.codiceEsame,
    esame.codiceAmbulatorio AS ambulatorio,
    storico.data AS dataPrenotazione,
    storico.oraInizio AS oraInizio,
    CONCAT(medico.nome, ' ', medico.cognome) AS medico,
    esame.diagnosi,
    esame.referto
    FROM esame
    JOIN medico ON esame.codiceMedico = medico.codiceMedico
    JOIN storico ON esame.codiceEsame = storico.codiceEsame
    WHERE esame.codiceFiscale = ?
");
$stmtEsami->bind_param("s", $cf);
$stmtEsami->execute();
$esamiPrenotati = $stmtEsami->get_result();

$numeroEsami = $esamiPrenotati->num_rows;

?>

<html>
    <head>
        <title>Esami Prenotati</title>
        <link rel="stylesheet" href="../CSS/theme.css">
    </head>
    <body>
        <?php require_once("../components/menuPaziente.php"); ?>
        <div id="overlay" onclick="toggleMenu()"></div>
        <div id="mainContent"></div>

        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <h1>Esami Prenotati da <?= $paziente['nome'] ?></h1>
        </header>

        <div class="card">
            <?php if($numeroEsami > 0): ?>
                <ul>
                    <?php while($esame = $esamiPrenotati->fetch_assoc()): ?>
                        <li>
                            <strong>Codice Esame: <?= $esame['codiceEsame'] ?></strong><br>
                            Ambulatorio: <?= $esame['ambulatorio'] ?><br>
                            Data esame: <?= date("d/m/Y", strtotime($esame['dataPrenotazione'])) ?><br>
                            Ora inizio: <?= sprintf("%02d:00", $esame['oraInizio']) ?><br>
                            Medico: <?= $esame['medico'] ?><br>
                            Diagnosi: <?= $esame['diagnosi'] ?: 'Non disponibile' ?><br>
                            Referto: <?= $esame['referto'] ?: 'Non disponibile' ?> <br><br>

                            <button onclick="toggleEdit('formModifica')">Modifica</button>
                            <button onclick="toggleEdit('formCancella')">Cancella</button>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="error-msg">
                    <p>Nessun esame prenotato al momento.</p>
                </div>
            <?php endif; ?>
        </div>
    
        <script src="../js/menu.js" defer></script>
    </body>
</html>

<?php
    $conn->close();
?>