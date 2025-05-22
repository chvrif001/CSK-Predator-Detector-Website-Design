<?php

class Database
{
	private $host = "dpg-d0evn2c9c44c738hdq4g-a.oregon-postgres.render.com";
	private $username = "cskpredatordetetor_db";
	private $password = "zk90UPRI8WZHEC1TVdNFtNm0aPOX12aP";
	private $db = "cskpredatordetetor_db";

	function connect()
	{
		$conn_string = "host=$this->host dbname=$this->db user=$this->username password=$this->password";
		$connection = pg_connect($conn_string);

		if (!$connection) {
			die("PostgreSQL connection failed.");
		}

		return $connection;
	}

	function read($query)
	{
		$conn = $this->connect();
		$result = pg_query($conn, $query);

		if (!$result) {
			return false;
		}

		$data = [];
		while ($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
		return $data;
	}

	function save($query)
	{
		$conn = $this->connect();
		$result = pg_query($conn, $query);
		return $result !== false;
	}
}
?>


