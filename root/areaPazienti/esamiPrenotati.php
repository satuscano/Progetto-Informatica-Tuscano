<?php
include("../inc/start.inc");

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
    LEFT JOIN medico ON esame.codiceMedico = medico.codiceMedico
    LEFT JOIN storico ON esame.codiceEsame = storico.codiceEsame
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

        
            <?php if($numeroEsami > 0): ?>
                <ul>
                    <?php while($esame = $esamiPrenotati->fetch_assoc()): ?>
                        <div class="card">
                        <strong>Codice Esame: <?= $esame['codiceEsame'] ?></strong><br>
                        Ambulatorio: <?= $esame['ambulatorio'] ?: 'Non disponibile' ?><br>
                        Data esame: <?php if(!empty($esame['dataPrenotazione'])) { echo date("d/m/Y", strtotime($esame['dataPrenotazione'])); } else { echo 'Non prenotato'; } ?><br>
                        Ora inizio: <?php if(isset($esame['oraInizio']) && $esame['oraInizio'] !== null) { echo sprintf("%02d:00", $esame['oraInizio']); } else { echo 'Non disponibile'; } ?><br>
                        Medico: <?= $esame['medico'] ?: 'Non disponibile' ?><br>
                        Diagnosi: <?= $esame['diagnosi'] ?: 'Non disponibile' ?><br>
                        Referto: <?= $esame['referto'] ?: 'Non disponibile' ?> <br><br>

                        <?php if(!empty($esame['dataPrenotazione'])): ?>
                            <button onclick="toggleEdit('formModifica_<?= $esame['codiceEsame'] ?>')">Modifica</button>
                            <button onclick="toggleEdit('formCancella_<?= $esame['codiceEsame'] ?>')">Cancella</button>

                            <form id="formModifica_<?= $esame['codiceEsame'] ?>" class="hidden" method="POST" action="modificaPrenotazione.php">
                                <input type="hidden" name="codiceEsame" value="<?= $esame['codiceEsame'] ?>">
                                <label for="data">Nuova data:</label>
                                <input type="date" name="data" required>
                                <label for="ora">Nuova ora:</label>
                                <input type="time" name="ora" required>
                                <button type="submit">Salva modifiche</button>
                            </form>

                            <form id="formCancella_<?= $esame['codiceEsame'] ?>" class="hidden" method="POST" action="annullaPrenotazione.php">
                                <input type="hidden" name="codiceEsame" value="<?= $esame['codiceEsame'] ?>">
                                <input type="hidden" name="data" value="<?= $esame['dataPrenotazione'] ?>">
                                <input type="hidden" name="oraInizio" value="<?= $esame['oraInizio'] ?>">
                                <br>
                                <button type="submit">Conferma cancellazione</button>
                            </form>
                        <?php else: ?>
                            <p>Nessuna prenotazione associata a questo esame</p>
                        <?php endif; ?>
                        </div>
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