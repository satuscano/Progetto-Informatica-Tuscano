<html>
    <head>
        <title>Home Page</title>
    </head>
    <body style="background-color:#f0f8ff">
        <h1 style="color: #0077b6;">AMBULATORIO A. TUSCANO</h1>

        <?php
            try{
                $conn = new PDO(
                    "mysql:host=127.0.0.1;dbname=databaseprogetto2;charset=utf8",
                    "root",
                    "",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                echo "<p><i>Cliccare sull'opzione desiderata:</i></p>";

                echo '<a href="index.orariMedici.php" target="_blank">Orari dei medici</a><p></p>'; // orari medici
                echo '<a href="index.elencoMedici.php" target="_blank">Elenco dei medici</a><p></p>'; // elenco medici /ordine alfabetico)
                echo '<a href="index.elencoPazienti.php" target="_blank">Elenco dei pazienti</a><p></p>'; // elenco pazienti
                echo '<a href="index.elencoEsami.php" target="_blank">Elenco degli esami dei pazienti</a>'; // elenco esami (solo esami da eseguire)
            } catch(PDOException $e) {
                echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
                exit;
            }
        ?>
    </body>
</html>