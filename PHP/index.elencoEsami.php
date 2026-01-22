<html>
    <head>
        <title>Elenco Esami & Storico</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f0f8ff; padding: 20px; }
            h1 { color: #0077b6; }
            table { border-collapse: collapse; width: 60%; margin-top: 20px; }
            th, td { border: 1px solid #555; padding: 8px 12px; }
            th { background-color: #9dd1ff; text-align: left; }
            td { background-color: #e6f2ff; }
            .checkbox { margin-top: 10px; }
        </style>
    </head>

    <body style="background-color:#f0f8ff">
        <h1>Elenco Esami & Storico</h1>

    <?php
        try {
            $conn = new PDO(
                "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
            exit;
        }

        $soloDaFare = isset($_GET['daFare']) && $_GET['daFare'] == "1";

        $oggi = date('Y-m-d');
    ?>

    <form method="GET" action="">
        <label class="checkbox">
            <input type="checkbox" name="daFare" value="1" onchange="this.form.submit()" <?= $soloDaFare ? "checked" : "" ?>>
            Mostra solo esami da fare
        </label>
    </form>

    <?php
        $sql = "SELECT codiceEsame, data, oraInizio 
                FROM STORICO";

        if($soloDaFare) {
            $sql .= " WHERE data >= :oggi";
        }

        $sql .= " ORDER BY data ASC, oraInizio ASC";

        $stmt = $conn->prepare($sql);

        if($soloDaFare) {
            $stmt->execute(['oggi' => $oggi]);
        } else {
            $stmt->execute();
        }

        $esami = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php
        if(count($esami) == 0) {
            echo "<p>Nessun esame trovato.</p>";
        } else {
            echo "<table>
                    <tr>
                        <th>Codice Esame</th>
                        <th>Data</th>
                        <th>Ora Inizio</th>
                    </tr>";
            foreach($esami as $e) {
                echo "<tr>
                        <td><a href='infoEsame.php?codiceEsame=" . $e['codiceEsame'] . "' target='_blank'>" . $e['codiceEsame'] . "</a></td>
                        <td>" . $e['data'] . "</td>
                        <td>" . $e['oraInizio'] . ":00</td>
                    </tr>";
            }
            echo "</table>";
        }
    ?>

    </body>
</html>