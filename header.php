<head>
	<title>Database Project</title>
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
	</style>
</head>
<body>
	<div class="header">
		<div class="header-left">
			<?php
			session_start();
			?>
			<a href="/">Home</a>
			<?php
			if(isset($_SESSION["userId"])){
?>
				<a href="/group.php">Group</a>
				<a href="/task.php">Task</a>
				<a href="/logout.php" style="background-color:#ffacac">Log out</a>
<?php
			}else{
?>
				<a href="/login.php" style="background-color:#4285f4; color:white;">Login</a>
				<a href="/register.php">Register</a>
<?php
			}
?>
		</div>
	</div>
</body>