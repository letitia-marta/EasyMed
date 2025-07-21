<?php
/**
 * Script pentru verificarea adreselor de email
 * 
 * Acest script gestionează verificarea adreselor de email pentru utilizatori:
 * - Validează codul de verificare trimis pe email
 * - Actualizează statusul de verificare în baza de date
 * - Permite utilizatorilor să se autentifice după verificare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
include('db_connection.php');

// Procesează formularul de verificare email
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extrage datele din formular
    $email = $_POST['email'];
    $code = $_POST['code'];

    // Verifică dacă codul de verificare este valid pentru email-ul dat
    $sql = "SELECT id FROM utilizatori WHERE email = ? AND verification_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Codul este valid - obține ID-ul utilizatorului
        $stmt->bind_result($user_id);
        $stmt->fetch();
        
        // Marchează email-ul ca fiind verificat și șterge codul de verificare
        $update = $conn->prepare("UPDATE utilizatori SET email_verified = 1, verification_code = NULL WHERE id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();
        $update->close();
        
        $success = "Email verificat cu succes! Acum vă puteți autentifica.";
    } else {
        // Codul este invalid
        $error = "Cod invalid sau email greșit!";
    }
    $stmt->close();
    $conn->close();
}

// Extrage parametrul email din URL pentru pre-completarea formularului
$email_param = isset($_GET['email']) ? $_GET['email'] : '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificare Email - EasyMed</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Secțiunea de navigare -->
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

    <!-- Conținutul principal -->
    <section class="content">
        <div class="heroPacienti">
            <div class="wrapper">
                <h1 class="hero-title">Verificare Email</h1>
                <p class="hero-subtitle">Introduceți codul de verificare trimis pe email.</p>
                
                <!-- Mesaj de succes -->
                <?php if (isset($success)): ?>
                    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="pacientiLogin.php" style="color: #155724; text-decoration: underline;">Accesați pagina de autentificare</a>
                    </div>
                <?php endif; ?>
                
                <!-- Mesaj de eroare -->
                <?php if (isset($error)): ?>
                    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formularul de verificare -->
                <div class="login-form">
                    <form method="POST">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_param); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="code">Cod de verificare:</label>
                            <input type="text" id="code" name="code" placeholder="Introduceți codul de 6 cifre" required>
                        </div>
                        <button type="submit">Verifică Email</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="wrapper">
            <p>EasyMed © 2024</p>
        </div>
    </footer>
</body>
</html> 