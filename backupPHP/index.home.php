<html>
    <head>
        <link rel="stylesheet" href="css/style.css">
        <title>🏠Home Page</title>
    </head>
    <body>
        <?php
            try{
                include("inc/startConn.inc");              

                echo '<h1>AMBULATORIO A. TUSCANO</h1>

                <div class="menu">
                    <a class="card" href="index.elencoMedici.php">
                        <h2>Medici</h2>
                        <p>Visualizza l elenco dei medici</p>
                    </a>

                    <a class="card" href="index.elencoPazienti.php">
                        <h2>Pazienti</h2>
                        <p>Gestione pazienti e dati anagrafici</p>
                    </a>

                    <a class="card" href="index.orariMedici.php">
                        <h2>Orari</h2>
                        <p>Orari di lavoro dei medici</p>
                    </a>

                    <a class="card" href="index.elencoEsami.php">
                        <h2>Esami</h2>
                        <p>Esami e storico clinico</p>
                    </a>
                </div>';
            } catch(PDOException $e) {
                echo "<h2 style='color:red;'>Errore DB: ".$e->getMessage()."</h2>";
                exit;
            }
        ?>
    </body>
</html>