<head>
    <style>
        th {vertical-align: top; padding-right:15px;}
		.menu{
			font-size:18px;
			font-weight:bold;
			margin:10px;
		}
    </style>
</head>
<?php
include_once "config.php";
include_once "header.php";
if(!isset($_SESSION["userId"])){
	echo "<span style='color:red'> Please <a href='/login.php'>Log in</a>!</span>";
	die();
}

function showAlterUser($id,$action,$conn){
	echo "Users' id in your group: ";
	$stmt = $conn->prepare("SELECT userId FROM USER_GROUP WHERE groupId = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		$stmt->bind_result($GROUP["userId"]);
		$users = array();
		while($stmt->fetch()){
			$users[] = $GROUP["userId"];
		}
		echo implode(", ",$users);
	}
?>
	<br/><br/>
	<form method="post">
		User Id:<br/>
		<input type="text" name="userId" title="The id of the user to be added/deleted"?><br/>
		<input type="text" name="groupId" style="display:none" value=<?php echo $id ?>></input>
		<input type="text" name="action" value="<?php echo $action; ?>" style="display:none"></input>
		<br/>
		<input type="submit"> <br/>
	</form>
<?php
	echo "<h4><a href='/group.php?id=".$id."'>Back</a></h4>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["action"])){
        if($_POST["action"] == "create"){
			$pattern = "/^\w+([ +\w]+)*$/";
            if(isset($_POST["groupName"]) && preg_match($pattern, $_POST["groupName"]) != 0){
                $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
                $stmt = $conn->prepare("INSERT INTO GROUPS(groupName,groupDescription,creatorId) VALUES(?,?," . $_SESSION["userId"] .")");
                $stmt->bind_param("ss", $_POST["groupName"], $_POST["groupDescription"]);
                $stmt->execute();
                if($stmt->errno == 0){
                    echo "Group \"" . $_POST["groupName"] . "\" successfully created";
                    $stmt = $conn->prepare("SELECT groupId FROM GROUPS WHERE groupName LIKE ?");
                    $stmt->bind_param("s",$_POST["groupName"]);
                    $stmt->execute();
                    $stmt->bind_result($GROUP["id"]);
                    $stmt->fetch();
					$conn2 = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
					$stmt2 = $conn2->prepare("INSERT INTO USER_GROUP VALUES(?,?);");
					$stmt2->bind_param("ii", $_SESSION["userId"], $GROUP["id"]);
					$stmt2->execute();
                    echo "<script>setTimeout(\"location.href = 'group.php?id=" . $GROUP["id"] . "';\",2000);</script>";
                }else{
                    echo "<span style='color:red;'>Error, please try again</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php?action=create';\",2000);</script>";
                }
            }else{
                echo "<span style='color:red;'>Error, invalid group name</span>";
                echo "<script>setTimeout(\"location.href = 'group.php?action=create';\",2000);</script>";
            }
        } else if($_POST["action"] == "addUser"){
            if(isset($_POST["groupId"])){
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("SELECT creatorId FROM GROUPS WHERE groupId = ?");
				$stmt->bind_param("i", $_POST["groupId"]);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows > 0){
					$stmt->bind_result($GROUP["creatorId"]);
					$stmt->fetch();
					if($_SESSION["userId"] == $GROUP["creatorId"]){
						if(isset($_POST["userId"])){
							$stmt = $conn->prepare("SELECT * FROM USER WHERE userId = ?;");
							$stmt->bind_param("i", $_POST["userId"]);
							$stmt->execute();
							$stmt->store_result();
							if($stmt->num_rows > 0){
								$stmt = $conn->prepare("SELECT * FROM USER_GROUP WHERE groupId = ? AND userId = ?");
								$stmt->bind_param("ii", $_POST["groupId"], $_POST["userId"]);
								$stmt->execute();
								$stmt->store_result();
								if($stmt->num_rows > 0 || $_POST["userId"] == $GROUP["creatorId"]){ // User alread exist in the group
									echo "<span style='color:orange;'>Warning, User already exist<br/></span>";
								}else {
									$stmt = $conn->prepare("INSERT INTO USER_GROUP VALUES(?,?);");
									$stmt->bind_param("ii", $_POST["userId"], $_POST["groupId"]);
									$stmt->execute();
									if($stmt->errno == 0){
										echo "<span style='color:green;'>User was successfully added<br/></span>";
									}else{
										echo $stmt->error;
									}
								}
							}else{
								echo "<span style='color:red;'>Error:User does not exist<br/></span>";
							}	
						}else{
							echo "<span style='color:red;'>Error, need user ID<br/></span>";
						}
						showAlterUser($_POST["groupId"],$_POST["action"],$conn);
					}else{
						echo "<span style='color:red;'>Error, permission denied</span>";
						echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
					}
				}else{
					echo "<span style='color:red;'>Error, Group does not exist</span>";
					echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
				}
			}else{
				echo "<span style='color:red;'>Error, need Group ID</span>";
                echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
			}
        } else if($_POST["action"] == "deleteUser"){
            if(isset($_POST["groupId"])){
				$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
				$stmt = $conn->prepare("SELECT creatorId FROM GROUPS WHERE groupId = ?");
				$stmt->bind_param("i", $_POST["groupId"]);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows > 0){
					$stmt->bind_result($GROUP["creatorId"]);
					$stmt->fetch();
					if($_SESSION["userId"] == $GROUP["creatorId"]){
						if(isset($_POST["userId"])){
							$stmt = $conn->prepare("SELECT * FROM USER_GROUP WHERE groupId = ? AND userId = ?");
							$stmt->bind_param("ii", $_POST["groupId"], $_POST["userId"]);
							$stmt->execute();
							$stmt->store_result();
							if($stmt->num_rows == 0){ // User not exist in the group
								echo "<span style='color:orange;'>Warning, User does not exist in the group<br/></span>";
							}else if($_SESSION["userId"] != $_POST["userId"]){
								$stmt = $conn->prepare("DELETE FROM USER_GROUP WHERE userId = ? AND groupId = ?");
								$stmt->bind_param("ii", $_POST["userId"], $_POST["groupId"]);
								$stmt->execute();
								if($stmt->errno == 0){
									echo "<span style='color:green;'>User was successfully removed<br/></span>";
								}else{
									echo $stmt->error;
								}
							}else{
								echo "<span style='color:orange;'>Warning, you can't remove yourself<br/></span>";
							}
						}else{
							echo "<span style='color:red;'>Error, need user ID<br/></span>";
						}
						showAlterUser($_POST["groupId"],$_POST["action"],$conn);
					}else{
						echo "<span style='color:red;'>Error, permission denied</span>";
						echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
					}
				}else{
					echo "<span style='color:red;'>Error, Group does not exist</span>";
					echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
				}
			}else{
				echo "<span style='color:red;'>Error, need Group ID</span>";
                echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
			}
        } else if($_POST["action"] == "editGroup"){
            $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
            $stmt = $conn->prepare("SELECT * FROM GROUPS WHERE groupId = ?");
            $stmt->bind_param("i",$_POST["id"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $stmt->bind_result($GROUP["id"],$GROUP["name"],$GROUP["description"],$GROUP["creatorId"]);
				$stmt->fetch();
				if($GROUP["creatorId"] == $_SESSION["userId"]){
					$pattern = "/^\w+([ +\w]+)*$/";
					if(isset($_POST["groupName"]) && preg_match($pattern, $_POST["groupName"]) != 0){
						$stmt = $conn->prepare("UPDATE GROUPS SET groupName = ?, groupDescription = ? WHERE groupId = ?;");
						$stmt->bind_param("ssi",$_POST["groupName"],$_POST["groupDescription"],$_POST["id"]);
						$stmt->execute();
						if($stmt->errno){
							echo $stmt->error;
							echo "<h4><a href='/group.php?id=".$_GET["id"]."'>Back</a></h4>";
						}else{
							echo "Edit succeed.";
							echo "<script>setTimeout(\"location.href = 'group.php?id=". $_POST["id"] ."';\",2000);</script>";
						}
					}else{
						echo "<span style='color:red;'>Error, invalid group name</span>";
						echo "<script>setTimeout(\"location.href = 'group.php?action=editGroup&id=".$_POST["id"]."';\",2000);</script>";
					}
                }else{
                    echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
                }
			}
        }else if($_POST["action"] == "deleteGroup"){
            $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
            $stmt = $conn->prepare("SELECT groupId,groupName,groupDescription,userName,creatorId FROM GROUPS NATURAL JOIN USER WHERE groupId = ? AND userId = creatorId");
            $stmt->bind_param("i",$_GET["id"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $stmt->bind_result($GROUP["id"],$GROUP["name"],$GROUP["description"],$GROUP["creator"],$GROUP["creatorId"]);
				$stmt->fetch();
				if($GROUP["creatorId"] == $_SESSION["userId"]){
?>
					<table>
						<tr>
							<th> Group id: </th>
							<td> <?php echo $GROUP["id"]; ?> </td>
						</tr>
						<tr>
							<th> Group Name: </th>
							<td> <?php echo htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8'); ?> </td>
						</tr>
						<tr>
							<th> Group description: </th>
							<td> <?php echo nl2br(htmlspecialchars($GROUP["description"], ENT_QUOTES, 'UTF-8')); ?> </td>
						</tr>
						<tr>
							<th> Creator: </th>
							<td><?php echo htmlspecialchars($GROUP["creator"], ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
					</table>
					
                    <form method="post" style="color:coral">
                        <br/>You're about to delete this group!<br/>
						Please confirm the id of the group to be deleted: <br/>
                        <input type="text" name="groupId"><br/>
                        <input type="text" name="id" style="display:none" value=<?php echo $GROUP["id"] ?>></input>
                        <input type="text" name="action" value="deleteGroup" style="display:none"></input>
                        <br/>
                        <input type="submit"> <br/>
                    </form>


<?php
					if($_POST["id"]==$_POST["groupId"] && $_POST["id"]==$_GET["id"]){
						$stmt = $conn->prepare("DELETE FROM USER_GROUP WHERE groupId = ?;");
						$stmt->bind_param("i",$_POST["groupId"]);
						$stmt->execute();
						if($stmt->errno){
							die($stmt->error);
						}else{
							$stmt = $conn->prepare("DELETE FROM TASK_GROUP WHERE groupId = ?;");
							$stmt->bind_param("i",$_POST["groupId"]);
							$stmt->execute();
							if($stmt->errno){
								die($stmt->error);
							}else{
								$stmt = $conn->prepare("DELETE FROM GROUPS WHERE groupId = ?;");
								$stmt->bind_param("i",$_POST["groupId"]);
								$stmt->execute();
								if($stmt->errno){
									die($stmt->error);
								}else{
									echo "GROUP DELETED!";
									die("<script>setTimeout(\"location.href = 'group.php';\",1000);</script>");
								}
							}
						}
					}else{
						echo "<span style='color:red'>Group id is not confirmed</span>";
					}
					
                }else{
                    echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
                }
			}else{
				echo "<span style='color:red;'>Error, Group does not exist</span>";
				echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
			}
        } else{
            echo "<span style='color:red;'>Error, invalid action</span>";
            echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
        }
        
    }
} else{
    if(isset($_GET["action"]) && $_GET["action"] == "create"){ 
?>
		<form action="/group.php" method="post">
			Group Name:<br/>
			<input type="text" name="groupName"><br/>
			Group Description:<br/>
			<textarea name="groupDescription" rows="5" cols="100"></textarea>
			<input type="text" name="action" value="create" style="display:none"></input>
			<br/><br/>
			<input type="submit"> <br/>
		</form>
<?php
		echo "<h4><a href='/group.php'>Back</a></h4>";
    } else if(isset($_GET["action"]) && $_GET["action"] == "editGroup"){
        if(isset($_GET["id"])){
            $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
            $stmt = $conn->prepare("SELECT * FROM GROUPS WHERE groupId = ?");
            $stmt->bind_param("i",$_GET["id"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $stmt->bind_result($GROUP["id"],$GROUP["name"],$GROUP["description"],$GROUP["creatorId"]);
                $stmt->fetch();
                if($GROUP["creatorId"] == $_SESSION["userId"]){ 
?>
                    <form action="/group.php?id=<?php echo $_GET["id"]?>&action=editGroup" method="post">
                        Group Name:<br/>
                        <input type="text" name="groupName" value="<?php echo htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8'); ?>"><br/>
                        Group Description:<br/>
                        <textarea name="groupDescription" rows="5" cols="100"><?php echo htmlspecialchars($GROUP["description"], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <input type="text" name="id" style="display:none" value=<?php echo $GROUP["id"] ?>></input>
                        <input type="text" name="action" value="editGroup" style="display:none"></input>
                        <br/><br/>
                        <input type="submit"> <br/>
                    </form>
<?php
					echo "<h4><a href='/group.php?id=".$_GET["id"]."'>Back</a></h4>";
                }else{
                    echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
                }
            }else{
                echo "<span style='color:red;'>Error, Group does not exist</span>";
                echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
            }
        }else{
            echo "<span style='color:red;'>Error, need group id</span>";
            echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
        }
    } else if(isset($_GET["action"]) && $_GET["action"] == "addUser"){
		if(isset($_GET["id"])){
			$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
			$stmt = $conn->prepare("SELECT creatorId FROM GROUPS WHERE groupId = ?");
			$stmt->bind_param("i", $_GET["id"]);
			$stmt->execute();
        	$stmt->store_result();
        	if($stmt->num_rows > 0){
				$stmt->bind_result($GROUP["creatorId"]);
				$stmt->fetch();
				if($_SESSION["userId"] == $GROUP["creatorId"]){
					showAlterUser($_GET["id"],$_GET["action"],$conn);
				}else{
					echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
				}
			}else{
				echo "<span style='color:red;'>Error, group does not exist</span>";
				echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
			}
		}else{
			echo "<span style='color:red;'>Error, need group id</span>";
            echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
		}
	} else if(isset($_GET["action"]) && $_GET["action"] == "deleteUser"){
		if(isset($_GET["id"])){
			$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
			$stmt = $conn->prepare("SELECT creatorId FROM GROUPS WHERE groupId = ?");
			$stmt->bind_param("i", $_GET["id"]);
			$stmt->execute();
        	$stmt->store_result();
        	if($stmt->num_rows > 0){
				$stmt->bind_result($GROUP["creatorId"]);
				$stmt->fetch();
				if($_SESSION["userId"] == $GROUP["creatorId"]){
					showAlterUser($_GET["id"],$_GET["action"],$conn);
				}else{
					echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
				}
			}else{
				echo "<span style='color:red;'>Error, group does not exist</span>";
				echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
			}
		}else{
			echo "<span style='color:red;'>Error, need group id</span>";
            echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
		}
	}else if(isset($_GET["action"]) && $_GET["action"] == "deleteGroup"){
		if(isset($_GET["id"])){
            $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
            $stmt = $conn->prepare("SELECT groupId,groupName,groupDescription,userName,creatorId FROM GROUPS NATURAL JOIN USER WHERE groupId = ? AND userId = creatorId");
            $stmt->bind_param("i",$_GET["id"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $stmt->bind_result($GROUP["id"],$GROUP["name"],$GROUP["description"],$GROUP["creator"],$GROUP["creatorId"]);
                $stmt->fetch();
                if($GROUP["creatorId"] == $_SESSION["userId"]){ 
?>
					<table>
						<tr>
							<th> Group id: </th>
							<td> <?php echo $GROUP["id"]; ?> </td>
						</tr>
						<tr>
							<th> Group Name: </th>
							<td> <?php echo htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8'); ?> </td>
						</tr>
						<tr>
							<th> Group description: </th>
							<td> <?php echo nl2br(htmlspecialchars($GROUP["description"], ENT_QUOTES, 'UTF-8')); ?> </td>
						</tr>
						<tr>
							<th> Creator: </th>
							<td><?php echo htmlspecialchars($GROUP["creator"], ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
					</table>
					
                    <form method="post" style="color:coral">
                        <br/>You're about to delete this group!<br/>
						Please confirm the id of the group to be deleted: <br/>
                        <input type="text" name="groupId"><br/>
                        <input type="text" name="id" style="display:none" value=<?php echo $GROUP["id"] ?>></input>
                        <input type="text" name="action" value="deleteGroup" style="display:none"></input>
                        <br/>
                        <input type="submit"> <br/>
                    </form>


<?php
                	echo "<h4><a href='/group.php?id=".$_GET["id"]."'>Back</a></h4>";
				}else{
                    echo "<span style='color:red;'>Error, permission denied</span>";
                    echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
                }
            }else{
                echo "<span style='color:red;'>Error, Group does not exist</span>";
                echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
            }
        }else{
            echo "<span style='color:red;'>Error, need group id</span>";
            echo "<script>setTimeout(\"location.href = 'group.php';\",2000);</script>";
        }
	}else if(isset($_GET["id"])){
        $conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
        $stmt = $conn->prepare("SELECT groupId,groupName,groupDescription,userName FROM GROUPS NATURAL JOIN USER WHERE groupId = ? AND userId = creatorId");
        $stmt->bind_param("i", $_GET["id"]);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $stmt->bind_result($GROUP["id"],$GROUP["name"],$GROUP["description"],$GROUP["creator"]);
            $stmt->fetch();
?>
            <table>
	            <tr>
		            <th> Group id: </th>
		            <td> <?php echo $GROUP["id"]; ?> </td>
	            </tr>
                <tr>
                    <th> Group Name: </th>
                    <td> <?php echo htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8'); ?> </td>
                </tr>
                <tr>
                    <th> Group description: </th>
                    <td> <?php echo nl2br(htmlspecialchars($GROUP["description"], ENT_QUOTES, 'UTF-8')); ?> </td>
                </tr>
                <tr>
                    <th> Creator: </th>
                    <td><?php echo htmlspecialchars($GROUP["creator"], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </table>
<?php
            $stmt->close();
            $conn->close();
			if($_SESSION["userName"] == $GROUP["creator"]){
				echo "<br/><a class='menu' href='/group.php?id=".$GROUP["id"]."&action=addUser'>Add User</a>";
				echo "<a class='menu' href='/group.php?id=".$GROUP["id"]."&action=deleteUser'>Remove User</a>";
				echo "<a class='menu' href='/group.php?id=".$GROUP["id"]."&action=editGroup'>Edit Group</a>";
				echo "<a class='menu' href='/group.php?id=".$GROUP["id"]."&action=deleteGroup'>Delete Group</a>";
			}
        }else{
            echo "<span style='color:red;'>Group does not exist</span>";
			die("<script>setTimeout(\"location.href = 'group.php';\",2000);</script>");
        }
    }else{
?>
<h3><a href="/group.php?action=create">Create Group</a></h3>
<?php
		$conn = new mysqli(SQL_SERVER, SQL_USER, SQL_PWD, SQL_DB);
        $stmt = $conn->prepare("SELECT groupId, groupName FROM GROUPS WHERE creatorId = ?");
        $stmt->bind_param("i", $_SESSION["userId"]);
        $stmt->execute();
        $stmt->store_result();
		if($stmt->num_rows > 0){
            $stmt->bind_result($GROUP["id"],$GROUP["name"]);
?>			
<h3>Group you created: </h3>
<?php
			while($stmt->fetch()){
				echo "<a href=/group.php?id=" . $GROUP["id"] . ">" . htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8') . "</a><br/>";
			}
		}
		
		$stmt = $conn->prepare("SELECT groupId, groupName FROM GROUPS NATURAL JOIN USER_GROUP WHERE userId != creatorId AND userId = ?;");
        $stmt->bind_param("i", $_SESSION["userId"]);
        $stmt->execute();
        $stmt->store_result();
		if($stmt->num_rows > 0){
            $stmt->bind_result($GROUP["id"],$GROUP["name"]);
?>
			<h3>Group you joined: </h3>
<?php
			while($stmt->fetch()){
				echo "<a href=/group.php?id=" . $GROUP["id"] . ">" . htmlspecialchars($GROUP["name"], ENT_QUOTES, 'UTF-8') . "</a><br/>";
			}
		}
	}
}
?>