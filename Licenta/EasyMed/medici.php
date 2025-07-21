<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EasyMed - pentru medici</title>
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
                    <h1 class="hero-title">EasyMed pentru medici</h1>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    <p class="hero-subtitle"></p>
                    <div class="cards">
                        <div class="card" onclick="location.href='mediciLogin.php';">
                            <h2 class="card-title">Conectați-vă</h2>
                            <span class="section-divider">─────── ⋆⋅☆⋅⋆ ───────</span>
                            <p class="card-description"></p>
                        </div>        
                    </div>
                </div>
            </div>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </section>
    </body>
</html>