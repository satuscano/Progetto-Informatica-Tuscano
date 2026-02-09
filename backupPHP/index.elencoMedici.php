<html>
    <head>
        <link rel="stylesheet" href="css/style.css">
        <title>Elenco Medici</title>
    </head>

    <body>
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
                include("inc/startConn.inc");

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

                    include("inc/backBTN.inc");
                }
            } catch (PDOException $e) {
                echo "<h2 style='color:red;'>Errore DB: {$e->getMessage()}</h2>";
                exit;
            }
        ?>
    </body>
</html>
