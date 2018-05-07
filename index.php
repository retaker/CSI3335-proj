<body>
    <?php
	include_once "header.php";
    if(isset($_SESSION["userId"])){
        echo "Hi, ". $_SESSION["userName"] ."<br/> 
        Your user id is " . $_SESSION["userId"];
    }else{
    ?>
	<p>You'll need to <a href="/login.php">log in</a> to proceed.</p>
    <?php
    }
    ?>
</body>