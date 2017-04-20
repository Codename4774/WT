<?php
	include 'workWithTableInit.php';
	
	if (EditInTable($mysqli, $table, $columns, GetData($_POST, $columns, "Edit"), $_POST["editedCriteria"], $_POST["editedCriteriaValue"]))
	{
		ShowTable($mysqli, $table, $columns);
		include 'generateContent.php';
	}
	else
	{
		echo "ERROR WHILE EDITING";
	}
