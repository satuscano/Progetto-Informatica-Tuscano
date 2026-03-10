<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

define('DEFAULT_START_HOUR', 8); // Ora di inizio più bassa per prenotazioni
define('DEFAULT_END_HOUR', 18); // Ora di fine più alta per prenotazioni

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING); // la filter_sanitize_string serve a togliere eventuali caratteri non desiderati, come tag HTML o simili
    $oraInizio = filter_input(INPUT_POST, 'oraInizio', FILTER_VALIDATE_INT); // la filter_validate_int serve a verificare che sia un numero intero
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    
    // verifica che i dati siano validi e che l'ora sia in un range accettabile
    if (!$data || $oraInizio === false || !$motivo) { // se uno dei dati è mancante o non valido, mostra un errore
        $error = 'Data, ora e motivo sono obbligatori.';
    } else {
        if (strtotime($data) < strtotime(date('Y-m-d'))) { // se la data è nel passato, mostra un errore
            $error = 'Non puoi prenotare per una data passata.';
        } else {
            $giorno = date('w', strtotime($data)); // w restituisce il giorno della settimana, la strtotime serve a convertire la data in un timestamp per poter estrarre il giorno della settimana
            
            // trova un medico libero in quel giorno/ora
            $medicoQuery = $conn->prepare(
                "SELECT codiceMedico FROM medico_orariolavoro 
                 WHERE giorno = ? AND oraInizio = ? LIMIT 1"
            );
            $medicoQuery->bind_param("ii", $giorno, $oraInizio);
            $medicoQuery->execute();
            $medicoQuery->bind_result($codiceMedico);
            if (!$medicoQuery->fetch()) {
                $error = 'Nessun medico disponibile in questo slot.';
                $medicoQuery->close();
            } else {
                $medicoQuery->close();
                
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
                        $success = "Prenotazione creata (esame $codiceEsame) con medico $codiceMedico in ambulatorio $codAmbulatorio.";
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
            <h1>Prenota nuovo esame</h1>
        </header>

        <div class="card">
            <?php if ($error): ?>
                <div class="error-msg" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                    <label for="data">Data desiderata:</label>
                    <input type="date" name="data" id="data" required min="<?= date('Y-m-d') ?>">

                    <label for="oraInizio">Ora desiderata:</label>
                    <select name="oraInizio" id="oraInizio" required>
                        <option value="">-- Scegli un'ora --</option>
                        <?php for ($h = 8; $h <= 18; $h++): ?>
                            <option value="<?= $h ?>"><?= sprintf("%02d:00", $h) ?> - <?= sprintf("%02d:00", $h+1) ?></option>
                        <?php endfor; ?>
                    </select>

                    <label for="motivo">Motivo dell'esame / note:</label>
                    <textarea name="motivo" id="motivo" rows="3" required></textarea>

                    <button type="submit">Richiedi prenotazione</button>
                </form>
        </div>
    
    
        <script src="../js/menu.js" defer></script>
    </body>
</html>

<?php
    $conn->close();
?>