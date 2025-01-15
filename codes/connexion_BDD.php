<?php
   session_start();

   try
   {
      $bdd = new PDO('mysql:host=localhost;dbname=empoct_app_medecin;charset=utf8', 'root', 'YES');
   }
   catch(Exception $e)
   {
           die('Erreur : '.$e->getMessage());
   }


 ?>