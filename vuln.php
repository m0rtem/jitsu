<?php
	// jitsu should always be loaded first, at the start.
	require_once("jitsu.class.php");
	$jitsu = new jitsu();
?>
<form action="" method="post">
<textarea name="message" placeholder="your message"></textarea><br />
<input type="text" name="name" placeholder="your name" /><br />
<input type="submit" name="submit" id="submit" value="contact us" />
</form>
