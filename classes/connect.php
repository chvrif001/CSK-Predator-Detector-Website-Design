<?php

class Database
{
	private $host = "dpg-d0evn2c9c44c738hdq4g-a"; //Change here if you want to connect to a different host//
	private $username = "cskpredatordetetor_db_user" ; 
	private $password = "zk90UPRI8WZHEC1TVdNFtNm0aPOX12aP";
	private $db = "cskpredatordetetor_db";


	function connect()
	{
		$connection = mysqli_connect($this->host, $this->username, $this->password, $this->db);
		return $connection;
	}


	function read($query)
	{
		$conn = $this->connect();
		$result = mysqli_query($conn,$query);

		if (!$result)
		{
			return false;
		}
		else
		{
			$data = false;
			while( $row = mysqli_fetch_array($result))
			{
				$data[] = $row;
				
			}
			return $data;
		}

	}


	function save($query)
	{
		$conn = $this->connect();
		$result = mysqli_query($conn,$query);

		if (!$result)
		{return false;}
		else{return true;}
	}
}



