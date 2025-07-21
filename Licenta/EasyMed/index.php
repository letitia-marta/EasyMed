<?php
/**
 * Pagina principală EasyMed
 * 
 * Această pagină servește ca landing page pentru platforma EasyMed:
 * - Prezintă platforma utilizatorilor
 * - Oferă linkuri către secțiunile pentru medici și pacienți
 * - Include navigarea principală și footer-ul
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
?>
<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
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
                    <li class="profile-link">
                        <a href="profile.php">
                            <img src="images/user.png" width="70" alt="Profil utilizator">
                        </a>
                    </li>
                </ul>
            </nav>
        </section>

        <!-- Conținutul principal al paginii -->
        <section class="content">
            <div class="hero">
                <div class="wrapper">
                    <!-- Titlul principal și subtitlul -->
                    <h1 class="hero-title">Bine ați venit pe EasyMed!</h1>
                    <p class="hero-subtitle">Noua platformă medicală care conectează</p>
                    <p class="hero-subtitle">pacienții și medicii pentru o experiență</p>
                    <p class="hero-subtitle">modernă și eficientă în gestionarea sănătății.</p>
                    
                    <!-- Cardurile pentru medici și pacienți -->
                    <div class="cards">
                        <!-- Card pentru medici -->
                        <div class="card" style="min-width:260px;max-width:340px;" onclick="location.href='medici.php';">
                            <h2 class="card-title">Pentru medici</h2>
                            <span class="section-divider">─────────── ⋆⋅☆⋅⋆ ───────────</span>
                            <p class="card-description">Oferim o interfață pentru medici, care permite gestionarea pacienților, programarea consultațiilor și acces rapid la registrul digital de consultații.</p>
                        </div>
                        
                        <!-- Card pentru pacienți -->
                        <div class="card" style="min-width:260px;max-width:340px;" onclick="location.href='pacienti.php';">
                            <h2 class="card-title">Pentru pacienți</h2>
                            <span class="section-divider">─────────── ⋆⋅☆⋅⋆ ───────────</span>
                            <p class="card-description">Pacienții pot accesa rapid informațiile personale, programa consultații și vizualiza istoricul medical.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer-ul paginii -->
        <div class="wrapper">
            <p>EasyMed © 2024</p>
        </div>
    </body>
</html>