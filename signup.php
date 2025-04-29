<?php

	include("classes/connect.php");
	include("classes/signup.php");

	$first_name = "";
	$last_name = "";
	$email = "";

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    	$signup = new Signup();
    	$result = $signup->evaluate($_POST);

    	if ($result != "") {
    		echo "<div style='text-align: center; font-size:12px; color: white; background-color: grey;'>";
    		echo "The following errors occurred<br><br>";
        	echo $result;	
        	echo "</div>";
    	}
    	else
    	{
    		header("Location: login.php");
    		die;
    	}

    	$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];
	}

?>



<html> 


	<head> 
		<title>PredatorDetector | Sign up</title>
	</head>

	<style>
		#bar{
			height: 100px; 
			background-color: green; 
			color: white; 
			padding: 4px;
		}
		
		#signup_button{
				background-color: black;
				width: 70px;
				text-align: centre;
				padding: 4px;
				border-radius: 4px;
				float: right;
		}
		#bar2{
			background-color: white; 
			width: 800px; 
			height: 400px; 
			margin: auto; 
			margin-top: 60px;
			padding: 10px;
			padding-top: 60px;
			text-align: center;
			font-weight: bold;

		}

		#text{
			height:40px;
			width: 300px;
			border-radius: 4px;
			padding: 4px;
			font-size: 15px;
		}

		#button{
			height: 30px;
			width:200px;
			background-color: green ;
		}
		
	</style>

	<body style="font-family: tahoma; background-color: #e9ebee;"> 
		<div id="bar">
			<div style = "font-size: 50px"> 
				CSK Predator Detector 
			</div>
			
			<div id="signup_button">
				Login
			</div>

		</div>

		<div id="bar2">

			Sign up to CSK Predator Detector<br><br><br>

			<form method="post" action="">
				<input name = "first_name" type="text" id="text" placeholder =  "First Name"><br><br>

				<input name = "last_name" type="text" id="text" placeholder =  "Last Name"><br><br>

				<input name = "email"type="text" id="text" placeholder =  "Email"><br><br>
				
				<input name = "password1" type="password" id="text" placeholder = "Password"><br><br>

				<input name = "password2" type="password" id="text" placeholder = "Retype Password"><br><br>

				<input type="submit" id="button" value= "Sign up"><br><br>

			</form>

		</div>

	</body>

</html>