<?php
	include 'workWithTableInit.php';
	include_once 'Libs.php';
	
	echo "<br>";
	ShowAddControls($columns);
	echo "<br>";
	ShowDeleteControls($columns);
	echo "<br>";
	ShowEditControls($columns);
