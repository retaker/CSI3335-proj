<?php
include_once "header.php";
include_once $_SERVER['DOCUMENT_ROOT']."/config.php";
if(!$_SESSION["isAdmin"]){
	die("Permission denied");
}

function showAllTask(){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM TASK");
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
	$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["description"],$TASK["category"],
					   $TASK["due"],$TASK["priority"],$TASK["progress"],$TASK["progressTime"],
					   $TASK["modTime"],$TASK["creatorId"],$TASK["isComplete"]);
		?>
		<table>
			<tr>
				<th>Id</th>
				<th>Name</th>
				<th>due</th>
				<th>isComplete</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
<?php
		while($stmt->fetch()){	
			echo "<tr><td>". $TASK["id"] ."</td><td>". $TASK["name"] ."</td><td>". $TASK["due"] 
				."</td><td>". $TASK["isComplete"] ."</td><td><a href='/admin/task.php?action=edit&id=". $TASK["id"] ."'>Edit</a></td>
				<td><a href='/admin/task.php?action=delete&id=". $TASK["id"] ."'>Delete</a></td></tr>";
		}
?>
		</table>

<?php
	}else{
		echo "There's no task yet.";
	}
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
	if(isset($_GET["action"])){
		if($_GET["action"] == "delete" && isset($_GET["id"])){
			if($_GET["id"] == ""){
				echo "Invalid Id";
			}else{
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("DELETE FROM TASK_GROUP WHERE taskId = ?");
				$stmt->bind_param("i",$_GET["id"]);
				$stmt->execute();
				
				$stmt = $conn->prepare("DELETE FROM TASK WHERE taskId = ?");
				$stmt->bind_param("i",$_GET["id"]);
				$stmt->execute();
				
				header("Location: /admin/task.php");
			}
		}else if($_GET["action"] == "edit" && isset($_GET["id"])){
			if($_GET["id"] == ""){
				echo "Invalid Id";
			}else{
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("SELECT * FROM TASK WHERE taskId = ?");
				$stmt->bind_param("i", $_GET["id"]);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows > 0){
					$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["description"],$TASK["category"],
					   $TASK["due"],$TASK["priority"],$TASK["progress"],$TASK["progressTime"],
					   $TASK["modTime"],$TASK["creatorId"],$TASK["isComplete"]);
					$stmt->fetch();
?>
<form method="post">
	Task Name:<br/>
	<input type="text" name="taskName" value="<?php echo htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8'); ?>"></input><br/>
	Task Description:<br/>
	<textarea name="taskDescription" rows="10" cols="100"><?php echo htmlspecialchars($TASK["description"], ENT_QUOTES, 'UTF-8'); ?></textarea><br/>
	Category:<br/>
	<input type="text" name="category" value="<?php echo htmlspecialchars($TASK["category"], ENT_QUOTES, 'UTF-8'); ?>"></input><br/>
	Due:<br/>
	<input type="text" name="due" value="<?php echo $TASK["due"]; ?>"></input><br/>
	Priority:<br/>
	<select name="priority">
		<option value="3">High</option>
		<option value="2">Medium</option>
		<option value="1">Low</option>
	</select>
	Progress:<br/>
	<textarea name="progress" rows="10" cols="100"><?php echo htmlspecialchars($TASK["progress"], ENT_QUOTES, 'UTF-8'); ?></textarea><br/>
	Creator Id:<br/>
	<input type="text" name="creatorId" value="<?php echo $TASK["creatorId"]; ?>"></input><br/>
	isComplete:<br/>
	<input type="text" name="isComplete" value="<?php echo $TASK["isComplete"]; ?>"></input><br/>
	<input type="text" name="action" value="edit" style="display:none"></input>
	<input type="text" name="id" value="<?php echo $TASK["id"]; ?>" style="display:none"></input>
	<br/>
	<input type="submit"> <br/>
</form>
<?php
				}else{
					echo "task does not exist.";
				}
			}
		}
	}else{
		showAllTask();
	}
}else if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["action"])){
		if(isset($_POST["id"])&& $_POST["id"]!=""&&$_POST["action"] == "edit"){
			$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
			$stmt = $conn->prepare("UPDATE TASK SET taskName=?, taskDescription=?, category=?, due=?, priority=?, progress=?, creatorId=?, isComplete=? WHERE taskId=?");
			$stmt->bind_param("ssssisiii", $_POST["taskName"], $_POST["taskDescription"], $_POST["category"], $_POST["due"], $_POST["priority"],
							  $_POST["progress"], $_POST["creatorId"], $_POST["isComplete"], $_POST["id"]);
			$stmt->execute();
			if($stmt->errno){
				echo $stmt->error;
			}else{
				header("Location: /admin/task.php");
			}
		}
	}
}
?>