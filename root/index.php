<html>
    <head>
        <title>WELCOME - LOGIN</title>
        <link rel="stylesheet" href="CSS/theme.css">
    </head>
    <body class="login_page"> 
        <div class="wrapper">
            
        <div class="login-container">
            <h1 style="font-size: 30px;">AMBULATORIO A. TUSCANO</h1>
            <h2>Login</h2>

            <?php
            session_start();

            $msg = ""; // Variabile per messaggi di errore

            if($_SERVER['REQUEST_METHOD'] == 'POST'){ // Se il form è stato inviato tramite POST
                $codiceFiscale = $_POST['codiceFiscale']; // Prendo il codice fiscale inserito
                $password = $_POST['password']; // Prendo la password inserita

                $conn = new mysqli("localhost", "root", "", "databaseprogetto");
                if($conn->connect_error) die("Connessione fallita: ".$conn->connect_error);

                $stmt = $conn->prepare("SELECT ruolo, password
                                                FROM users
                                                WHERE codiceFiscale = ?");
                $stmt->bind_param("s", $codiceFiscale);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows == 1){ // Se esiste un utente con quel codice fiscale
                    $row = $result->fetch_assoc();
                    if(password_verify($password, $row['password'])){ // Verifico la password
                    // Setto le variabili di sessione    
                    $_SESSION['codiceFiscale'] = $codiceFiscale;
                        $_SESSION['ruolo'] = $row['ruolo'];
                        
                        // Reindirizzo alla dashboard in base al ruolo
                        switch ($_SESSION['ruolo']) {
                            case 'paziente':
                                header("Location: areaPazienti/dashboard.php");
                                break;
                            case 'medico':
                                header("Location: areaMedici/dashboard.php");
                                break;
                            case 'admin':
                                header("Location: areaAdmin/dashboard.php");
                                break;
                        }
                        exit;
                    } else {
                        $msg = "Password sbagliata!";
                    }
                } else {
                    $msg = "Utente non trovato!";
                }

                $conn->close();
            }
            ?>
            
            <!-- Stampo il messaggio di errore se necessario -->
            <?php if($msg != ""): ?>
                <div class="error-msg"><?php echo $msg; ?></div>
            <?php endif; ?>
            
            <!-- Form di login -->
            <form method="POST">
                <input type="text" name="codiceFiscale" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
        </div>
    </body>
</html>