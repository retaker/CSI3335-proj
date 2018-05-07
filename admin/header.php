<head>
	<title>Admin Panel</title>
	<style>
		.header {
		  overflow: hidden;
		  background-color: #f1f1f1;
		  padding: 20px 10px;
		  margin-bottom:20px;
		}

		.header a {
		  float: left;
		  color: black;
		  text-align: center;
		  padding: 12px;
		  text-decoration: none;
		  font-size: 18px; 
		  line-height: 25px;
		  border-radius: 4px;
		}

		.header a.logo {
		  font-size: 25px;
		  font-weight: bold;
		}

		.header a:hover {
		  background-color: #ddd;
		  color: black;
		}

		.header-left {
		  float: left;
		}

		@media screen and (max-width: 500px) {
		  .header a {
			float: none;
			display: block;
			text-align: left;
		  }
		  .header-left {
			float: none;
		  }
		}
		
		td{
			text-align:center;
			padding-left:15px;
		}
		th{
			padding-left:15px;
		}
	</style>
</head>
<body>
	<div class="header">
		<div class="header-left">
			<?php
			session_start();
			?>
			<a href="/admin">Admin Panel</a>
			<?php
			if(isset($_SESSION["isAdmin"]) && $_SESSION["isAdmin"]){
?>
				<a href="user.php">User</a>
				<a href="task.php">Task</a>
				<a href="/logout.php" style="background-color:#ffacac">Log out</a>
<?php
			}else{
				header("Location: /");
			}
?>
		</div>
	</div>
</body>