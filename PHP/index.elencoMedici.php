<html>
    <head>
        <title>Elenco Medici</title>
        <style>
            h1 { color: #0077b6; }
            table { border-collapse: collapse; margin-bottom: 20px; width: 80%; }
            th, td { border: 0.5px solid black; padding: 5px 10px; }
            th { background-color: #bee1ff; }
            .primario {
                font-weight: bold;
                color: darkblue;
            }
        </style>
    </head>

    <body style="background-color:#f0f8ff">
        <h1>Elenco Medici Ambulatorio</h1>

        <form method="get">
            <label>
                Mostra solo primari di reparto
                <input type="checkbox" name="soloPrimari" value="1"
                    onchange="this.form.submit()"
                    <?php if (isset($_GET['soloPrimari'])) echo "checked"; ?>>
            </label>
        </form>

        <?php
            try {
                $conn = new PDO(
                    "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
                    "root",
                    "",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $soloPrimari = isset($_GET['soloPrimari']);

                $sql = "SELECT codiceMedico, nome, cognome, primario
                        FROM MEDICO";

                if ($soloPrimari) {
                    $sql .= " WHERE primario = 1";
                }

                $sql .= " ORDER BY cognome, nome";
                $res = $conn->query($sql);
                $medici = $res->fetchAll(PDO::FETCH_ASSOC);

                if (count($medici) == 0) {
                    echo "<p style='color: red;'>Nessun medico trovato.</p>";
                } else {
                    echo "<table>
                            <tr>
                                <th>Codice</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                            </tr>";
                    foreach ($medici as $m) {
                        $classe = ($m['primario']) ? "class='primario'" : "";

                        echo "<tr $classe>
                                <td><a href='infoMedico.php?codice=".$m['codiceMedico']."' target='_blank'>".$m['codiceMedico']."</a></td>
                                <td>{$m['nome']}</td>
                                <td>{$m['cognome']}</td>
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
