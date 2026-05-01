<html>
    <head>
        <title>Login</title>
        <?php include ('../inc/header.inc'); ?>
    </head>
    <body>
        <?php include ('../inc/auth.inc'); ?>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h2 class="text-center mt-5">Login</h2>
                    <form action="../dashboardsrc/dist/dashboard.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="codiceFiscale" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
        
        <?php 
            if($_SERVER['REQUEST_METHOD'] == 'POST'){ // Se il form è stato inviato tramite POST
                $codiceFiscale = $_POST['codiceFiscale']; // Prendo e salvo il codice fiscale inserito
                $password = $_POST['password']; // Prendo e salvo la password inserita

                $stmt = $conn->prepare("SELECT ruolo, password
                                        FROM users
                                        WHERE codiceFiscale = ?");
                $stmt->bind_param("s", $codiceFiscale);
                $stmt->execute();
                $result = $stmt->get_result(); // Eseguo la query e prendo il risultato

                if($result->num_rows == 1){ // Se esiste un utente con quel codice fiscale
                    $row = $result->fetch_assoc();
                    if(password_verify($password, $row['password'])){ // Verifico la password
                    // Setto le variabili di sessione    
                        $_SESSION['codiceFiscale'] = $codiceFiscale;
                        $_SESSION['ruolo'] = $row['ruolo'];
                        header("Location ../dashboardsrc/dist/dashboard.php"); // Reindirizzo alla dashboard
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

        
    </body>
</html>