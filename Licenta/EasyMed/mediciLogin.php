<?php
/**
 * Pagină de login pentru medici
 * 
 * Acest script gestionează autentificarea medicilor:
 * - Include conexiunea la baza de date
 * - Validează credențialele de login
 * - Verifică dacă utilizatorul are rolul de medic
 * - Creează sesiunea pentru medicul autentificat
 * - Redirecționează către dashboard-ul medicilor
 */

include('db_connection.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, email, parola, rol FROM utilizatori WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0)
    {
        $stmt->bind_result($id, $stored_email, $stored_password, $role);
        $stmt->fetch();

        if ($role !== "medic") {
            echo "Acces permis doar medicilor!";
        }
        elseif (password_verify($password, $stored_password))
        {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $stored_email;

            header("Location: dashboardMedici.php");
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
    <title>EasyMed - Login pentru medici</title>
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
        <div class="heroMedici">
            <div class="wrapper">
                <h1 class="hero-title">Login pentru medici</h1>
                <p class="hero-subtitle">Accesați platforma pentru gestionarea pacienților și consultațiilor.</p>
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
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrapper">
            <p>EasyMed © 2024</p>
        </div>
    </footer>
</body>
</html>