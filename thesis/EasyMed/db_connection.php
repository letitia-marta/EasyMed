<?php
/**
 * Fișier pentru conexiunea la baza de date
 * 
 * Acest fișier stabilește conexiunea cu baza de date MySQL:
 * - Configurează parametrii de conexiune
 * - Creează obiectul de conexiune mysqli
 * - Verifică dacă conexiunea a fost stabilită cu succes
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "easymed";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}