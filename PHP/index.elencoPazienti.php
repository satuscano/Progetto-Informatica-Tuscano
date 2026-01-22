<html>
    <head>
        <title>Elenco Medici</title>
        <style>
            h1 { color: #0077b6; }
            table { border-collapse: collapse; margin-bottom: 20px; width: 80%; }
            th, td { border: 0.5px solid black; padding: 5px 10px; }
            th { background-color: #bee1ff; }
        </style>
    </head>

    <body style="background-color:#f0f8ff">
        <h1>Elenco Pazienti</h1>
        <?php
            try {
                $conn = new PDO(
                    "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
                    "root",
                    "",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $sql = "SELECT codiceFiscale, nome, cognome
                        FROM PAZIENTE";
                
                $sql .= " ORDER BY cognome, nome";
                $res = $conn->query($sql);
                $pazienti = $res->fetchAll(PDO::FETCH_ASSOC);

                if(count($pazienti) == 0) {
                    echo "<p style='color: red;'>Nessun paziente trovato</p>";
                } else {
                    echo "<table>
                        <tr>
                            <th>Codice Fiscale</th>
                            <th>Nome</th>
                            <th>Cognome</th>
                        </tr>";
                    foreach ($pazienti as $p) {
                        echo "<tr>
                            <td><a href='infoPaziente.php?codiceFiscale=".$p['codiceFiscale']."' target='_blank'>".$p['codiceFiscale']."</a></td>
                            <td>{$p['nome']}</td>
                            <td>{$p['cognome']}</td>
                        </tr>";
                    }
                    echo "</table>";
                }

            } catch (PDOException $e) {
                echo "<h2 style='color:red;'>Errore DB: {$e->getMessage()}</h2>";
                exit;
            }
        ?>
    </body>
</html>
