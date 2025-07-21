<?php
/**
 * Dashboard pentru pacienți - pagina principală după autentificare
 * 
 * Această pagină oferă interfața principală pentru pacienții autentificați:
 * - Afișează meniul principal cu opțiunile disponibile pentru pacienți
 * - Include navigare către lista de medici disponibili
 * - Permite programarea de consultații
 * - Oferă acces la istoricul medical personal
 * - Include acces la relațiile pacient-medic
 * - Implementează dropdown pentru profil și deconectare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    session_start();
    require_once 'db_connection.php';

    // Verifică rolul utilizatorului din sesiune
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT rol FROM utilizatori WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $role = $user['rol'];
    }
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed pentru pacienți</title>
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
        <style>
            /* Stilizarea dropdown-ului pentru profil */
            .profile-dropdown {
                position: relative;
                display: inline-block;
            }

            /* Meniul dropdown pentru profil */
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

            /* Afișarea meniului dropdown când este activ */
            .dropdown-menu.show {
                display: block;
            }

            /* Stilizarea elementelor din dropdown */
            .dropdown-item {
                color: white;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
                transition: background-color 0.3s;
            }

            /* Efect hover pentru elementele dropdown */
            .dropdown-item:hover {
                background-color: #2A363F;
            }

            /* Rotunjirea colțurilor pentru primul element */
            .dropdown-item:first-child {
                border-radius: 8px 8px 0 0;
            }

            /* Rotunjirea colțurilor pentru ultimul element */
            .dropdown-item:last-child {
                border-radius: 0 0 8px 8px;
            }
        </style>
    </head>
    
    <body>
        <!-- Secțiunea de navigare principală -->
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
                    <li><a href="dashboardPacienti.php">Acasă</a></li>
                    <!-- Dropdown pentru profil utilizator -->
                    <li class="profile-link" style="position: relative;">
                        <div class="profile-dropdown">
                            <img src="images/user.png" width="70" alt="Profil pacient" id="profileIcon" style="cursor: pointer;">
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

        <!-- Conținutul principal al dashboard-ului -->
        <section class="content">
            <div class="heroPacienti">
                <div class="wrapper">
                    <h1 class="hero-title">EasyMed pentru pacienti</h1>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    
                    <!-- Grid-ul cu cardurile pentru funcționalități -->
                    <div class="cards">
                        <!-- Card pentru lista de medici -->
                        <div class="card" onclick="location.href='listaMedici.php';">
                            <h2 class="card-title">Medici</h2>
                            <span class="section-divider">── ⋆⋅☆⋅⋆ ──</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru programarea consultațiilor -->
                        <div class="card" onclick="location.href='programarePacienti.php';">
                            <h2 class="card-title">Programări</h2>
                            <span class="section-divider">───── ⋆⋅☆⋅⋆ ─────</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru istoricul medical -->
                        <div class="card" onclick="location.href='istoricPacient.php';">
                            <h2 class="card-title">Istoric medical</h2>
                            <span class="section-divider">───────── ⋆⋅☆⋅⋆ ─────────</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru relațiile pacient-medic -->
                        <div class="card" onclick="location.href='relatiiPacient.php';">
                            <h2 class="card-title">Relații Pacient</h2>
                            <span class="section-divider">───────── ⋆⋅☆⋅⋆ ─────────</span>
                            <p class="card-description"></p>
                        </div>
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

        <!-- Script-ul pentru funcționalitatea dropdown-ului -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Obține referințele la elementele dropdown
                const profileIcon = document.getElementById('profileIcon');
                const dropdownMenu = document.querySelector('.dropdown-menu');

                // Adaugă event listener pentru click pe iconița de profil
                profileIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Închide dropdown-ul când se face click în afara lui
                document.addEventListener('click', function(e) {
                    if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            });
        </script>
    </body>
</html>