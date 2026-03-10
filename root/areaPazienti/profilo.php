<?php
include("../inc/start.inc");

$cf = $_SESSION['codiceFiscale'];

$stmt = $conn->prepare("SELECT *
                                FROM paziente
                                WHERE codiceFiscale = ?");
$stmt->bind_param("s", $cf);
$stmt->execute();
$result = $stmt->get_result();
$paziente = $result->fetch_assoc();

$success = ""; // Variabile per messaggi di successo o errore

if($_SERVER['REQUEST_METHOD'] === 'POST'){ // Richiesta tipo POST
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $dataNascita = $_POST['dataNascita'];
    $ind_cap = $_POST['ind_cap'];
    $ind_citta = $_POST['ind_citta'];
    $ind_via = $_POST['ind_via'];
    $ind_civico = $_POST['ind_civico'];

    // Aggiorno i dati del paziente
    $update = $conn->prepare("UPDATE paziente
                                        SET nome=?, cognome=?, dataNascita=?, ind_cap=?, ind_citta=?, ind_via=?, ind_civico=?
                                        WHERE codiceFiscale=?");
    $update->bind_param("ssssssss", $nome, $cognome, $dataNascita, $ind_cap, $ind_citta, $ind_via, $ind_civico, $cf);

    if($update->execute()){ // Se l'aggiornamento è andato a buon fine, aggiorno i dati nella variabile $paziente per riflettere le modifiche
        $paziente['nome'] = $nome;
        $paziente['cognome'] = $cognome;
        $paziente['dataNascita'] = $dataNascita;
        $paziente['ind_cap'] = $ind_cap;
        $paziente['ind_citta'] = $ind_citta;
        $paziente['ind_via'] = $ind_via;
        $paziente['ind_civico'] = $ind_civico;
    } else {
        $success = "Errore durante l'aggiornamento.";
    }
}
?>

<html>
    <head>
        <title>Profilo Paziente</title>
        <link rel="stylesheet" href="../CSS/theme.css">
    </head>
    <body>
        <!-- Richiamo il menu -->
        <?php require_once("../components/menuPaziente.php"); /* La require once serve a includere il menu solo una volta */ ?>
        <div id="overlay" onclick="toggleMenu()"></div> <!-- Overlay per chiudere il menu quando si clicca fuori -->
        <div id="mainContent"></div> <!-- Contenuto principale, usato per spostare tutto quando si apre il menu -->

        <header class="top-bar">
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <h1>Profilo di <?= $paziente['nome'] ?></h1>
        </header>

        <?php if($success): ?>
            <div class="error-msg"><?= $success ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="card">
                <h2>Dati personali</h2>
                <p><strong>Nome:</strong> <?= $paziente['nome'] ?></p>
                <p><strong>Cognome:</strong> <?= $paziente['cognome'] ?></p>
                <p><strong>Codice Fiscale:</strong> <?= $paziente['codiceFiscale'] ?></p>
                <p><strong>Data di nascita:</strong> <?= $paziente['dataNascita'] ?></p>
                <p><strong>Residente in:</strong> <?= $paziente['ind_via'] ?>, <?= $paziente['ind_civico'] ?>, <?= $paziente['ind_cap'] ?> <?= $paziente['ind_citta'] ?></p>
                <button onclick="toggleEdit('formAnagrafica')">Modifica</button>
            </div>

            <!-- Form di modifica dati personali -->
            <div id="formAnagrafica" class="edit-form hidden">
                <form method="POST" action="">
                    <p class="form-label">Nome:</p>
                    <input type="text" name="nome" value="<?= $paziente['nome'] ?>" required>
                    <br>

                    <p class="form-label">Cognome:</p>
                    <input type="text" name="cognome" value="<?= $paziente['cognome'] ?>" required>
                    <br>

                    <p class="form-label">Data di nascita:</p>
                    <input type="text" name="dataNascita" value="<?= $paziente['dataNascita'] ?>" required>
                    <br>

                    <p class="form-label">CAP:</p>
                    <input type="text" name="ind_cap" value="<?= $paziente['ind_cap'] ?>">
                    <br>

                    <p class="form-label">Città:</p>
                    <input type="text" name="ind_citta" value="<?= $paziente['ind_citta'] ?>">
                    <br>

                    <p class="form-label">Via:</p>
                    <input type="text" name="ind_via" value="<?= $paziente['ind_via'] ?>">
                    <br>

                    <p class="form-label">Civico:</p>
                    <input type="text" name="ind_civico" value="<?= $paziente['ind_civico'] ?>">
                    <br>

                    <button type="submit">Salva</button>
                </form>
            </div>
        </div>
        <script src="../js/menu.js" defer></script>
    </body>
</html>

<?php
    $conn->close();
?>