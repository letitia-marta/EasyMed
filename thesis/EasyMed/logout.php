<?php
/**
 * Script pentru deconectarea utilizatorilor din sistem
 * 
 * Acest script gestionează procesul de logout:
 * - Șterge toate variabilele din sesiune
 * - Distruge sesiunea curentă
 * - Redirecționează utilizatorul către pagina principală
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

session_start();

// Șterge toate variabilele din sesiune
session_unset();

// Distruge sesiunea curentă
session_destroy();

// Redirecționează utilizatorul către pagina principală
header("Location: index.php");
exit();
?>
