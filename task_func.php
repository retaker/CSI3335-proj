<?php
function authOwner($user,$id){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT creatorId FROM TASK WHERE taskId = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["creatorId"]);
		$stmt->fetch();
		return $user == $TASK["creatorId"];
	}else{
		throw new Exception("<span style='color:red;'>Task not found</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>", 1);
	}
}

function authUser($user,$id){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM USER_GROUP NATURAL JOIN GROUPS WHERE groupId IN (SELECT groupId FROM TASK NATURAL JOIN TASK_GROUP WHERE taskId = ?) AND userId = ?;");
	$stmt->bind_param("ii", $id,$user);
	$stmt->execute();
	$stmt->store_result();
	return $stmt->num_rows > 0;
}

function showTask($user,$id){
	if(!(authOwner($user,$id) || authUser($user,$id))){
		throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
	}
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT taskId,taskName,taskDescription,category,due,priority,progress,progressTime,modTime,userName,isComplete FROM TASK NATURAL JOIN USER WHERE taskId = ? AND (creatorId = ? OR userId IN (SELECT userId FROM TASK NATURAL JOIN TASK_GROUP NATURAL JOIN USER_GROUP WHERE taskId = ?)) AND creatorId = userId;");
	$stmt->bind_param("iii", $id,$user,$id);
	$stmt->execute();
	$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["description"],$TASK["category"],$TASK["due"],$TASK["priority"],$TASK["progress"],$TASK["progressTime"],$TASK["modTime"],$TASK["creator"],$TASK["isComplete"]);
	$stmt->fetch();
?>
	<table>
		<tr>
			<th> Task id: </th>
			<td> <?php echo $TASK["id"]; ?> </td>
		</tr>
		<tr>
			<th> Task Name: </th>
			<td> <?php echo htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8'); ?> </td>
		</tr>
		<tr>
			<th> Task Description: </th>
			<td> <?php echo nl2br(htmlspecialchars($TASK["description"], ENT_QUOTES, 'UTF-8')); ?> </td>
		</tr>
		<tr>
			<th> Category: </th>
			<td> <?php echo htmlspecialchars($TASK["category"], ENT_QUOTES, 'UTF-8'); ?> </td>
		</tr>
		<tr>
			<th> Due: </th>
			<td> <?php echo $TASK["due"]; ?> </td>
		</tr>
		<tr>
			<th> Priority: </th>
			<td> <?php echo $TASK["priority"]; ?> </td>
		</tr>
		<tr>
			<th> Progress: </th>
			<td> <?php echo nl2br(htmlspecialchars($TASK["progress"], ENT_QUOTES, 'UTF-8')); ?> </td>
		</tr>
		<tr>
			<th> Progress Update Time: </th>
			<td> <?php echo $TASK["progressTime"]; ?> </td>
		</tr>
		<tr>
			<th> Last Modify Time: </th>
			<td> <?php echo $TASK["modTime"]; ?> </td>
		</tr>
		<tr>
			<th> Creator: </th>
			<td><?php echo $TASK["creator"]; ?></td>
		</tr>
		<tr>
			<th> isComplete: </th>
			<td><?php if($TASK["isComplete"]){ echo "True"; }else{ echo "False"; } ?></td>
		</tr>
	</table>
<?php
if(authOwner($user,$id)){
?>
<a class="menu" href="/task.php?action=share&id=<?php echo $id; ?>">Add Group</a>
<a class="menu" href="/task.php?action=removeShare&id=<?php echo $id; ?>">Remove Group</a>
<a class="menu" href="/task.php?action=edit&id=<?php echo $id; ?>">Edit Task</a>
<a class="menu" href="/task.php?action=delete&id=<?php echo $id; ?>">Delete Task</a>
<a class="menu" href="/task.php?action=setComplete&id=<?php echo $id; ?>">Set Complete</a>
<?php
}
?>
<a class="menu" href="/task.php?action=update&id=<?php echo $id; ?>">Update Task</a>
<h4><a href="/task.php">Back</a></h4>
<?php
}

