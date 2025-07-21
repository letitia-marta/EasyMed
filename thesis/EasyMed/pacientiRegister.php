<?php
/**
 * Pagină pentru înregistrarea pacienților noi
 * 
 * Această pagină gestionează înregistrarea pacienților în sistemul EasyMed:
 * - Afișează formularul de înregistrare pentru pacienți
 * - Validează datele introduse (email, parolă, CNP, etc.)
 * - Creează contul de utilizator cu parolă hash-uită
 * - Creează profilul de pacient asociat
 * - Trimite email de verificare cu cod de confirmare
 * - Gestionează erorile de validare și înregistrare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    include('db_connection.php');
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    // Procesează formularul de înregistrare
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // Extrage datele din formular
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        // Datele personale ale pacientului
        $cnp = $_POST['cnp'];
        $nume = $_POST['nume'];
        $prenume = $_POST['prenume'];
        $data_nasterii = $_POST['data_nasterii'];
        $sex = $_POST['sex'];

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
        $rol = "pacient";
        $stmt_user->bind_param("sss", $email, $hashed_password, $rol);

        if ($stmt_user->execute())
        {
            // Obține ID-ul utilizatorului creat
            $utilizator_id = $stmt_user->insert_id;

            // Creează profilul de pacient
            $sql_pacient = "INSERT INTO pacienti (utilizator_id, CNP, nume, prenume, data_nasterii, sex) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_pacient = $conn->prepare($sql_pacient);
            $stmt_pacient->bind_param("isssss", $utilizator_id, $cnp, $nume, $prenume, $data_nasterii, $sex);

            if ($stmt_pacient->execute())
            {
                // Generează codul de verificare și îl salvează în baza de date
                $verification_code = rand(100000, 999999);
                $update_sql = "UPDATE utilizatori SET verification_code = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $verification_code, $utilizator_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Configurează și trimite email-ul de verificare
                $mail = new PHPMailer(true);
                try {
                    // Configurarea serverului SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'formularcontact1@gmail.com';
                    $mail->Password   = 'aayg mocl ifyq bnsv';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Configurarea email-ului
                    $mail->setFrom('formularcontact1@gmail.com', 'EasyMed');
                    $mail->addAddress($email, $nume . ' ' . $prenume);
                    $mail->isHTML(true);
                    $mail->Subject = 'Cod de verificare EasyMed';
                    $mail->Body    = 'Codul dumneavoastră de verificare este: <b>' . $verification_code . '</b>';
                    $mail->send();
                } catch (Exception $e) {
                    echo "Mailer Error: {$mail->ErrorInfo}";
                }

                // Redirecționează către pagina de verificare email
                echo "<script>alert('Înregistrare reușită! Verificați emailul pentru codul de verificare.'); window.location.href = 'verify_email.php?email=" . urlencode($email) . "';</script>";
                exit();
            }
            else
            {
                // Eroare la crearea profilului de pacient
                echo "Eroare la înregistrarea pacientului: " . $stmt_pacient->error;
            }
        }
        else
        {
            // Eroare la crearea contului de utilizator
            echo "Eroare la înregistrarea utilizatorului: " . $stmt_user->error;
        }

        $stmt_user->close();
        $stmt_pacient->close();
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed - Înregistrare pentru pacienți</title>
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
            <div class="heroPacienti">
                <div class="wrapper">
                    <h1 class="hero-title">Înregistrare pentru pacienți</h1>
                    <p class="hero-subtitle">Creați un cont pentru a accesa platforma EasyMed.</p>
                    
                    <!-- Formularul de înregistrare -->
                    <div class="login-form">
                        <form action="pacientiRegister.php" method="POST">
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
                            
                            <!-- Câmpul pentru CNP -->
                            <div class="form-group">
                                <label for="cnp">CNP</label>
                                <input type="text" id="cnp" name="cnp" placeholder="Introduceți CNP-ul" required>
                            </div>
                            
                            <!-- Câmpul pentru data nașterii -->
                            <div class="form-group">
                                <label for="data_nasterii">Data nașterii</label>
                                <input type="date" id="data_nasterii" name="data_nasterii" required placeholder="zz-ll-aaaa">
                            </div>
                            
                            <!-- Câmpul pentru sex -->
                            <div class="form-group">
                                <label for="sex">Sex</label>
                                <select name="sex" id="sex" required>
                                    <option value="M">Masculin</option>
                                    <option value="F">Feminin</option>
                                </select>
                            </div>
                            
                            <!-- Butonul de înregistrare -->
                            <button type="submit">Înregistrează-te</button>
                        </form>
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

        <!-- Script-ul pentru validarea formularului -->
        <script>
            function validateForm() {
                var password = document.getElementById("password").value;
                var confirmPassword = document.getElementById("confirmPassword").value;
                var cnp = document.getElementById("CNP").value;
                var email = document.getElementById("email").value;
                var phone = document.getElementById("telefon").value;
                var name = document.getElementById("nume").value;
                var surname = document.getElementById("prenume").value;
                var birthDate = document.getElementById("data_nasterii").value;
                var address = document.getElementById("adresa").value;
                var sex = document.getElementById("sex").value;

                // Validează CNP-ul (13 cifre)
                if (cnp.length !== 13 || !/^\d+$/.test(cnp)) {
                    alert("CNP-ul trebuie să conțină exact 13 cifre!");
                    return false;
                }

                // Validează formatul email-ului
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert("Adresa de email nu este validă!");
                    return false;
                }

                if (phone.length !== 10 || !/^\d+$/.test(phone)) {
                    alert("Numărul de telefon trebuie să conțină exact 10 cifre!");
                    return false;
                }

                if (name.length < 2 || surname.length < 2) {
                    alert("Numele și prenumele trebuie să aibă cel puțin 2 caractere!");
                    return false;
                }

                if (!birthDate) {
                    alert("Data nașterii este obligatorie!");
                    return false;
                }

                if (address.length < 5) {
                    alert("Adresa trebuie să aibă cel puțin 5 caractere!");
                    return false;
                }

                if (sex === "") {
                    alert("Sexul este obligatoriu!");
                    return false;
                }

                if (password.length < 6) {
                    alert("Parola trebuie să aibă cel puțin 6 caractere!");
                    return false;
                }

                if (password !== confirmPassword) {
                    alert("Parolele nu coincid!");
                    return false;
                }

                return true;
            }

            function updateFromCNP() {
                const cnp = document.getElementById("cnp").value;
                const sexSelect = document.getElementById("sex");
                const birthDateInput = document.getElementById("data_nasterii");
                
                if (cnp.length > 0) {
                    const firstDigit = parseInt(cnp.charAt(0));
                    const sex = firstDigit % 2 === 0 ? "F" : "M";
                    sexSelect.value = sex;

                    if (cnp.length >= 7) {
                        const year = cnp.substring(1, 3);
                        const month = cnp.substring(3, 5);
                        const day = cnp.substring(5, 7);
                        
                        let fullYear;
                        if (firstDigit === 1 || firstDigit === 2) {
                            fullYear = "19" + year;
                        } else if (firstDigit === 3 || firstDigit === 4) {
                            fullYear = "18" + year;
                        } else if (firstDigit === 5 || firstDigit === 6) {
                            fullYear = "20" + year;
                        } else {
                            fullYear = "20" + year;
                        }

                        const formattedDate = `${fullYear}-${month}-${day}`;
                        birthDateInput.value = formattedDate;
                    }
                }
            }

            document.getElementById("cnp").addEventListener("input", updateFromCNP);
        </script>
    </body>
</html>