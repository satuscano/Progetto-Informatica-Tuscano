<html>
<head>
    <title>Orari Lavoro Ambulatorio A</title>
    <style>
        table { border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 0.5px solid black; padding: 5px 10px;}
        th { background-color: #9dd1ff; }

    </style>
</head>
<body>
    <h1>Orari di Lavoro - Medici</h1>

    <?php
    try {
        $conn = new PDO(
            "mysql:host=127.0.0.1;dbname=databaseprogetto;charset=utf8",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $giorniSettimana = [
            'L'  => 'Lunedì',
            'M'  => 'Martedì',
            'Me' => 'Mercoledì',
            'G'  => 'Giovedì',
            'V'  => 'Venerdì',
            'S'  => 'Sabato',
            'D'  => 'Domenica'
        ];

        // ricavo i giorni dal db
        $sqlGiorni = "SELECT DISTINCT giorno FROM medico_orariolavoro";
        $resGiorni = $conn->query($sqlGiorni)->fetchAll(PDO::FETCH_COLUMN);

        // ordino i giorni
        usort($resGiorni, function($a, $b) use ($giorniSettimana) {
            return array_search($a, array_keys($giorniSettimana)) - array_search($b, array_keys($giorniSettimana));
        });

    } catch(PDOException $e) {
        echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
        exit;
    }
    ?>

    <form method="GET" action="">
        <label for="giorno">Seleziona giorno:</label>
        <select name="giorno">
            <option value="">Orario settimanale</option>
            <?php
            foreach($giorniSettimana as $codice => $nome) {
                if(in_array($codice, $resGiorni)) {
                    $selected = (isset($_GET['giorno']) && $_GET['giorno'] == $codice) ? " selected" : "";
                    echo "<option value='$codice'$selected>$nome</option>";
                }
            }
            ?>
        </select>
        <input type="submit" value="Filtra">
    </form>

    <?php
    // ricavo i medici + orari
    $giorniDaMostrare = [];
    if(isset($_GET['giorno']) && $_GET['giorno'] != "") {
        $giorniDaMostrare[] = $_GET['giorno'];
    } else {
        $giorniDaMostrare = $resGiorni; // tutti i giorni
    }

    foreach($giorniDaMostrare as $g) {
        echo "<h2>".$giorniSettimana[$g]."</h2>";

        // join per nome, cognome, codice, oraInizio, oraFine
        // la query restituisce tutti i medici che lavorano nel giorno s
        $sql = "SELECT me.codiceMedico, me.nome, me.cognome, o.oraInizio, o.oraFine
                FROM medico_orariolavoro mo
                JOIN MEDICO me ON mo.codiceMedico = me.codiceMedico
                JOIN ORARIOLAVORO o ON mo.giorno = o.giorno AND mo.oraInizio = o.oraInizio
                WHERE mo.giorno = :giorno
                ORDER BY o.oraInizio";

        $res = $conn->prepare($sql);
        $res->execute(['giorno' => $g]);
        $medici = $res->fetchAll(PDO::FETCH_ASSOC);

        // stampo tabella
        if(count($medici) == 0) {
            echo "<p>Nessun medico trovato per questo giorno.</p>";
        } else {
            echo "<table>
                    <tr>
                        <th>Codice</th>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Inizio turno</th>
                        <th>Fine turno</th>
                    </tr>";
            foreach($medici as $m) {
                echo "<tr>
                        <td>".$m['codiceMedico']."</td>
                        <td>".$m['nome']."</td>
                        <td>".$m['cognome']."</td>
                        <td>".$m['oraInizio'].":00</td>
                        <td>".$m['oraFine'].":00</td>
                      </tr>";
            }
            echo "</table>";
        }
    }
    ?>
</body>
</html>
