<?
	function injection_check($str)
	{
		$origin = array("select","union","insert","update","delete","drop","alter","create","\"","\'","#","/*","*/","\\",";");

		for($i=0;$i<count($origin);$i++){
			if(strstr($str,$origin[$i]))
				return false;
		}
		return true;
	}

	function execute_db($con,$query){
		$server = "";	// Database host
		$dbname = "";	// Database name
		$dbid = "";	// Database user ID
		$dbpass = "";	// Database password

		$status = 0;
		$error = 0;
		if($con == null)
		$con = mysql_connect($server,$dbid,$dbpass);
		if(!$con)
		{
			die('Could not connect:'.mysql_error());
			$error = 1;
		}
		mysql_select_db($dbname,$con);
		$status = mysql_errno();
		if($status!=0) $error = 1;
		$result = mysql_query($query,$con);
		$status = mysql_errno();
		if($status!=0) $error = 1;
		if(strstr($query,"SELECT")!=false){
			$num = mysql_num_rows($result);
			$status = mysql_errno();
			if($status!=0)	$error = 1;
			if($error==0)	return array($con,$result,$num);
			$error=1;
		}
		if($error==0)	return array($con,$result);
		else		return false;
	}

	function close_db($con){
		mysql_close($con);
	}
?>
