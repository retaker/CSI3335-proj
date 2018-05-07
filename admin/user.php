<?php
include_once "header.php";
include_once $_SERVER['DOCUMENT_ROOT']."/config.php";

function showAllUser(){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM USER");
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($USER["id"],$USER["name"],$USER["pwd"],$USER["isAdmin"],$USER["isBanned"]);
		?>
		<table>
			<tr>
				<th>Id</th>
				<th>Name</th>
				<th>isAdmin</th>
				<th>isBanned</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
<?php
		while($stmt->fetch()){	
			echo "<tr><td>". $USER["id"] ."</td><td>". $USER["name"] ."</td><td>". $USER["isAdmin"] 
				."</td><td>". $USER["isBanned"] ."</td><td><a href='/admin/user.php?action=edit&id=". $USER["id"] ."'>Edit</a></td>
				<td><a href='/admin/user.php?action=ban&id=". $USER["id"] ."'>Ban</a></td></tr>";
		}
?>
		</table>

<?php
	}else{
		echo "There's no user yet.";
	}
}


if(!$_SESSION["isAdmin"]){
	die("Permission denied");
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
	if(isset($_GET["action"])){
		if($_GET["action"] == "ban" && isset($_GET["id"])){
			if($_GET["id"] == ""){
				echo "Invalid Id";
			}else{
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("SELECT isBanned FROM USER WHERE userId = ?");
				$stmt->bind_param("i", $_GET["id"]);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows > 0){
					$stmt->bind_result($USER["isBanned"]);
					$stmt->fetch();
					if($USER["isBanned"]){
						$conn2 = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
						$stmt2 = $conn2->prepare("UPDATE USER SET isBanned = 0 WHERE userId = ?");
						$stmt2->bind_param("i", $_GET["id"]);
						$stmt2->execute();
					}else{
						$conn2 = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
						$stmt2 = $conn2->prepare("UPDATE USER SET isBanned = 1 WHERE userId = ?");
						$stmt2->bind_param("i", $_GET["id"]);
						$stmt2->execute();
					}
					header("Location: /admin/user.php");
				}else{
					echo "User does not exist.";
				}
			}
		}else if($_GET["action"] == "edit" && isset($_GET["id"])){
			if($_GET["id"] == ""){
				echo "Invalid Id";
			}else{
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("SELECT * FROM USER WHERE userId = ?");
				$stmt->bind_param("i", $_GET["id"]);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows > 0){
					$stmt->bind_result($USER["id"],$USER["name"],$USER["pwd"],$USER["isAdmin"],$USER["isBanned"]);
					$stmt->fetch();
?>
<form method="post">
	UserName:<br/>
	<input type="text" name="userName" value="<?php echo $USER["name"] ?>"></input><br/>
	isAdmin: <br/>
	<input type="text" name="isAdmin" value="<?php echo $USER["isAdmin"] ?>"></input><br/><br/>
	<input type="text" name="id" value="<?php echo $USER["id"] ?>" style="display:none"></input>
	<input type="text" name="action" value="edit" style="display:none"></input>
	<input type="submit">
</form>
<?php
				}else{
					echo "User does not exist.";
				}
			}
		}
	}else{
		showAllUser();
	}
}else if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["action"])){
		if(isset($_POST["id"])&& $_POST["id"]!=""&&$_POST["action"] == "edit"){
			$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
			$stmt = $conn->prepare("UPDATE USER SET userName = ?, isAdmin = ? WHERE userId = ?");
			$stmt->bind_param("sii", $_POST["userName"], $_POST["isAdmin"], $_POST["id"]);
			$stmt->execute();
			if($stmt->errno){
				echo $stmt->error;
			}else{
				header("Location: /admin/user.php");
			}
		}
	}
}
?>