<html>
    <head>
        <title>Register</title>
    </head>
    <body>
		<?php
        include_once "config.php";
		include_once "header.php";
		$error="";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pattern = "/^[\w]{4,16}$/";
            if(isset($_POST["userName"]) && preg_match($pattern, $_POST["userName"]) != 0){
                if(isset($_POST["password"]) && strlen($_POST["password"]) >= 6){
                    $hash = password_hash($_POST["password"],PASSWORD_DEFAULT);
                    //echo $_POST["userName"].":".$hash."<br/>";
					$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
					$stmt = $conn->prepare("INSERT INTO USER(userName,password) VALUES(?,?)");
					$stmt->bind_param("ss", $_POST["userName"], $hash);
					$stmt->execute();
					if($stmt->errno != 0){
						$error = "<span style='color:red;'>User has already been registered.</span>";
						//echo $stmt->error;
					}else if($stmt->errno == 0){
						$stmt2 = $conn->prepare("SELECT userId, isAdmin FROM USER WHERE userName LIKE ?");
						$stmt2->bind_param("s", $_POST["userName"]);
						$stmt2->execute();
						$stmt2->bind_result($userId, $isAdmin);
						$stmt2->fetch();
						$stmt2->close();
						$_SESSION["userId"] = $userId;
						$_SESSION["userName"] = $_POST["userName"];
						$_SESSION["isAdmin"] = $isAdmin;
						header("Location: /");
					}
					$stmt->close();
					$conn->close();
                }else{
                    $error = "<span style='color:red;'>Invalid Password.</span>";
                }
            } else {
                $error = "<span style='color:red;'>Invalid user name.</span>";
            }
        }
        ?>
        <div>
            User name should only contain letters, numbers and "_" <br/>
            and its length should in between 4-16. <br/>
            Password should be at least 6 characters long. <br/> <br/>
        </div>
        <form action="/register.php" method="post">
            User Name:<br/>
            <input type="text" name="userName"><br/>
            Password:<br/>
            <input type="password" name="password"><br/><br/>
            <input type="submit">
        </form>
		<?php
		echo $error;
		?>
    </body>
</html>
