<?php
	// jitsu should always be loaded first, at the start.
	require_once("jitsu.class.php");
	$jitsu = new jitsu();

if(isset($_POST["submit"]))
{
	$password = trim($_POST["password"]);
	$login = $jitsu->login($password);
	if($login)
	{
		session_start();
		setcookie("login", crc32($password), time()+31536000,'/');
		header("Location: /jitsu/");
	}
	else
	{
		$jitsu->addToBlacklist($jitsu->getIPAddress());
		echo "fuck off";
	}
}
if(isset($_POST["submit_blacklist"]))
{
	$jitsu->addToBlacklist($_POST["target"]);
	header("Location: /jitsu/");
}
if(isset($_GET["logout"]))
{
	setcookie("login", crc32($password), time()-31536000,'/');
	header("Location: /jitsu/");
}
if(isset($_GET["clear"]))
{
	$jitsu->clearLogs();
	header("Location: /jitsu/");
}
if(isset($_COOKIE["login"]))
{
	//$sessionCheck = $jitsu->sessionCheck($_COOKIE["login"]);
	//if(!$sessionCheck)
	//{
		//$jitsu->addToBlacklist($jitsu->getIPAddress());
		//exit;
	//}
}
else
{
?>
<form method="post" id="login" name="login" action="">
<input type="password" name="password" id="password" placeholder="Password" required/>
<input type="submit" name="submit" id="submit" value="Login" />
</form>
<?php
	exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    
    <link rel="icon" href="../../favicon.ico">

    <title>jitsu admin</title>

    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/bs/jqc-1.12.0,dt-1.10.11/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.bootstrap.min.css"/>
	<link href="public/css/template.css" rel="stylesheet">
    
    <style type="text/css">
	.container { width: auto; }
    </style>
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/jitsu/">jitsu admin</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/jitsu/">Dashboard</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

        <div style="float:left;"><h2>Newest Threats:</h2></div>
        <div style="float:right;"><a href="?logout"><button>Logout</button></a> <a href="?clear" onClick="alert('Are you sure?');"><button>Clear Logs</button></a></div>
        <hr style="clear:both;" />
        <?php $getAttackHistoryHTML = $jitsu->getAttackHistoryHTML(); echo $getAttackHistoryHTML;?>
        
        <h2>Add to blacklist:</h2>
        <hr />
        <form action="" method="post" name="addto" id="addto">
        <input type="text" placeholder="Enter an IP address" name="target" id="target" />
        <input type="submit" name="submit_blacklist" />
        </form>
        
        <h2>Blacklist:</h2>
        <hr />
        <?php echo $jitsu->getBlacklistHTML(); ?>

    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="public/js/bootstrap.min.js"></script>
	<script src="https://cdn.datatables.net/responsive/2.0.2/js/responsive.bootstrap.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/t/bs/jqc-1.12.0,dt-1.10.11/datatables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js"></script> 
	<script>
	$(document).ready(function() {
		$('#example').DataTable( {
			"scrollX": true,
			 "order": [[ 6, "desc" ]]
		} );
	} );
	</script>
  </body>
</html>
