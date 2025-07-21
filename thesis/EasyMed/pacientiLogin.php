<?php
/**
 * Pagină de login pentru pacienți în aplicația EasyMed
 * 
 * Această pagină gestionează autentificarea pacienților:
 * - Afișează formularul de login pentru pacienți
 * - Validează credențialele de autentificare
 * - Verifică parola hash-uită în baza de date
 * - Creează sesiunea pentru pacientul autentificat
 * - Redirecționează către dashboard-ul pacienților
 * - Gestionează erorile de autentificare
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

include('db_connection.php');
session_start();

// Gestionează cererea POST pentru autentificare
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Extrage datele din formular
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Caută utilizatorul în baza de date după email
    $sql = "SELECT id, email, parola FROM utilizatori WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0)
    {
        $stmt->bind_result($id, $stored_email, $stored_password);
        $stmt->fetch();

        // Verifică dacă parola este corectă
        if (password_verify($password, $stored_password))
        {
            // Creează sesiunea pentru pacientul autentificat
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $stored_email;

            // Redirecționează către dashboard-ul pacienților
            header("Location: dashboardPacienti.php");
            exit();
        }
        else
        {
            echo "Parola incorectă!";
        }
    }
    else
    {
        echo "Email-ul nu este înregistrat!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed - Login pentru pacienți</title>
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    </head>

    <body>
        <section class="navigation">
            <a href="index.php">
                <img src="images/logo.png" width="70" alt="">
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
                            <img src="images/user.png" width="70" alt="Profile">
                        </a>
                    </li>
                </ul>
            </nav>
        </section>

        <section class="content">
            <div class="heroPacienti">
                <div class="wrapper">
                    <h1 class="hero-title">Login pentru pacienți</h1>
                    <p class="hero-subtitle">Accesați platforma pentru comunicarea cu medicii și aflarea rapidă a rezultatelor.</p>
                    <div class="login-form">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="exemplu@email.com" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Parolă</label>
                                <input type="password" id="password" name="password" placeholder="Introduceți parola" required>
                            </div>
                            <button type="submit">Conectați-vă</button>
                        </form>
                        <p class="login-helper">
                            Nu aveți încă un cont? <a href="pacientiRegister.php">Creeați-vă un cont</a>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="wrapper"></div>

        </section>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>
    </body>
</html>