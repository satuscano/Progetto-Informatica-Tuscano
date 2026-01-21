<html>
<head>
    <title>Orari Lavoro Ambulatorio</title>
</head>
<body>
    <h1>Orari di Lavoro</h1>
    <form method="GET" action="#">
        <label for="giorno">Giorno:</label>
        <select name="giorno">
            <option value="">Tutti</option>
            <?php
            try {
                // nome del server - sempre "localhost"
                $servername = "localhost";
                // il nome del database che vuoi utilizzare
                $dbname = "databaseprogetto";
                // username per accedere al db (normalmente "root" - no password)
                $username = "root";
                $password = "";

                // creo un nuovo oggetto PDO (connessione)
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                // setto la modalità di errori del PDO come exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // conn.setAttribute()

                // Prendo i giorni disponibili dai medici
                $sqlGiorni = "SELECT DISTINCT giorno FROM medico_orariolavoro ORDER BY giorno";
                $resGiorni = $conn->query($sqlGiorni)->fetchAll(PDO::FETCH_ASSOC);

                foreach($resGiorni as $g) {
                    $selected = (isset($_GET["giorno"]) && $_GET["giorno"] == $g["giorno"]) ? " selected" : "";
                    echo "<option value='".$g["giorno"]."'$selected>".$g["giorno"]."</option>";
                }

                // Query principale: join con ORARIOLAVORO per avere oraFine
                $sql = "SELECT m.giorno, m.oraInizio, o.oraFine
                        FROM medico_orariolavoro m
                        JOIN ORARIOLAVORO o 
                          ON m.giorno = o.giorno AND m.oraInizio = o.oraInizio";

                if(isset($_GET["giorno"]) && $_GET["giorno"] != "") {
                    $giorno = $_GET["giorno"];
                    $sql .= " WHERE m.giorno = '".$giorno."'";
                }

                $results = $conn->query($sql);
                $nOrari = $results->rowCount();

                if($nOrari == 0)
                    echo "<h2>Nessun orario trovato</h2>";
                else if($nOrari == 1)
                    echo "<h2>Trovato un solo orario</h2>";
                else
                    echo "<h2>Trovati ".$nOrari." orari</h2>";

                if($nOrari > 0) {
                    echo "<table border='1'>
                            <tr>
                                <th>Giorno</th>
                                <th>Inizio turno</th>
                                <th>Fine turno</th>
                            </tr>";
                    while($row = $results->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>".$row["giorno"]."</td>
                                <td>".$row["oraInizio"]."</td>
                                <td>".$row["oraFine"]."</td>
                              </tr>";
                    }
                    echo "</table>";
                }

            } catch(PDOException $e) {
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
            ?>
        </select>
        <input type="submit" value="Invia"/>
    </form>
</body>
</html>
