<?php
/**
 * Pagină pentru înregistrarea medicilor noi
 * 
 * Această pagină gestionează înregistrarea medicilor în sistemul EasyMed:
 * - Afișează formularul de înregistrare pentru medici
 * - Validează datele introduse (email, parolă, cod parafă, specializare)
 * - Creează contul de utilizator cu parolă hash-uită
 * - Creează profilul de medic asociat cu specializarea
 * - Gestionează erorile de validare și înregistrare
 * - Redirecționează către pagina de autentificare după succes
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    include('db_connection.php');

    // Procesează formularul de înregistrare
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // Extrage datele din formular
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        // Datele personale și profesionale ale medicului
        $nume = $_POST['nume'];
        $prenume = $_POST['prenume'];
        $cod_parafa = $_POST['cod_parafa'];
        $specializare = $_POST['specializare'];

        // Validează că parolele se potrivesc
        if ($password != $confirm_password)
        {
            echo "Parolele nu se potrivesc!";
            exit();
        }

        // Hash-uiește parola pentru securitate
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Creează contul de utilizator
        $sql_user = "INSERT INTO utilizatori (email, parola, rol) VALUES (?, ?, ?)";
        $stmt_user = $conn->prepare($sql_user);
        $rol = "medic";
        $stmt_user->bind_param("sss", $email, $hashed_password, $rol);

        if ($stmt_user->execute())
        {
            // Obține ID-ul utilizatorului creat
            $utilizator_id = $stmt_user->insert_id;

            // Creează profilul de medic
            $sql_medic = "INSERT INTO medici (utilizator_id, nume, prenume, cod_parafa, specializare) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmt_medic = $conn->prepare($sql_medic);
            $stmt_medic->bind_param("issss", $utilizator_id, $nume, $prenume, $cod_parafa, $specializare);

            if ($stmt_medic->execute())
            {
                // Înregistrare reușită - redirecționează către autentificare
                echo "Înregistrare reușită!";
                header("Location: mediciLogin.php");
                exit();
            }
            else
            {
                // Eroare la crearea profilului de medic
                echo "Eroare la înregistrarea medicului: " . $stmt_medic->error;
            }
        }
        else
        {
            // Eroare la crearea contului de utilizator
            echo "Eroare la înregistrarea utilizatorului: " . $stmt_user->error;
        }

        $stmt_user->close();
        $stmt_medic->close();
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed - Înregistrare pentru medici</title>
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    </head>

    <body>
        <!-- Secțiunea de navigare -->
        <section class="navigation">
            <a href="index.php">
                <img src="images/logo.png" width="70" alt="Logo EasyMed">
            </a>
            <h1><a href="index.php">EasyMed</a></h1>
            <nav aria-label="Main Navigation">
                <ul class="menu-hamburger hidden">
                    <li>Meniu</li>
                </ul>
                <ul class="menu-opened test">
                    <li><a href="index.php">Acasă</a></li>
                    <li class="profile-link">
                        <a href="profile.php">
                            <img src="images/user.png" width="70" alt="Profil utilizator">
                        </a>
                    </li>
                </ul>
            </nav>
        </section>

        <!-- Conținutul principal -->
        <section class="content">
            <div class="heroMedici">
                <div class="wrapper">
                    <h1 class="hero-title">Înregistrare pentru medici</h1>
                    <p class="hero-subtitle">Creați un cont pentru a accesa platforma EasyMed.</p>
                    
                    <!-- Formularul de înregistrare -->
                    <div class="login-form">
                        <form action="mediciRegister.php" method="POST">
                            <!-- Câmpul pentru nume -->
                            <div class="form-group">
                                <label for="nume">Nume</label>
                                <input type="text" id="nume" name="nume" placeholder="Introduceți numele" required>
                            </div>
                            
                            <!-- Câmpul pentru prenume -->
                            <div class="form-group">
                                <label for="prenume">Prenume</label>
                                <input type="text" id="prenume" name="prenume" placeholder="Introduceți prenumele" required>
                            </div>
                            
                            <!-- Câmpul pentru codul parafă -->
                            <div class="form-group">
                                <label for="cod_parafa">Cod parafă</label>
                                <input type="text" id="cod_parafa" name="cod_parafa" placeholder="Introduceți codul parafă" required>
                            </div>
                            
                            <!-- Câmpul pentru specializare -->
                            <div class="form-group">
                                <label for="specializare">Specializare</label>
                                <select id="specializare" name="specializare" required>
                                    <option value="">-- Selectați specializarea --</option>
                                    <option value="Medic de familie">Medic de familie</option>
                                    <option value="Cardiologie">Cardiologie</option>
                                    <option value="Dermatologie">Dermatologie</option>
                                    <option value="Pediatrie">Pediatrie</option>
                                    <option value="Ortopedie">Ortopedie</option>
                                    <option value="Neurologie">Neurologie</option>
                                    <option value="Ginecologie">Ginecologie</option>
                                    <option value="Psihiatrie">Psihiatrie</option>
                                    <option value="Oftalmologie">Oftalmologie</option>
                                    <option value="Endocrinologie">Endocrinologie</option>
                                </select>
                            </div>
                            
                            <!-- Câmpul pentru email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="exemplu@email.com" required>
                            </div>
                            
                            <!-- Câmpul pentru parolă -->
                            <div class="form-group">
                                <label for="password">Parolă</label>
                                <input type="password" id="password" name="password" placeholder="Introduceți parola" required>
                            </div>
                            
                            <!-- Câmpul pentru confirmarea parolei -->
                            <div class="form-group">
                                <label for="confirm-password">Confirmare parolă</label>
                                <input type="password" id="confirm-password" name="confirm-password" placeholder="Reintroduceți parola" required>
                            </div>
                            
                            <!-- Butonul de înregistrare -->
                            <button type="submit">Înregistrați-vă</button>
                        </form>
                        
                        <!-- Link către pagina de autentificare -->
                        <p class="login-helper">
                            Aveți deja un cont? <a href="mediciLogin.php">Conectați-vă</a>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer-ul paginii -->
        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>
    </body>
</html>