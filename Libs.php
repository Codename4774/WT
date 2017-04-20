<?php
	ini_set('display_errors','Off');
	
	function GetResponce($mysqli, $table, $responce)
	{
		$result = $mysqli->query($responce);
		return $result;
	}
	
	function InitWorkWithDataBase($mysqli)
	{
		$mysqli->query("SET CHARACTER SET 'UTF8'");
		$mysqli->query("SET NAMES 'UTF8'"); 
	}
	
	function GetTableList($mysqli, $dataBase)
	{
		$result = $mysqli->query("SHOW TABLES");
		$tableList = array();
		
		while ($table = $result->fetch_assoc())
  		{
  			$tableName = $table["Tables_in_$dataBase"];
			array_push($tableList, $tableName);
			
  		}
  		return $tableList;	
	}
	
	function ShowTableList($tableList, $serverAddr, $user, $password, $dataBase)
	{
		echo "<table>";
		foreach($tableList as $table)
		{
			echo "<td>";
			echo "<form method=\"post\" action=\"workWithTableMain.php\">";
				echo "<input type=\"hidden\" name=\"serverAddr\" value=$serverAddr>";
				echo "<input type=\"hidden\" name=\"user\" value=$user>";
				echo "<input type=\"hidden\" name=\"password\" value=$password>";
				echo "<input type=\"hidden\" name=\"dataBase\" value=$dataBase>";
				echo "<input type=\"hidden\" name=\"table\" value=$table>";
				echo "<input type=submit value=$table>";
			echo "</form>";
			echo "</td>";
			
		}
		echo "</table>";
	}
	
	function GetColumns($mysqli, $table)
	{	
		$resultResponce = GetResponce($mysqli, $table, "SHOW FIELDS FROM $table");
		$columns = array();
		while ($row = $resultResponce->fetch_assoc())
  		{
  			$name_column = $row["Field"];
  			array_push($columns, $name_column);
  		}
  		return $columns;
	}
	
	function ShowTable($mysqli, $table, $columns)
	{

		echo "<table border=1><tr>";
		foreach($columns as $column)
		{
			echo "<td>$column</td>";
		}
		echo "</tr>";			
  		$resultResponce = GetResponce($mysqli, $table, "SELECT * FROM $table");
  		if ($resultResponce)
 		{
  			while ($row = $resultResponce->fetch_assoc())
  			{
  				echo "<tr>";
  				foreach($row as $data)
  				{
  					echo "<td>$data</td>";
  				}
  				echo "</tr>";
  			}	
 		}
  		echo "</table></tr>";
	}
	
	function AddDefaultParamsToForm($_Arr)
	{
		$serverAddr = $_Arr["serverAddr"];
		$user = $_Arr["user"];
		$password = $_Arr["password"];
		$dataBase = $_Arr["dataBase"];
		$table = $_Arr["table"];

		
		echo "<input type=\"hidden\" name=\"serverAddr\" value=$serverAddr>";
		echo "<input type=\"hidden\" name=\"user\" value=$user>";
		echo "<input type=\"hidden\" name=\"password\" value=$password>";
		echo "<input type=\"hidden\" name=\"dataBase\" value=$dataBase>";
		echo "<input type=\"hidden\" name=\"table\" value=$table>";
	}
		
	function ShowAddControls($columns)
	{
		echo "<form method=\"post\" action=\"workWithTableAdd.php\">";
		AddDefaultParamsToForm($_POST);

			foreach($columns as $column)
			{
				$controlNameAdd= $column . "Add";
 				echo "<input type=\"text\" name=$controlNameAdd value=$column>";
			}
			echo "<br>";
			echo "<input type=submit value=\"Add\">";
		echo "</form>";
	}
	
	function GetData($POST_Arr, $columns, $typeData)
	{
		$result = array();
		foreach($columns as $column)
		{
			$column .= $typeData;
			$res = "'";
			$res .= $POST_Arr[$column];
			$res .= "'";
			array_push($result, $res);
		}
		return $result;
	}
	
	function AddToTable($mysqli, $table, $data)
	{
		$responce = "INSERT INTO $table VALUES(";
		foreach($data as $value)
		{
			$responce = $responce . $value . ", ";
		}
		$responce = substr($responce, 0, strlen($responce) - strlen(", "));
		$responce .= ");";

		$resultResponce = GetResponce($mysqli, $table, $responce);
		if ($resultResponce)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function AddDroppedList($columns, $nameList)
	{
		$countColumns = count($columns);

		echo "<select name=\"$nameList\" size=\"1\">";
		foreach($columns as $column)
		{
			echo "<option>$column</option>";
		}
		echo "</select>";
		
	}
	
	function ShowDeleteControls($columns)
	{
	
		echo "<form method=\"post\" action=\"workWithTableDelete.php\">";
			AddDefaultParamsToForm($_POST);
			echo "delete where ";
		
			AddDroppedList($columns, "deletedCriteria");
			echo " equals ";
			echo "<input type=\"text\" name=\"deletedCriteriaValue\">";
			echo "<input type=\"submit\" value=\"delete\">";
		echo "</form>";
	}
	
	function DeleteFromTable($mysqli, $table, $deletedCriteria, $deletedCriteriaValue)
	{
		$resultResponce = GetResponce($mysqli, $table, "DELETE FROM $table WHERE $deletedCriteria = '$deletedCriteriaValue'");
		if ($resultResponce)
		{
			return true; 
		}
		else
		{
			return false;
		}
	}
	
	function ShowEditControls($columns)
	{
		echo "<form method=\"post\" action=\"workWithTableEdit.php\">";
			AddDefaultParamsToForm($_POST);

			echo "Set new values where ";
		
			AddDroppedList($columns, "editedCriteria");
			echo " equals ";
			echo "<input type=\"text\" name=\"editedCriteriaValue\">";
			
			echo "<br>";
			
			foreach($columns as $column)
			{
				$controlNameEdit = $column . "Edit";
 				echo "<input type=\"text\" name=$controlNameEdit value=$column>";
			}


			echo "<input type=\"submit\" value=\"Edit\">";
		echo "</form>";
	}
	
	function EditInTable($mysqli, $table, $columns, $data, $editedCriteria, $editedCriteriaValue)
	{
		$responce = "UPDATE $table SET ";
		$i = 0;
		foreach($data as $value)
		{
			$responce = $responce . $columns[$i++] . " = " . $value . ", ";
		}
		$responce = substr($responce, 0, strlen($responce) - strlen(", "));
		$responce .= " WHERE $editedCriteria = '$editedCriteriaValue';";

		$resultResponce = GetResponce($mysqli, $table, $responce);
		if ($resultResponce)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	

	
