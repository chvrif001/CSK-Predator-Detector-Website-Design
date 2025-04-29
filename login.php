<?php
session_start();

	include("classes/connect.php");
	include("classes/login.php");

	
	$email = "";
	$password = "";
	

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    	$login = new Login();
    	$result = $login->evaluate($_POST);

    	if ($result != "") {
    		echo "<div style='text-align: center; font-size:12px; color: white; background-color: grey;'>";
    		echo "The following errors occurred<br><br>";
        	echo $result;	
        	echo "</div>";
    	}
    	else
    	{
    		header("Location: profile.php");
    		die;
    	}

    	
		$email = $_POST['email'];
		$password = $_POST['password'];
		
	}

?>

<html> 


	<head> 
		<title>PredatorDetector | Log in</title>
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
			height: 250px; 
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
				PredatorDetector 
			</div>
			
			<div id="signup_button">
				Signup
			</div>

		</div>

		<div id="bar2">
			<form method= "post">
				Log in to CSK Predator Detector<br><br><br>

				<input name="email" type="text" id="text" placeholder =  "Email"><br><br>
				<input name="password" type="password" id="text" placeholder = "Password"><br><br>
				<input type="submit" id="button" value= "Login">
				<br><br>
			</form>
		</div>

	</body>

</html>