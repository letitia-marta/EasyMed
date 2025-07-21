<?php
/**
 * Componenta de navigare pentru EasyMed
 * 
 * Această componentă oferă navigarea principală pentru aplicație:
 * - Gestionează sesiunea utilizatorului
 * - Afișează link-ul corect pentru dashboard în funcție de rol
 * - Include meniul dropdown pentru profil și deconectare
 * - Stilizează și funcționalizează meniul dropdown
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Inițializează sesiunea dacă nu este deja activă
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determină link-ul pentru dashboard în funcție de rolul utilizatorului
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$dashboard_link = $role === 'pacient' ? 'dashboardPacienti.php' : 'dashboardMedici.php';
?>

<!-- Secțiunea principală de navigare -->
<section class="navigation">
    <!-- Logo-ul aplicației -->
    <a href="index.php">
        <img src="images/logo.png" width="70" alt="Logo EasyMed">
    </a>
    <h1><a href="index.php">EasyMed</a></h1>
    
    <!-- Meniul de navigare -->
    <nav aria-label="Main Navigation">
        <ul class="menu-opened test">
            <!-- Link-ul pentru dashboard -->
            <li><a href="<?php echo $dashboard_link; ?>">Acasă</a></li>
            
            <!-- Meniul dropdown pentru utilizatorii autentificați -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="profile-link">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <img src="images/user.png" width="70" alt="Profil utilizator">
                        </a>
                        <div class="dropdown-menu">
                            <!-- Link către pagina de profil -->
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Profil
                            </a>
                            <!-- Link pentru deconectare -->
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Deconectare
                            </a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</section>

<!-- Stilurile CSS pentru meniul dropdown -->
<style>
    /* Container-ul pentru dropdown */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    /* Meniul dropdown */
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: #13181d;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        border-radius: 8px;
        padding: 0.5rem 0;
        z-index: 1000;
    }

    /* Clasa pentru afișarea meniului */
    .dropdown-menu.show {
        display: block;
    }

    /* Elementele din meniul dropdown */
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        color: white;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    /* Efectul hover pentru elementele din meniu */
    .dropdown-item:hover {
        background-color: #2A363F;
    }

    /* Stilizarea iconițelor */
    .dropdown-item i {
        width: 20px;
        text-align: center;
    }
</style>

<!-- Script-ul JavaScript pentru funcționalitatea dropdown -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (dropdownToggle && dropdownMenu) {
            // Adaugă event listener pentru deschiderea/închiderea meniului
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownMenu.classList.toggle('show');
            });

            // Închide meniul când se face click în afara lui
            document.addEventListener('click', function(e) {
                if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    });
</script> 