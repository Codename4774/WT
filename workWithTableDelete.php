<?php
	include 'workWithTableInit.php';
	
	if (DeleteFromTable($mysqli, $table, $_POST["deletedCriteria"], $_POST["deletedCriteriaValue"]))
	{
		ShowTable($mysqli, $table, $columns);
		include 'generateContent.php';
	}
	else
	{
		echo "ERROR WHILE DELETING";
	}
