<html>
    <head>
    </head>
    <body>
        <h1>Orari di Lavoro</h1>
        <form method="GET" action="#">
            <label for="Giorno">Giorno:</label>
            <select name="Giorno">
            <option value="">Tutti</option>
            <?php
                try {
                    include("inc/connectionData.inc");
                    include("inc/startConnection.inc");

                    // query: seleziono tutti gli orari e li salvo
                    $sql = "SELECT * FROM orari";
                    $sqlGiorni = "SELECT DISTINCT Giorno FROM orari";
                    $resGiorni = $conn->query($sqlGiorni);
                    $resGiorni = $resGiorni->fetchAll(PDO::FETCH_ASSOC);

                    // serve per filtrare in base al giorno selezionato
                    if(isset($_GET["Giorno"])) {
                        $giorno = $_GET["Giorno"] != "< Tutti >";
                        $sql .= " WHERE Giorno = '".$giorno."'";
                    }
                    
                    // riempio la tendina dei giorni automaticamente
                    foreach($resGiorni as $g) {
                        echo "<option value='".$g["Giorno"]."'";
                        if(isset($_GET["Giorno"]) && $_GET["Giorno"] == $g["Giorno"])
                            echo " selected";
                        echo ">".$g["Giorno"]."</option>";
                    }

                    $results = $conn->query($sql);
                    $nOrari = $results->rowCount();
                    if($nOrari > 0)
                        echo "<h2>Nessun orario trovato</h2>";
                    else if($nOrari == 1)
                        echo "<h2>Trovato un solo orario</h2>";
                    else
                        echo "<h2>Trovati ".$results->rowCount()." orari</h2>";

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