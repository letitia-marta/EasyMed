<?php
    include('db_connection.php');
    session_start();

    if (!isset($_SESSION['user_id']))
    {
        header("Location: pacientiLogin.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $conn->real_escape_string($_POST['email']);

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE utilizatori SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("SELECT rol FROM utilizatori WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $role = $user['rol'];
            $stmt->close();

            if ($role === 'pacient') {
                $nume = $conn->real_escape_string($_POST['nume']);
                $prenume = $conn->real_escape_string($_POST['prenume']);
                $adresa = $conn->real_escape_string($_POST['adresa']);
                $grupa_sanguina = $conn->real_escape_string($_POST['grupa_sanguina']);
                $grupa_sanguina = str_replace([';', "'", '"'], '', $grupa_sanguina);

                $stmt = $conn->prepare("UPDATE pacienti SET nume = ?, prenume = ?, adresa = ?, grupa_sanguina = ? WHERE utilizator_id = ?");
                $stmt->bind_param("ssssi", $nume, $prenume, $adresa, $grupa_sanguina, $user_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($role === 'medic') {
                $nume = $conn->real_escape_string($_POST['nume']);
                $prenume = $conn->real_escape_string($_POST['prenume']);
                $specializare = $conn->real_escape_string($_POST['specializare']);

                $stmt = $conn->prepare("UPDATE medici SET nume = ?, prenume = ?, specializare = ? WHERE utilizator_id = ?");
                $stmt->bind_param("sssi", $nume, $prenume, $specializare, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $message = "Profilul a fost actualizat cu succes!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "A apărut o eroare la actualizarea profilului.";
            $messageType = "error";
        }
    }

    $sql_role = "SELECT rol FROM utilizatori WHERE id = ?";
    $stmt = $conn->prepare($sql_role);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    $sql1 = "SELECT email FROM utilizatori WHERE id = ?";
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if ($role === 'pacient')
    {
        $sql2 = "SELECT nume, prenume, CNP, sex, data_nasterii, adresa, grupa_sanguina
                 FROM pacienti WHERE utilizator_id = ?";
        $stmt = $conn->prepare($sql2);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($nume, $prenume, $CNP, $sex, $data_nasterii, $adresa, $grupa_sanguina);
        $stmt->fetch();
        $stmt->close();

        $birthdate = new DateTime($data_nasterii);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
    }
    elseif ($role === 'medic')
    {
        $sql2 = "SELECT nume, prenume, specializare, cod_parafa
                 FROM medici WHERE utilizator_id = ?";
        $stmt = $conn->prepare($sql2);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($nume, $prenume, $specializare, $cod_parafa);
        $stmt->fetch();
        $stmt->close();
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .profile-container {
                max-width: 800px;
                margin: 2rem auto;
                padding: 2rem;
                background: #13181d;
                border-radius: 10px;
                color: white;
            }

            .profile-header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .profile-form {
                display: grid;
                gap: 1rem;
            }

            .form-group {
                display: grid;
                gap: 0.5rem;
            }

            .form-group label {
                font-weight: bold;
            }

            .form-group input, .form-group select {
                padding: 0.5rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
                background: #2A363F;
                color: white;
            }

            .form-group input:disabled {
                background: #444;
                cursor: not-allowed;
            }

            .edit-button {
                background: #5cf9c8;
                color: black;
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                margin-top: 1rem;
            }

            .edit-button:hover {
                background: #4ad7a8;
            }

            .message {
                padding: 1rem;
                border-radius: 4px;
                margin-bottom: 1rem;
                text-align: center;
            }

            .message.success {
                background: #4ad7a8;
                color: black;
            }

            .message.error {
                background: #ff4444;
                color: white;
            }

            .non-editable {
                color: #888;
                font-style: italic;
            }

            .profile-dropdown {
                position: relative;
                display: inline-block;
            }

            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                top: 100%;
                background-color: #13181d;
                min-width: 160px;
                box-shadow: 0 8px 16px rgba(0,0,0,0.2);
                z-index: 1000;
                border-radius: 8px;
                margin-top: 10px;
            }

            .dropdown-menu.show {
                display: block;
            }

            .dropdown-item {
                color: white;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
                transition: background-color 0.3s;
            }

            .dropdown-item:hover {
                background-color: #2A363F;
            }

            .dropdown-item:first-child {
                border-radius: 8px 8px 0 0;
            }

            .dropdown-item:last-child {
                border-radius: 0 0 8px 8px;
            }
        </style>
    </head>

    <body>
        <section class="navigation">
            <a href="index.php">
                <img src="images/logo.png" width="70" alt="">
            </a>
            <h1><a href="index.php">EasyMed</a></h1>
            <nav aria-label="Main Navigation">
                <ul class="menu-opened test">
                    <li><a href="<?php echo $role === 'pacient' ? 'dashboardPacienti.php' : 'dashboardMedici.php'; ?>">Acasă</a></li>
                    <li class="profile-link" style="position: relative;">
                        <div class="profile-dropdown">
                            <img src="images/user.png" width="70" alt="Profil" id="profileIcon" style="cursor: pointer;">
                            <div class="dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    Profil
                                </a>
                                <a href="logout.php" class="dropdown-item">
                                    Deconectare
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </section>

        <div class="content">
            <div class="profile-container">
                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="profile-header">
                    <h2>Profilul meu</h2>
                </div>

                <?php if ($role === 'pacient'): ?>
                    <form class="profile-form" method="POST" action="">
                        <div class="form-group">
                            <label for="nume">Nume</label>
                            <input type="text" id="nume" name="nume" value="<?php echo htmlspecialchars($nume); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenume">Prenume</label>
                            <input type="text" id="prenume" name="prenume" value="<?php echo htmlspecialchars($prenume); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="cnp">CNP</label>
                            <input type="text" value="<?php echo htmlspecialchars($CNP); ?>" disabled>
                            <span class="non-editable">CNP-ul nu poate fi modificat</span>
                        </div>

                        <div class="form-group">
                            <label for="sex">Sex</label>
                            <input type="text" value="<?php echo $sex === 'M' ? 'Masculin' : 'Feminin'; ?>" disabled>
                            <span class="non-editable">Sexul nu poate fi modificat</span>
                        </div>

                        <div class="form-group">
                            <label for="data_nasterii">Data nașterii</label>
                            <input type="text" value="<?php echo htmlspecialchars($data_nasterii); ?>" disabled>
                            <span class="non-editable">Data nașterii nu poate fi modificată</span>
                        </div>

                        <div class="form-group">
                            <label for="varsta">Vârstă</label>
                            <input type="text" value="<?php echo $age; ?> ani" disabled>
                        </div>

                        <div class="form-group">
                            <label for="adresa">Adresă</label>
                            <input type="text" id="adresa" name="adresa" value="<?php echo htmlspecialchars($adresa); ?>">
                        </div>

                        <div class="form-group">
                            <label for="grupa_sanguina">Grupa sanguină</label>
                            <select id="grupa_sanguina" name="grupa_sanguina" required>
                                <?php
                                $grupe_sanguine = ['O(I)+', 'O(I)-', 'A(II)+', 'A(II)-', 'B(III)+', 'B(III)-', 'AB(IV)+', 'AB(IV)-'];
                                foreach ($grupe_sanguine as $grupa) {
                                    $selected = ($grupa === $grupa_sanguina) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($grupa) . "' $selected>" . htmlspecialchars($grupa) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" class="edit-button">Salvează modificările</button>
                    </form>
                <?php elseif ($role === 'medic'): ?>
                    <form class="profile-form" method="POST" action="">
                        <div class="form-group">
                            <label for="nume">Nume</label>
                            <input type="text" id="nume" name="nume" value="<?php echo htmlspecialchars($nume); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenume">Prenume</label>
                            <input type="text" id="prenume" name="prenume" value="<?php echo htmlspecialchars($prenume); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="specializare">Specializare</label>
                            <select id="specializare" name="specializare" required>
                                <?php
                                $specialties = [
                                    'Medic de familie', 'Cardiologie', 'Dermatologie', 'Pediatrie',
                                    'Ortopedie', 'Neurologie', 'Ginecologie', 'Psihiatrie',
                                    'Oftalmologie', 'Endocrinologie'
                                ];
                                foreach ($specialties as $specialty) {
                                    $selected = ($specialty === $specializare) ? 'selected' : '';
                                    echo "<option value='$specialty' $selected>$specialty</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cod_parafa">Cod Parafă</label>
                            <input type="text" value="<?php echo htmlspecialchars($cod_parafa); ?>" disabled>
                            <span class="non-editable">Codul parafă nu poate fi modificat</span>
                        </div>

                        <button type="submit" class="edit-button">Salvează modificările</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>

        <script>
            document.querySelector('.profile-form').addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Vă rugăm introduceți o adresă de email validă.');
                    return;
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const profileIcon = document.getElementById('profileIcon');
                const dropdownMenu = document.querySelector('.dropdown-menu');

                profileIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            });
        </script>
    </body>
</html>
