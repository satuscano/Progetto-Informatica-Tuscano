<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

$error = '';
$success = '';

// Tipo di esame selezionato (può arrivare da GET per filtro o da POST per submit)
$selectedTipo = $_GET['tipoEsame'] ?? $_POST['tipoEsame'] ?? '';

// Recupera tipi esame
$tipiEsame = $conn->query("SELECT DISTINCT tipo FROM specializzazione WHERE tipo IS NOT NULL ORDER BY tipo");

// Recupera medici per il tipo selezionato (o tutti se non selezionato)
$mediciList = [];
if ($selectedTipo) {
    $stmt = $conn->prepare(
        "SELECT m.codiceMedico, m.nome, m.cognome
         FROM medico m
         JOIN specializzazione s ON m.codiceMedico = s.codiceMedico
         WHERE s.tipo = ?
         ORDER BY m.nome"
    );
    $stmt->bind_param("s", $selectedTipo);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $mediciList[] = $row;
    }
    $stmt->close();
} else {
    $res = $conn->query("SELECT codiceMedico, nome, cognome FROM medico ORDER BY nome");
    while ($row = $res->fetch_assoc()) {
        $mediciList[] = $row;
    }
}

// Recupera date disponibili in base alla disponibilità dei medici del tipo selezionato
$dateDisponibili = [];
if (!empty($mediciList)) {
    $mediciCodici = array_column($mediciList, 'codiceMedico');

    for ($i = 0; $i < 30; $i++) {
        $dataCorrente = date('Y-m-d', strtotime("+$i days"));
        $giorno = date('w', strtotime($dataCorrente));
        $hasSlot = false;
        for ($ora = 8; $ora <= 18; $ora++) {
            foreach ($mediciCodici as $med) {
                $medicoOrario = $conn->prepare("SELECT COUNT(*) FROM medico_orariolavoro WHERE codiceMedico = ? AND giorno = ? AND oraInizio = ?");
                $medicoOrario->bind_param("sii", $med, $giorno, $ora);
                $medicoOrario->execute();
                $medicoOrario->bind_result($countOrario);
                $medicoOrario->fetch();
                $medicoOrario->close();

                if ($countOrario == 0) {
                    continue;
                }

                $check = $conn->prepare("SELECT COUNT(*) FROM storico s JOIN esame e ON s.codiceEsame = e.codiceEsame WHERE e.codiceMedico = ? AND s.data = ? AND s.oraInizio = ?");
                $check->bind_param("ssi", $med, $dataCorrente, $ora);
                $check->execute();
                $check->bind_result($count);
                $check->fetch();
                $check->close();
                if ($count == 0) {
                    $hasSlot = true;
                    break 2;
                }
            }
        }
        if ($hasSlot) {
            $dateDisponibili[] = $dataCorrente;
        }
    }
}

// Valori selezionati (utili quando si ricarica la pagina per filtrare o in caso di errore)
$selectedData = $_POST['data'] ?? $_GET['data'] ?? '';
$selectedOra = $_POST['oraInizio'] ?? $_GET['oraInizio'] ?? '';
$selectedMedico = $_POST['codiceMedico'] ?? $_GET['codiceMedico'] ?? '';
$selectedMotivo = $_POST['motivo'] ?? '';

