<?php
$configs = include('config.php');

$server =  $configs->{'db_server'};
$database = $configs->{'db_database'};
$user = $configs->{'db_username'};
$pass = $configs->{'db_pass'};

$connection = new mysqli($server, $user, $pass, $database);


 return $connection;

