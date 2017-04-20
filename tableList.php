<?php
	ini_set('display_errors','Off');
	
	$serverAddr = $_POST["serverAddr"];
	$user = $_POST["user"];
	$password = $_POST["password"];
	$dataBase = $_POST["dataBase"];

	include_once 'Libs.php';
			
	$mysqli = new mysqli($serverAddr, $user, $password, $dataBase);
	if ($mysqli->connect_errno) 
	{
    		echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	else
	{	
		InitWorkWithDataBase($mysqli);
		$tableList = GetTableList($mysqli, $dataBase);
		ShowTableList($tableList, $serverAddr, $user, $password, $dataBase);

	}
	

