<?php
/**
 * Dashboard pentru medici - pagina principală după autentificare
 * 
 * Această pagină oferă interfața principală pentru medicii autentificați:
 * - Afișează meniul principal cu opțiunile disponibile pentru medici
 * - Include navigare către gestionarea pacienților
 * - Permite programarea de consultații
 * - Oferă acces la vizualizarea programărilor
 * - Include acces la registrul de consultații
 * - Implementează dropdown pentru profil și deconectare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    include('db_connection.php');
    session_start();
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed pentru medici</title>
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
                    <li><a href="index.php">Acasă</a></li>
                    <!-- Dropdown pentru profil utilizator -->
                    <li class="profile-link" style="position: relative;">
                        <div class="profile-dropdown">
                            <img src="images/user.png" width="70" alt="Profil medic" id="profileIcon" style="cursor: pointer;">
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
            <div class="heroMedici">
                <div class="wrapper">
                    <h1 class="hero-title">EasyMed pentru medici</h1>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    
                    <!-- Grid-ul cu cardurile pentru funcționalități -->
                    <div class="cards">
                        <!-- Card pentru gestionarea pacienților -->
                        <div class="card" onclick="location.href='listaPacienti.php';">
                            <h2 class="card-title">Pacienți</h2>
                            <span class="section-divider">─── ⋆⋅☆⋅⋆ ───</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru programarea consultațiilor -->
                        <div class="card" onclick="location.href='programareMedici.php';">
                            <h2 class="card-title">Programează un pacient</h2>
                            <span class="section-divider">───── ⋆⋅☆⋅⋆ ─────</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru vizualizarea programărilor -->
                        <div class="card" onclick="location.href='vizualizareProgramari.php';">
                            <h2 class="card-title">Programări</h2>
                            <span class="section-divider">───── ⋆⋅☆⋅⋆ ─────</span>
                            <p class="card-description"></p>
                        </div>
                        
                        <!-- Card pentru registrul de consultații -->
                        <div class="card" onclick="location.href='registru.php';">
                            <h2 class="card-title">Registru consultații</h2>
                            <span class="section-divider">───────────── ⋆⋅☆⋅⋆ ─────────────</span>
                            <p class="card-description"></p>
                        </div>           
                    </div>
                </div>
            </div>
            
            <!-- Footer-ul paginii -->
            <footer>
                <div class="wrapper">
                    <p>EasyMed © 2024</p>
                </div>
            </footer>
        </section>

        <!-- Script-ul pentru funcționalitatea dropdown-ului -->
        <script>
            document.addEventListener('DOMContentLoaded', function()
            {
                // Obține referințele la elementele dropdown
                const profileIcon = document.getElementById('profileIcon');
                const dropdownMenu = document.querySelector('.dropdown-menu');

                if (profileIcon && dropdownMenu)
                {
                    // Adaugă event listener pentru click pe iconița de profil
                    profileIcon.addEventListener('click', function(e)
                    {
                        e.stopPropagation();
                        dropdownMenu.classList.toggle('show');
                    });

                    // Închide dropdown-ul când se face click în afara lui
                    document.addEventListener('click', function(e)
                    {
                        if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target))
                        {
                            dropdownMenu.classList.remove('show');
                        }
                    });
                }
            });
        </script>
    </body>
</html>