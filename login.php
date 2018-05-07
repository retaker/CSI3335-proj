<html>
    <head>
        <title>Login</title>
    </head>
	<?php
        include_once "config.php";
		include_once "header.php";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
            $stmt = $conn->prepare("SELECT * FROM USER WHERE userName LIKE ?;");
            $stmt->bind_param("s", $_POST["userName"]);
            $stmt->execute();
            $stmt->bind_result($userId,$userName,$password,$isAdmin,$isBanned);
            $stmt->fetch();
            if($isBanned){
				echo "<span style = 'color:red'>You're Banned.</span>";
			}else if(password_verify($_POST["password"],$password)){
                echo "success<br/>";
                $_SESSION["userId"] = $userId;
                $_SESSION["userName"] = $_POST["userName"];
                $_SESSION["isAdmin"] = $isAdmin;
                header("Location: /");
            }else{
                echo "<span style = 'color:red'>Invalid login.</span>";
            }
            $stmt->close();
            $conn->close();
        }
        ?>
    <body>
        <form action="/login.php" method="post">
            User Name:<br/>
            <input type="text" name="userName"><br/>
            Password:<br/>
            <input type="password" name="password"><br/><br/>
            <input type="submit">
        </form>
        <div>Do not have an account? <a href="/register.php">Register</a></div>
    </body>
</html>