function showAllTask($user){
	echo "<h3><a href='/task.php?action=create'>Create Task</a></h3>";
	echo "<h3>Tasks you created</h3>";
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT taskId, taskName, due FROM TASK WHERE creatorId = ? AND isComplete = 0");
	$stmt->bind_param("i", $user);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["due"]);
		while($stmt->fetch()){
			echo "<a href='/task.php?id=" . $TASK["id"] . "'>" . htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8') . " [Due: " . $TASK["due"] . "]</a><br/>";
		}
	}
	
	$stmt = $conn->prepare("SELECT taskId, taskName, due FROM TASK WHERE creatorId = ? AND isComplete = 1");
	$stmt->bind_param("i", $user);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["due"]);
		while($stmt->fetch()){
			echo "<a style='color:green' href='/task.php?id=" . $TASK["id"] . "'>" . htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8') . " [Completed]</a><br/>";
		}
	}
	
	echo "<h3>Tasks shared with you</h3>";
	$stmt = $conn->prepare("SELECT DISTINCT taskId, taskName, due FROM TASK_GROUP NATURAL JOIN TASK NATURAL JOIN USER_GROUP WHERE creatorId != userId AND userId = ? AND isComplete = 0;");
	$stmt->bind_param("i", $user);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["due"]);
		while($stmt->fetch()){
			echo "<a href='/task.php?id=" . $TASK["id"] . "'>" . htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8') . " [Due: " . $TASK["due"] . "]</a><br/>";
		}
	}
	
	$stmt = $conn->prepare("SELECT taskId, taskName, due FROM TASK_GROUP NATURAL JOIN TASK NATURAL JOIN USER_GROUP WHERE creatorId != userId AND userId = ? AND isComplete = 1;");
	$stmt->bind_param("i", $user);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["due"]);
		while($stmt->fetch()){
			echo "<a style='color:green' href='/task.php?id=" . $TASK["id"] . "'>" . htmlspecialchars($TASK["name"], ENT_QUOTES, 'UTF-8') . " [Completed]</a><br/>";
		}
	}
}

function showCreateTask(){
?>
<form method="post">
	Task Name:<br/>
	<input type="text" name="taskName"></input><br/>
	Task Description:<br/>
	<textarea name="taskDescription" rows="10" cols="100"></textarea><br/>
	Category:<br/>
	<input type="text" name="category"></input><br/>
	Due:<br/>
	<input type="text" name="due"></input><br/>
	Priority:<br/>
	<select name="priority">
		<option value="3">High</option>
		<option value="2">Medium</option>
		<option value="1">Low</option>
	</select>
	<input type="text" name="action" value="create" style="display:none"></input>
	<br/><br/>
	<input type="submit"> <br/>
</form>
<h4><a href="/task.php">Back</a></h4>
<?php
}

function validateDatetime($d){
	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $d);
	$errors = DateTime::getLastErrors();
	if (!empty($errors['warning_count'])) {
		return false;
	}
	return $dateTime !== false;
}

function createTask($name, $description, $category, $due, $priority,$uid){
	$p2 = "/^\w+([ +\w]+)*$/";
	if(!preg_match($p2, $name)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Task Name Format</span>",3);
	}else if(!validateDatetime($due)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Due Date Format</span>",3);
	}else if(!preg_match($p2, $category)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Category Name Format</span>",3);
	}
	$due = substr($due,0,13) . ":00:00";
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("INSERT INTO TASK(taskName, taskDescription, category, due, priority, creatorId) VALUES(?,?,?,?,?,?);");
	$stmt->bind_param("ssssii", $name,$description,$category,$due,$priority,$uid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	}
	$stmt = $conn->prepare("SELECT taskId FROM TASK WHERE taskName LIKE ?");
	$stmt->bind_param("s", $name);
	$stmt->execute();
	$stmt->bind_result($TASK["id"]);
	$stmt->fetch();
	echo "<span style='color:green'>Succeed!</span><script>setTimeout(\"location.href = 'task.php?id=". $TASK["id"] ."';\",500);</script>";
}

