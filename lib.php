<?php
error_reporting(E_ALL);
require_once "inc_private.php";

/*
This library encompasses all the database functions.  The system allows three
ways for accessing the data:
1. MySQL legacy functions
2. MySQL PDO
3. MySQL PDO with h2/quercus (for TNoodle compatibility)

H2 behaves differently with the casing of table names and column names. 
MySQL preserves the case of both table names and columns, whereas
h2 capitalizes all column names, and lowercases all table names.
These wrapper methods abtract away this difference.

See https://groups.google.com/forum/?fromgroups=#!topic/h2-database/YB25Esue7Rw for details.

*/

function cased_mysql_fetch_array(&$result) 
{
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		return mysql_fetch_array($result);
	$row = current($result);
	next($result);
	if (SQL_DBTYPE == DBTYPE_PDO)
		return $row;
	$new_row = array();
	foreach ($row as $key => $value) { 
		$key = strtolower($key);
		if($key == 'wcaid') {
			$key = 'WCAid';
		}
		$new_row[$key] = $value;
	}
	return $new_row;
}

function cased_mysql_result(&$result, $row, $field) 
{
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		return mysql_result($result, $row, $field);
	if (SQL_DBTYPE == DBTYPE_H2) 
		$field = strtoupper($field);
	return $result[$row][$field];
}

function strict_query($query, $array = null, $H = null) 
{
	if (SQL_DBTYPE == DBTYPE_MYSQL)
	{
		for ($x=0;$x<count($array);$x++)
			if (is_int($array[$x]))
				$query = preg_replace("/\?/", $array[$x],$query, 1);
			else
				$query = preg_replace("/\?/", "'".mysql_real_escape_string($array[$x])."'", $query, 1);
		$result = mysql_query($query);
		if (!$result)
			die('Invalid mysql_query query: ' . mysql_error());
		return $result;
	}
	else
	{
		/* On preparing the statament, H2 makes column name checking. Some of them come like 'name?' and it fails.
		 * This 'if' prevents this situation by replacing the '?' (in column names only!) previous to preparation.
		 */
		if (SQL_DBTYPE == DBTYPE_H2)
		{
			$narray = array();
			$offset = 0;
			$x = 0;
			while (($p = strpos($query,"?",$offset)) !== false)
			{
				if ($p > 0 && preg_match('/[a-z_]/',substr($query,$p-1,1)))
					$query = substr($query,0,$p) . $array[$x] . substr($query,$p+1);
				else
					$narray[count($narray)] = $array[$x];
				$offset = $p+1;
				$x++;
			}
			$array = $narray;
		}
		
		if (!$H)
		{
			global $DBH;
			$H = $DBH;
		}
		$sth = $H->prepare ($query);
		if (!$sth)
			die("Could not prepare statement<br>\n" .
				"errorCode: " . $H->errorCode () . "<br>\n" .
				"errorInfo: " . join (", ", $H->errorInfo ()));
		for ($x=0;$x<count($array);$x++)
			$sth->bindParam($x+1, $array[$x], (is_int($array[$x]) ? PDO::PARAM_INT : PDO::PARAM_STR));
		if (!$sth->execute ())
			die("Could not execute statement<br>\n" .
				"errorCode: " . $sth->errorCode () . "<br>\n" .
				"errorInfo: " . join (", ", $sth->errorInfo ()));
		return $sth->fetchAll();
	}
}

function sql_num_rows(&$result) 
{
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		return mysql_num_rows($result);
	else
		return count($result);
}

function sql_close()
{
	global $DBH;
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		mysql_close();
	else
		$DBH = null;	
}

function sql_insert_id($H = null)
{
	if (!$H)
	{
		global $DBH;
		$H = $DBH;
	}
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		return mysql_insert_id();
	else
		return (int)($H->lastInsertId());
}

function sql_data_reset(&$result)
{
	global $DBH;
	if (SQL_DBTYPE == DBTYPE_MYSQL)
		mysql_data_seek($result,0);
	else
		reset($result);
}

function refererMatchesHost()
{
	if (!isset($_SERVER['HTTP_REFERER']))
		return false;
	$referer = $_SERVER['HTTP_REFERER'];
	$host = preg_quote($_SERVER['HTTP_HOST']);
	return preg_match("+^(http://)?$host+", $referer);
}
?>
