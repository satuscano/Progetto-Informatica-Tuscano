<html>
<head>
    <title>Orari Lavoro Medici</title>
    <style>
        h1 { color: #0077b6; }
        table { border-collapse: collapse; margin-bottom: 20px; width: 80%; }
        th, td { border: 0.5px solid black; padding: 5px 10px; }
        th { background-color: #bee1ff; }
        .giorno-intestazione {
            background-color: #79bffd;
            color: white;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>
<body style="background-color:#f0f8ff">
    <h1>Orari di Lavoro - Medici</h1>

    <?php
    try {
        $conn = new PDO(
        "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
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
        <select name="giorno" onchange="this.form.submit()">
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
    </form>

    <?php
    // Giorni da mostrare
    $giorniDaMostrare = isset($_GET['giorno']) && $_GET['giorno'] != "" ? [$_GET['giorno']] : $resGiorni;

    foreach($giorniDaMostrare as $g) {

        // Query medici per il giorno
        $sql = "SELECT me.codiceMedico, me.nome, me.cognome, o.oraInizio, o.oraFine
                FROM medico_orariolavoro mo
                JOIN MEDICO me ON mo.codiceMedico = me.codiceMedico
                JOIN ORARIOLAVORO o ON mo.giorno = o.giorno AND mo.oraInizio = o.oraInizio
                WHERE mo.giorno = :giorno
                ORDER BY o.oraInizio";

        $res = $conn->prepare($sql);
        $res->execute(['giorno' => $g]);
        $medici = $res->fetchAll(PDO::FETCH_ASSOC);

        if(count($medici) == 0) {
            echo "<p>Nessun medico trovato per questo giorno.</p>";
        } else {
            echo "<table>";
            // Riga intestazione giorno
            echo "<tr class='giorno-intestazione'>
                    <td colspan='5'>".$giorniSettimana[$g]."</td>
                  </tr>";
            // Riga intestazione colonne
            echo "<tr>
                    <th>Codice</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Inizio turno</th>
                    <th>Fine turno</th>
                  </tr>";

            foreach($medici as $m) {
                echo "<tr>
                        <td><a href='infoMedico.php?codice=".$m['codiceMedico']."' target='_blank'>".$m['codiceMedico']."</a></td>
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
