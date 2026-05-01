<?php
session_start();

// Verifica se l'utente è autenticato verificando se sono definite le variabili di sessione
if (isset($_SESSION['codiceFiscale']) && isset($_SESSION['password'])){ // Se il form è stato inviato tramite POST
                $codiceFiscale = $_SESSION['codiceFiscale']; // Prendo e salvo il codice fiscale inserito
                $password = $_SESSION['password']; // Prendo e salvo la password inserita

                $conn = new mysqli("localhost", "root", "", "databaseprogetto");
                if($conn->connect_error) die("Connessione fallita: ".$conn->connect_error);

                $stmt = $conn->prepare("SELECT *
                                        FROM users
                                        WHERE codiceFiscale = ?");
                $stmt->bind_param("s", $codiceFiscale);
                $stmt->execute();
                $result = $stmt->get_result(); // Eseguo la query e prendo il risultato

                if($result->num_rows == 1){ // Se esiste un utente con quel codice fiscale
                    $row = $result->fetch_assoc();
                    if(password_verify($password, $row['password'])){ // Verifico la password
                        $datiUtente=$row;
                        
                    } else {
                        $msg = "Password sbagliata!";
                    }
                } else {
                    $msg = "Utente non trovato!";
                }
                } else {
                    $msg="";
                }
?>
<?php
function requireRole($ruoloRichiesto) {
    if ($_SESSION['ruolo'] !== $ruoloRichiesto) {
        header("Location: /PROGETTO-INFO/root/index.php");
        exit;
    }
}
?>