function showEditTask($tid,$uid){
	if(!(authOwner($uid,$tid))){
		throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
	}
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM TASK WHERE taskId = ?");
	$stmt->bind_param("i", $tid);
	$stmt->execute();
	$stmt->bind_result($TASK["id"],$TASK["name"],$TASK["description"],$TASK["category"],$TASK["due"],$TASK["priority"],$TASK["progress"],$TASK["progressTime"],$TASK["modTime"],$TASK["creatorId"],$TASK["isComplete"]);
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
	<input type="text" name="action" value="edit" style="display:none"></input>
	<input type="text" name="id" value="<?php echo $tid; ?>" style="display:none"></input>
	<br/>
	<input type="submit"> <br/>
</form>
<h4><a href="/task.php?id=<?php echo $tid; ?>">Back</a></h4>
<?php
}

function editTask($name, $description, $category, $due, $priority,$tid){
	$p2 = "/^\w+([ +\w]+)*$/";
	if(!preg_match($p2, $name)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Task Name Format</span>",3);
	}else if(!validateDatetime($due)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Due Date Format</span>",3);
	}else if(!preg_match($p2, $category)){
		throw new Exception("<span style='color:red'>ERROR: Invalid Category Name Format</span>",3);
	}
	$due = substr($due,0,13) . ":00:00";
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("UPDATE TASK SET taskName=?, taskDescription=?, category=?, due=?, priority=? WHERE taskId=?;");
	$stmt->bind_param("ssssii", $name,$description,$category,$due,$priority,$tid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	} else {
		echo "<span style='color:green'>Succeed!</span><script>setTimeout(\"location.href = 'task.php?id=". $tid ."';\",500);</script>";
	}
}

function showDeleteTask($tid){
?>
	<form method="post" style="color:coral">
		<br/>You're about to delete this task!<br/>
		Please confirm the id of the task to be deleted: <br/>
		<input type="text" name="taskId"><br/>
		<input type="text" name="id" style="display:none" value=<?php echo $tid ?>></input>
		<input type="text" name="action" value="delete" style="display:none"></input>
  		<br/>
		<input type="submit"> <br/>
	</form>
<?php
}

function deleteTask($tid){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("DELETE FROM TASK_GROUP WHERE taskId = ?");
	$stmt->bind_param("i", $tid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	}
	
	$stmt = $conn->prepare("DELETE FROM TASK WHERE taskId = ?");
	$stmt->bind_param("i", $tid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	} else {
		echo "<span style='color:green'>Succeed!</span><script>setTimeout(\"location.href = 'task.php';\",500);</script>";
	}
}

function setComplete($user,$tid){
	if(!authOwner($user,$tid)){
		throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
	}
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT isComplete FROM TASK WHERE taskId = ?");
	$stmt->bind_param("i", $tid);
	$stmt->execute();
	$stmt->bind_result($TASK["isComplete"]);
	$stmt->fetch();
	$conn2 = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	if($TASK["isComplete"] == 1){
		$stmt2 = $conn2->prepare("UPDATE TASK SET isComplete = 0 WHERE taskId = ?");
	}else{
		$stmt2 = $conn2->prepare("UPDATE TASK SET isComplete = 1 WHERE taskId = ?");
	}
	$stmt2->bind_param("i", $tid);
	$stmt2->execute();
	if($stmt2->errno){
		throw new Exception("<span style='color:red'>".$stmt2->error."</span>",5);
	} else {
		echo "<span style='color:green'>Succeed!</span><script>setTimeout(\"location.href = 'task.php';\",500);</script>";
	}
}

