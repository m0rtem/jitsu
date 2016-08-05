How to install

1. upload /jitsu/ dir to web server
2. setup jitsu database table using jitsu.sql
3. add:
require_once("./jitsu/jitsu.class.php");
$jitsu = new jitsu();
to the top of every page you want to protect
4. change password in jitsu.class.php then head over to /jitsu/ and log in
