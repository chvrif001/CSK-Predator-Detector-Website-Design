<?php 

class Signup
{
	private $error = "";

	public function evaluate($data)
{
    foreach ($data as $key => $value) {

        if (empty($value)) {
            $this->error .= ucfirst($key) . " is empty! <br>";
        }

        if ($key == "email" && !empty($value)) {
            if (!preg_match("/^[\w\-\.]+@([\w-]+\.)+[\w-]{2,4}$/", $value)) {
                $this->error .= "Invalid email format!<br>";
            }
        }

        if ($key == "first_name" && !empty($value)) {
            if (is_numeric($value)) {
                $this->error .= "Invalid first name format!<br>";
            }
        }

        if ($key == "last_name" && !empty($value)) {
            if (is_numeric($value)) {
                $this->error .= "Invalid last name format!<br>";
            }
        }
    }

    // Passwords match check
    if ($data['password1'] !== $data['password2']) {
        $this->error .= "Passwords not identical!<br>";
    }

    // Final error handling
    if ($this->error == "") {
        $this->create_user($data);
    } else {
        return $this->error;
    }
}

	




	public function create_user($data)
	{

		$first_name = $data['first_name'];
		$last_name = $data['last_name'];
		$email = $data['email'];
		$password = $data['password1'];

		//create these
		$url_address = strtolower($first_name) . "." . strtolower($last_name);
		$userid = $this->create_userid();

		$query = "INSERT INTO cskpredatordetector_db 
		(userid, first_name, last_name, email, password, url_address) 
		VALUES 
		('$userid', '$first_name', '$last_name', '$email', '$password', '$url_address')";

		$DB = new Database();
		$DB->save($query);
	}



	private function create_userid()
	{
		$length = rand(4,19);
		$number = "";
		for ($i=0; $i< $length; $i++)
		{
			$new_rand = rand(0,9);
			$number = $number . $new_rand;
		}
		return $number;
	}
}