// Controlla se c'è un messaggio di successo dalla redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $esame = $_GET['esame'] ?? '';
    $data = $_GET['data'] ?? '';
    $ora = $_GET['ora'] ?? '';
    $medico = $_GET['medico'] ?? '';
    $ambulatorio = $_GET['ambulatorio'] ?? '';
    $success = "Prenotazione creata (esame $esame) per il $data alle " . sprintf("%02d:00", $ora) . " con medico $medico in ambulatorio $ambulatorio.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoEsame = filter_input(INPUT_POST, 'tipoEsame', FILTER_SANITIZE_STRING);
    $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $oraInizio = filter_input(INPUT_POST, 'oraInizio', FILTER_VALIDATE_INT);
    $codiceMedico = filter_input(INPUT_POST, 'codiceMedico', FILTER_SANITIZE_STRING);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);

    if (!$tipoEsame || !$data || $oraInizio === false || !$codiceMedico || !$motivo) {
        $error = 'Tutti i campi sono obbligatori.';
    } else {
        // verifica che il medico appartenga alla specializzazione scelta
        $validMedico = false;
        foreach ($mediciList as $m) {
            if ($m['codiceMedico'] === $codiceMedico) {
                $validMedico = true;
                break;
            }
        }

        if (!$validMedico) {
            $error = 'Il medico selezionato non è specializzato per il tipo di esame scelto.';
        } elseif (strtotime($data) < strtotime(date('Y-m-d'))) {
            $error = 'Non puoi prenotare per una data passata.';
        } else {
            $giorno = date('w', strtotime($data));

            // verifica che il medico sia disponibile in quel giorno/ora
            $medicoCheck = $conn->prepare(
                "SELECT COUNT(*) as count FROM medico_orariolavoro 
                 WHERE codiceMedico = ? AND giorno = ? AND oraInizio = ?"
            );
            $medicoCheck->bind_param("sii", $codiceMedico, $giorno, $oraInizio);
            $medicoCheck->execute();
            $medicoCheck->bind_result($count);
            $medicoCheck->fetch();
            $medicoCheck->close();

            if ($count == 0) {
                $error = 'Il medico selezionato non è disponibile in questo slot.';
            } else {
                // verifica che non sia già prenotato
                $checkBooking = $conn->prepare(
                    "SELECT COUNT(*) as count FROM storico s 
                     JOIN esame e ON s.codiceEsame = e.codiceEsame 
                     WHERE e.codiceMedico = ? AND s.data = ? AND s.oraInizio = ?"
                );
                $checkBooking->bind_param("ssi", $codiceMedico, $data, $oraInizio);
                $checkBooking->execute();
                $checkBooking->bind_result($count);
                $checkBooking->fetch();
                $checkBooking->close();

                if ($count > 0) {
                    $error = 'Questo slot è già prenotato.';
                } else {
                    // scegli un ambulatorio generico
                    $amb = $conn->query("SELECT codiceAmbulatorio FROM ambulatorio ORDER BY codiceAmbulatorio LIMIT 1");
                    $ambRow = $amb->fetch_assoc();
                    $codAmbulatorio = $ambRow ? intval($ambRow['codiceAmbulatorio']) : 0;

                    // genera codiceEsame
                    $maxEsame = $conn->query("SELECT MAX(codiceEsame) AS maxCod FROM esame");
                    $row = $maxEsame->fetch_assoc();
                    $codiceEsame = intval($row['maxCod'] ?? 0) + 1;

                    $conn->begin_transaction();

                    $insEsame = $conn->prepare(
                        "INSERT INTO esame (codiceEsame, codiceAmbulatorio, codiceMedico, codiceFiscale) 
                         VALUES (?, ?, ?, ?)"
                    );
                    $insEsame->bind_param("iiss", $codiceEsame, $codAmbulatorio, $codiceMedico, $cf);
                    $insEsame->execute();

                    if ($insEsame->affected_rows === 0) {
                        $error = 'Errore creazione esame.';
                        $insEsame->close();
                        $conn->rollback();
                    } else {
                        $insEsame->close();

                        $insStorico = $conn->prepare(
                            "INSERT INTO storico (codiceEsame, data, oraInizio, codiceFiscale, prescrizione) 
                             VALUES (?, ?, ?, ?, ?)"
                        );
                        $insStorico->bind_param("issss", $codiceEsame, $data, $oraInizio, $cf, $motivo);
                        $insStorico->execute();

                        if ($insStorico->affected_rows === 0) {
                            $error = 'Errore creazione prenotazione.';
                            $insStorico->close();
                            $conn->rollback();
                        } else {
                            $insStorico->close();
                            $conn->commit();
                            // Redirect per evitare reinvio form
                            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1&esame=' . $codiceEsame . '&data=' . $data . '&ora=' . $oraInizio . '&medico=' . $codiceMedico . '&ambulatorio=' . $codAmbulatorio);
                            exit;
                        }
                    }
                }
            }
        }
    }
}

