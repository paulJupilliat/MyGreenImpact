<?php

$host = 'macbook-air-de-paul.local'; // Hôte
$dbname = 'my_green_impact_db'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur (par défaut dans XAMPP)
$password = ''; // Mot de passe (vide par défaut dans XAMPP)
$conn=new mysqli($host,$username,$password,$dbname);
if($conn->connect_error){
    echo "Failed to connect DB".$conn->connect_error;
}
?>