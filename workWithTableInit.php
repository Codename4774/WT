<?php
	
	$serverAddr = $_POST["serverAddr"];
	$user = $_POST["user"];
	$password = $_POST["password"];
	$dataBase = $_POST["dataBase"];
	$table = $_POST["table"];

	include_once 'Libs.php';
	
	ini_set('display_errors','Off');
			
	$mysqli = new mysqli($serverAddr, $user, $password, $dataBase);
	if ($mysqli->connect_errno) 
	{
    		echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	else
	{	
		InitWorkWithDataBase($mysqli);
		$columns = GetColumns($mysqli, $table);
	}
	
	
