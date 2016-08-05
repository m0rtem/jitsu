<?php
require_once("geoip/geoip.inc");
require_once("geoip/geoipcity.inc");
require_once("geoip/geoipregionvars.php");
 
$ipaddress = "173.194.36.1";
$gi = geoip_open("geoip/GeoLiteCity.dat", GEOIP_STANDARD);
$rsGeoData = geoip_record_by_addr($gi, $ipaddress);
$lat = $rsGeoData->latitude;
$long = $rsGeoData->longitude;
$city = $rsGeoData->city;
$state = $rsGeoData->region;
$country = $rsGeoData->country_name;
geoip_close($gi);
 
echo $city . ":" . $state . ":" . $country;
?>