<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

$stmtInfo = $conn->prepare("
    SELECT nome, cognome
    FROM paziente
    WHERE codiceFiscale = ?
");
$stmtInfo->bind_param("s", $cf);
$stmtInfo->execute();
$paziente = $stmtInfo->get_result()->fetch_assoc();

// questa query non funziona perche 
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
        <div id="overlay" onclick="toggleMenu()"></div>
        <div id="mainContent"></div>

        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <h1>Prenota un Nuovo Esame</h1>
        </header>

        <?php
            if(count($spec) > 0) {
                echo '<br>';
                echo '<form action="prenota.php" method="POST">';
                echo '<label for="specializzazione">Scegli una specializzazione:    </label>';
                echo '<select name="specializzazione" id="specializzazione" onchange="this.form.submit()">';
                echo '<option value="">-- Seleziona --</option>';
                foreach($spec as $s) {
                    echo '<option value="' . $s['codiceSpecializzazione'] . '">' . $s['titolo'] . '</option>';
                }
                echo '</select>';
                echo '</br>';
                echo '</form>';
            } else {
                echo '<p>Nessuna specializzazione disponibile al momento.</p>';
            }

            
            if(isset($_POST['specializzazione'])) {
                $stmtMedici = $conn->prepare("
                    SELECT codiceMedico, nome, cognome, codiceSpecializzazione
                    FROM medico
                    WHERE codiceSpecializzazione = ?
                ");
                $stmtMedici->bind_param("s", $_POST['specializzazione']);
                $stmtMedici->execute();
                $medici = $stmtMedici->get_result()->fetch_all(MYSQLI_ASSOC);

                foreach($medici as $m) {
                    echo '<p>Medico: ' . $m['nome'] . ' ' . $m['cognome'] . '</p>';
                }
            } else {
                echo '<div class="error-msg">';
                echo '<p>Seleziona una specializzazione per vedere i medici disponibili.</p>';
                echo '</div>';
            }

            // Impegno i parametri di ricerca (sostituire con i valori reali del form)
            $data = $_POST['data'] ?? date('Y-m-d');
            $oraInizio = isset($_POST['oraInizio']) ? intval($_POST['oraInizio']) : 8;
            $oraFine = isset($_POST['oraFine']) ? intval($_POST['oraFine']) : 9;
            $giorno = $_POST['giorno'] ?? date('D');
            $giorno = strtoupper(substr($giorno, 0, 2));
            if($giorno === 'SA' || $giorno === 'SU') {
                $giorno = 'LU'; // fallback per esempio
            }

            // Verifico disponibilità in base a orari e esami prenotati
            $stmtMediciDisponibili = $conn->prepare("
                SELECT DISTINCT
                    m.codiceMedico,
                    m.nome,
                    m.cognome,
                    r.nomeReparto,
                    ol.oraInizio,
                    ol.oraFine
                FROM medico m
                INNER JOIN medico_orariolavoro mol ON m.codiceMedico = mol.codiceMedico
                INNER JOIN orariolavoro ol ON mol.giorno = ol.giorno AND mol.oraInizio = ol.oraInizio
                LEFT JOIN esame e ON e.codiceMedico = m.codiceMedico
                LEFT JOIN storico s ON s.codiceEsame = e.codiceEsame
                    AND s.data = ?
                    AND NOT (s.oraFine <= ? OR s.oraInizio >= ?)
                INNER JOIN reparto r ON m.codiceReparto = r.codiceReparto
                WHERE mol.giorno = ?
                    AND ol.oraInizio <= ?
                    AND ol.oraFine > ? 
                    AND s.codiceEsame IS NULL
                ORDER BY m.cognome, m.nome
            ");
            $stmtMediciDisponibili->bind_param("siiiss", $data, $oraInizio, $oraFine, $giorno, $oraInizio, $oraFine);
            $stmtMediciDisponibili->execute();
            $mediciDisponibili = $stmtMediciDisponibili->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach($mediciDisponibili as $md) {
                echo '<p>Medico Disponibile: ' . $md['nome'] . ' ' . $md['cognome'] . ' - Reparto: ' . $md['nomeReparto'] . ' - Orario: ' . $md['oraInizio'] . '-' . $md['oraFine'] . '</p>';
            }

            
        ?>

        <script src="../js/menu.js" defer></script>
    </body>
</html>