?>

<html>
    <head>
        <title>Prenota Esame</title>
        <link rel="stylesheet" href="../CSS/theme.css">
    </head>
    <body>
        <?php require_once("../components/menuPaziente.php"); ?>
        <div id="overlay" onclick="toggleMenu()"></div>
        <div id="mainContent"></div>

        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
        <h1>Prenota un Esame</h1>
        </header>

        <div class="card">
            <?php if ($error): ?>
                <div class="error-msg">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <p>Seleziona il tipo di esame per mostrare i medici disponibili e le date con posti liberi.</p>
            
            <form method="POST" action="">
                    <label for="tipoEsame">Tipo di esame:</label>
                    <select name="tipoEsame" id="tipoEsame" required>
                        <option value="">-- Scegli tipo --</option>
                        <?php $tipiEsame->data_seek(0); while ($tipo = $tipiEsame->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($tipo['tipo']) ?>" <?= $selectedTipo === $tipo['tipo'] ? 'selected' : '' ?>><?= htmlspecialchars($tipo['tipo']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <br>
                    <br>

                    <label for="data">Data:</label>
                    <select name="data" id="data" required>
                        <option value="">-- Scegli data --</option>
                        <?php foreach ($dateDisponibili as $dataOp): ?>
                            <option value="<?= $dataOp ?>" <?= $selectedData === $dataOp ? 'selected' : '' ?>><?= date('d/m/Y', strtotime($dataOp)) ?> (<?= date('l', strtotime($dataOp)) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <br>
                    <br>

                    <label for="oraInizio">Ora:</label>
                    <select name="oraInizio" id="oraInizio" required>
                        <option value="">-- Scegli ora --</option>
                        <?php for ($h = 8; $h <= 18; $h++): ?>
                            <option value="<?= $h ?>" <?= $selectedOra == $h ? 'selected' : '' ?>><?= sprintf("%02d:00", $h) ?> - <?= sprintf("%02d:00", $h+1) ?></option>
                        <?php endfor; ?>
                    </select>
                    <br>
                    <br>

                    <label for="codiceMedico">Medico:</label>
                    <select name="codiceMedico" id="codiceMedico" required>
                        <option value="">-- Scegli medico --</option>
                        <?php if (empty($mediciList) && $selectedTipo): ?>
                            <option value="" disabled>Nessun medico disponibile per questo tipo</option>
                        <?php endif; ?>
                        <?php foreach ($mediciList as $med): ?>
                            <option value="<?= $med['codiceMedico'] ?>" <?= $selectedMedico === $med['codiceMedico'] ? 'selected' : '' ?>><?= htmlspecialchars($med['nome']) ?> <?= htmlspecialchars($med['cognome']) ?> (<?= $med['codiceMedico'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <br>
                    <br>

                    <label for="motivo">Motivo dell'esame / note:</label>
                    <textarea name="motivo" id="motivo" rows="3" required><?= htmlspecialchars($selectedMotivo) ?></textarea>
                    <br>
                    <br>

                    <button type="submit">Prenota</button>
                </form>
        </div>
    
    
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tipoSelect = document.getElementById('tipoEsame');
                if (!tipoSelect) return;

                tipoSelect.addEventListener('change', function () {
                    const tipo = this.value;
                    const params = new URLSearchParams(window.location.search);
                    if (tipo) {
                        params.set('tipoEsame', tipo);
                    } else {
                        params.delete('tipoEsame');
                    }
                    // Reset other selections when changing tipo
                    params.delete('data');
                    params.delete('oraInizio');
                    params.delete('codiceMedico');
                    const query = params.toString();
                    window.location.search = query ? '?' + query : '';
                });
            });
        </script>
        <script src="../js/menu.js" defer></script>
    </body>
</html>

<?php
    $conn->close();
?>