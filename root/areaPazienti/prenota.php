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
            function selected($val1, $val2) {
                if (trim($val1) == trim($val2)) {
                    return " selected=\"selected\"";
                }
                return "";
            }
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

            
            $specializzazione_selezionata = $_POST['specializzazione'] ?? '';
            $data = $_POST['data'] ?? date('Y-m-d');
            $giorno = strtoupper(substr(date('D', strtotime($data)), 0, 2));
            $mappaGiorni = [
                'MO' => 'LU', 'TU' => 'MA', 'WE' => 'ME', 'TH' => 'GI', 'FR' => 'VE', 'SA' => 'SA', 'SU' => 'SU'
            ];
            $giorno = $mappaGiorni[$giorno] ?? 'LU';

            echo '<form action="prenota.php" method="POST">';
            echo '<input type="hidden" name="specializzazione" value="' . htmlspecialchars($specializzazione_selezionata) . '">';
            echo '<label for="data">Data:</label> ';
            echo '<input type="date" name="data" id="data" value="' . htmlspecialchars($data) . '" required> ';
            echo '<button type="submit">Aggiorna disponibilità</button>';
            echo '</form>';

            if($specializzazione_selezionata !== '') {
                $stmtMedici = $conn->prepare(
                    'SELECT m.codiceMedico, m.nome, m.cognome, r.nomeReparto FROM medico m INNER JOIN reparto r ON m.codiceReparto = r.codiceReparto WHERE m.codiceSpecializzazione = ?'
                );
                $stmtMedici->bind_param('s', $specializzazione_selezionata);
                $stmtMedici->execute();
                $medici = $stmtMedici->get_result()->fetch_all(MYSQLI_ASSOC);

                $availableSlots = [];

                foreach($medici as $m) {
                    $stmtOrari = $conn->prepare(
                        'SELECT ol.oraInizio, ol.oraFine FROM medico_orariolavoro mol INNER JOIN orariolavoro ol ON mol.giorno = ol.giorno AND mol.oraInizio = ol.oraInizio WHERE mol.codiceMedico = ? AND mol.giorno = ?'
                    );
                    $stmtOrari->bind_param('ss', $m['codiceMedico'], $giorno);
                    $stmtOrari->execute();
                    $orari = $stmtOrari->get_result()->fetch_all(MYSQLI_ASSOC);

                    $stmtPrenotati = $conn->prepare(
                        'SELECT s.oraInizio, s.oraFine FROM storico s INNER JOIN esame e ON s.codiceEsame = e.codiceEsame WHERE e.codiceMedico = ? AND s.data = ?'
                    );
                    $stmtPrenotati->bind_param('ss', $m['codiceMedico'], $data);
                    $stmtPrenotati->execute();
                    $prenotati = $stmtPrenotati->get_result()->fetch_all(MYSQLI_ASSOC);

                    foreach($orari as $orario) {
                        $startHour = (int)$orario['oraInizio'];
                        $endHour = (int)$orario['oraFine'];
                        for($h = $startHour; $h < $endHour; $h++) {
                            $slotStart = $h;
                            $slotEnd = $h + 1;

                            $conflict = false;
                            foreach($prenotati as $p) {
                                $pStart = (int)$p['oraInizio'];
                                $pEnd = (int)$p['oraFine'];
                                if($slotStart < $pEnd && $slotEnd > $pStart) {
                                    $conflict = true;
                                    break;
                                }
                            }

                            if(!$conflict) {
                                $availableSlots[] = [
                                    'medico' => $m['codiceMedico'],
                                    'label' => $m['nome'] . ' ' . $m['cognome'] . ' (' . $m['nomeReparto'] . ') ' . sprintf('%02d:00-%02d:00', $slotStart, $slotEnd),
                                    'value' => $m['codiceMedico'] . '|' . $slotStart . '|' . $slotEnd
                                ];
                            }
                        }
                    }
                }

                if(count($availableSlots) > 0) {
                    echo '<form action="prenota.php" method="POST">';
                    echo '<input type="hidden" name="specializzazione" value="' . htmlspecialchars($specializzazione_selezionata) . '">';
                    echo '<input type="hidden" name="data" value="' . htmlspecialchars($data) . '">';
                    echo '<label for="slot">Scegli uno slot orario:</label>';
                    echo '<select name="slot" id="slot" required>';
                    echo '<option value="">-- Seleziona --</option>';
                    foreach($availableSlots as $slot) {
                        echo '<option value="' . htmlspecialchars($slot['value']) . '">' . htmlspecialchars($slot['label']) . '</option>';
                    }
                    echo '</select>';
                    echo '<button type="submit" name="prenotaSlot">Prenota</button>';
                    echo '</form>';
                } else {
                    echo '<p>Nessuno slot disponibile per la data selezionata.</p>';
                }

                if(isset($_POST['prenotaSlot']) && !empty($_POST['slot'])) {
                    list($codiceMedicoSelezionato, $inizioSelezionato, $fineSelezionato) = explode('|', $_POST['slot']);

                    // Implementa il salvataggio reale nel DB qui (esame + storico), oppure adattalo alle tue logiche.
                    echo '<p>Hai selezionato: medico ' . htmlspecialchars($codiceMedicoSelezionato) . ', ' . htmlspecialchars($inizioSelezionato) . ':00-' . htmlspecialchars($fineSelezionato) . ':00 il ' . htmlspecialchars($data) . '.</p>';
                    echo '<p>Qui puoi inserire la query INSERT in `storico`.</p>';
                }

            } else {
                echo '<div class="error-msg"><p>Seleziona una specializzazione per vedere gli slot disponibili.</p></div>';
            }
        ?>

        <script src="../js/menu.js" defer></script>
    </body>
</html>