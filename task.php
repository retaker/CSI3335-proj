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
include_once "task_func.php";

if(!isset($_SESSION["userId"])){
	die("<span style='color:red'> Please <a href='/login.php'>Log in</a>!</span>");
}

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["action"]) && $_GET["action"] == "create"){ //create task
		showCreateTask(); //TODO
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "edit"){ //edit task
		try{
			showEditTask($_GET["id"],$_SESSION["userId"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "delete"){ //delete task
		try{
			if(!authOwner($_SESSION["userId"],$_GET["id"])){
				throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
			}
			showTask($_SESSION["userId"],$_GET["id"]);
			showDeleteTask($_GET["id"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "update"){ //update progress
		try{
			showUpdateTask($_SESSION["userId"],$_GET["id"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "share"){ //share with group
		try{
			showShareTask($_SESSION["userId"],$_GET["id"]);
			showAddShare($_GET["id"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "removeShare"){
		try{
			showShareTask($_SESSION["userId"],$_GET["id"]);
			showRemoveShare($_GET["id"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "setComplete"){
		try{
			setComplete($_SESSION["userId"],$_GET["id"]); // add columns into TASK: creatorId, isComplete
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else if(isset($_GET["id"])){ // view task
		try{
			showTask($_SESSION["userId"],$_GET["id"]);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else{//main task page
		showAllTask($_SESSION["userId"]);
	}
}else if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["action"]) && $_POST["action"] == "create"){ //create task
		showCreateTask(); //TODO
		if(isset($_POST["taskName"]) && isset($_POST["category"]) && isset($_POST["due"]) && isset($_POST["priority"])){ // create task argument list check
			if(!isset($_POST["taskDescription"])){
				$_POST["taskDescription"] = " ";
			}
			
			try{
				createTask($_POST["taskName"],$_POST["taskDescription"],$_POST["category"],$_POST["due"],$_POST["priority"], $_SESSION["userId"]);
			}catch(Exception $e){
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Error: invalid attributes.</span>";
		}
	}else if(isset($_POST["id"]) && isset($_POST["action"]) && $_POST["action"] == "edit"){ //edit task
		if(isset($_GET["id"]) && $_POST["id"] == $_GET["id"]){
			try{
				showEditTask($_GET["id"],$_SESSION["userId"]);
				if(!isset($_POST["taskDescription"])){
					$_POST["taskDescription"] = " ";
				}
				editTask($_POST["taskName"],$_POST["taskDescription"],$_POST["category"],$_POST["due"],$_POST["priority"], $_POST["id"]);
			}catch(Exception $e){
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Invalid Task Id</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>";
		}
	}else if(isset($_POST["id"]) && isset($_POST["action"]) && $_POST["action"] == "delete"){ //delete task
		if(isset($_GET["id"]) && $_POST["taskId"] == $_GET["id"] && $_POST["taskId"]==$_POST["id"]){
			try{
				if(!authOwner($_SESSION["userId"],$_GET["id"])){
					throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
				}
				showTask($_SESSION["userId"],$_GET["id"]);
				showDeleteTask($_GET["id"]);
				deleteTask($_POST["id"]);
			}catch(Exception $e){
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Id does not match</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>";
		}
	}else if(isset($_POST["id"]) && isset($_POST["action"]) && $_POST["action"] == "update"){ //update progress
		if(isset($_GET["id"]) && $_POST["id"] == $_GET["id"]){
			try{
				showUpdateTask($_SESSION["userId"],$_GET["id"]);
				if(!isset($_POST["progress"])){
					$_POST["progress"] = " ";
				}
				updateTask($_POST["progress"],$_GET["id"]);
			}catch(Exception $e){
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Invalid Task Id</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>";
		}
	}else if(isset($_POST["id"]) && isset($_POST["action"]) && $_POST["action"] == "share"){ //share with group
		if(isset($_GET["id"]) && $_POST["id"] == $_GET["id"]){
			try{
				if(!authOwner($_SESSION["userId"],$_POST["id"])){
					throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
				}
				if(!isset($_POST["groupId"])){
					throw new Exception("<span style='color:red;'>Error: Need Group Id</span>");
				}
				$result = addShare($_POST["id"],$_POST["groupId"]);
				showShareTask($_SESSION["userId"],$_POST["id"]);
				showAddShare($_POST["id"]);
				echo $result;
			}catch(Exception $e){
				showShareTask($_SESSION["userId"],$_POST["id"]);
				showAddShare($_POST["id"]);
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Invalid Task Id</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>";
		}
	}else if(isset($_POST["id"]) && isset($_POST["action"]) && $_POST["action"] == "removeShare"){
		if(isset($_GET["id"]) && $_POST["id"] == $_GET["id"]){
			try{
				if(!authOwner($_SESSION["userId"],$_POST["id"])){
					throw new Exception("<span style='color:red;'>Permission Denied</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>",9);
				}
				if(!isset($_POST["groupId"]) || $_POST["groupId"] == ""){
					throw new Exception("<span style='color:red;'>Error: Need Group Id</span>");
				}
				$result = removeShare($_POST["id"],$_POST["groupId"]);
				showShareTask($_SESSION["userId"],$_POST["id"]);
				showRemoveShare($_POST["id"]);
				echo $result;
			}catch(Exception $e){
				showShareTask($_SESSION["userId"],$_POST["id"]);
				showRemoveShare($_POST["id"]);
				echo $e->getMessage();
			}
		}else{
			echo "<span style='color:red;'>Invalid Task Id</span><script>setTimeout(\"location.href = 'task.php';\",2000);</script>";
		}
	}else{//main task page
		showAllTask($_SESSION["userId"]);
	}
}
?>