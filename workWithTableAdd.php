<?php
	include 'workWithTableInit.php';
	
	AddToTable($mysqli, $table, GetData($_POST, $columns, "Add"));
	ShowTable($mysqli, $table, $columns);
	include 'generateContent.php';
	