function showUpdateTask($user,$tid){
	if(!(authOwner($user,$tid) || authUser($user,$tid))){
		throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
	}
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT progress FROM TASK NATURAL JOIN USER WHERE taskId = ? AND (creatorId = ? OR userId IN (SELECT userId FROM TASK NATURAL JOIN TASK_GROUP NATURAL JOIN USER_GROUP WHERE taskId = ?)) AND creatorId = userId;");
	$stmt->bind_param("iii", $tid,$user,$tid);
	$stmt->execute();
	$stmt->bind_result($TASK["progress"]);
	$stmt->fetch();
?>
<form method="post">
	Task Progress:<br/>
	<textarea name="progress" rows="10" cols="100"><?php echo htmlspecialchars($TASK["progress"], ENT_QUOTES, 'UTF-8'); ?></textarea><br/>
	<input type="text" name="action" value="update" style="display:none"></input>
	<input type="text" name="id" value="<?php echo $tid; ?>" style="display:none"></input>
	<br/>
	<input type="submit"> <br/>
</form>
<h4><a href="/task.php?id=<?php echo $tid; ?>">Back</a></h4>
<?php
}

function updateTask($p,$tid){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("UPDATE TASK SET progress = ?, progressTime = NOW() WHERE taskId = ?");
	$stmt->bind_param("si", $p,$tid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	}
	
	echo "<span style='color:green'>Succeed!</span><script>setTimeout(\"location.href = 'task.php?id=". $tid ."';\",500);</script>";
}

function showShareTask($uid,$tid){
	if(!authOwner($uid,$tid)){
		throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
	}
	
	echo "Groups shared: ";
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT groupId FROM TASK_GROUP WHERE taskId = ?");
	$stmt->bind_param("i", $tid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($TASK["groupId"]);
		$groups = array();
		while($stmt->fetch()){
			$groups[] = $TASK["groupId"];
		}
		echo implode(", ",$groups);
	}	
}

function showAddShare($tid){
?>

<br/><br/>
	<form method="post">
		Group Id:<br/>
		<input type="text" name="groupId" title="The id of the group to be added"?><br/>
		<input type="text" name="id" style="display:none" value=<?php echo $tid ?>></input>
		<input type="text" name="action" value="share" style="display:none"></input>
		<br/>
		<input type="submit"> <br/>
	</form>
<h4><a href="/task.php?id=<?php echo $tid; ?>">Back</a></h4>
<?php
}

function showRemoveShare($tid){
?>

<br/><br/>
	<form method="post">
		Group Id:<br/>
		<input type="text" name="groupId" title="The id of the group to be deleted"?><br/>
		<input type="text" name="id" style="display:none" value=<?php echo $tid ?>></input>
		<input type="text" name="action" value="removeShare" style="display:none"></input>
		<br/>
		<input type="submit"> <br/>
	</form>
<h4><a href="/task.php?id=<?php echo $tid; ?>">Back</a></h4>
<?php
}

function addShare($tid,$gid){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM GROUPS WHERE groupId = ?");
	$stmt->bind_param("i", $gid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows <= 0){
		throw new Exception("<span style='color:red;'>ERROR: Group Not Found</span>",2);
	}
	$stmt = $conn->prepare("SELECT * FROM TASK_GROUP WHERE groupId = ? AND taskId = ?");
	$stmt->bind_param("ii", $gid, $tid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		throw new Exception("<span style='color:orange;'>Warning: Group Already Exist</span>",6);
	}
	
	$stmt = $conn->prepare("INSERT INTO TASK_GROUP VALUES(?,?)");
	$stmt->bind_param("ii", $tid, $gid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	}
	
	return "<span style='color:green'>Succeed!</span>";
}

function removeShare($tid,$gid){
	$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
	$stmt = $conn->prepare("SELECT * FROM TASK_GROUP WHERE groupId = ? AND taskId = ?");
	$stmt->bind_param("ii", $gid, $tid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows <= 0){
		throw new Exception("<span style='color:red;'>Error: Group Is Not Shared</span>",2);
	}
	
	$stmt = $conn->prepare("DELETE FROM TASK_GROUP WHERE taskId = ? AND groupId = ?");
	$stmt->bind_param("ii", $tid, $gid);
	$stmt->execute();
	if($stmt->errno){
		throw new Exception("<span style='color:red'>".$stmt->error."</span>",5);
	}
	
	return "<span style='color:green'>Succeed!</span>";
}
?>