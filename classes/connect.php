<?php

class Database
{
	private $host = "sql202.infinityfree.com"; //Change here if you want to connect to a different host//
	private $username = "if0_38862256" ; 
	private $password = "VLL72PR3Ipb4O";
	private $db = "if0_38862256_cskpredatordetector_db";


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



