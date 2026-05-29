<?php
   $dbhost = 'db'; // Nombre del servicio de la base de datos en docker-compose
   $dbuser = 'root' ;
   $dbpass = 'root_password_segura' ;
   $dbname = 'optica_db';
   // Creamos la instancia usando tu clase personalizada
   $db = new db($dbhost, $dbuser, $dbpass, $dbname);
?>