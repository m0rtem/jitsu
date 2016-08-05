<?php
/**
 * jitsu - A PHP based website security framework
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Security
 * @package    jitsu
 * @author     Chris Feasey <chris@is-the.ninja>
 * @copyright  2016 chris
 */
class jitsu
{
	public $version = "1.0.0";
	
	// Filter rules from: https://github.com/PHPIDS/PHPIDS
	public $filter_rules_json = "/jitsu/filter_rules.json";
	public $filter_rules;
	
	public $path_to_blacklist = "/jitsu/data/blacklist.txt";
	public $path_to_excludes = "/jitsu/data/excludes.txt";
	
	// GeoIP
	public $path_to_geoip = "geoip/geoip.inc";
	public $path_to_geoipcity = "geoip/geoipcity.inc";
	public $path_to_geoipregionvars = "geoip/geoipregionvars.php";
	public $path_to_geolitecity = "/jitsu/geoip/GeoLiteCity.dat";

	private $dbtable = "jitsu";
	
	private $password = "P@SSW0RD";
	
	// Set this to true to filter out attacks; RECOMMENDED ON!
	private $filterQuery = true;
	
	// Set this to true if you want to kill the connection apon payload detection
	private $killConnection = true;
	
	/**
	 * This is the construct method which gets initialized with each class call,
	 * all the settings and configs that occur before site load happens here.
	 */
	public function __construct()
    {		
		// Check if IP banned
		if(self::ifUserBanned(self::getIPAddress()))
		{
			die(self::getIPAddress()." Banned.");
		}
		
		$filter_rules = @file_get_contents(dirname(__DIR__).$this->filter_rules_json, FILE_USE_INCLUDE_PATH);
		if($filter_rules === FALSE) 
		{
			exit("No filter file found or unable to read file correctly.");
		}
		else
		{
			$this->filter_rules = json_decode($filter_rules,true);
		}
		
		$firewall = self::firewall($_GET, $_POST);
		
		if(!empty($firewall))
		{
			// Log event
			self::log($firewall);
			
			// If filter query is set true
			if($this->filterQuery)
			{
				$_GET = self::filter($_GET);
				$_POST = self::filter($_POST);
			}
			
			// If kill connection is set true
			if($this->killConnection)
			{
				die();
			}
			
		}
    }
	
	/**
	 * ifUserBanned
	 */
	public function ifUserBanned ($data)
	{
		// If data is set then run
		if(isset($data))
		{
			$file = fopen(dirname(__DIR__).$this->path_to_blacklist, "r") or exit("Unable to open file!");
			while(!feof($file))
			{
				if(trim(fgets($file)) == $data)
				{
					return true;
				}
			}
			fclose($file);
		}
		
	}
	
	public function ifExcluded ($data)
	{
		// If data is set then run
		if(isset($data))
		{
			$file = fopen(dirname(__DIR__).$this->path_to_excludes, "r") or exit("Unable to open file!");
			while(!feof($file))
			{
				if(trim(fgets($file)) == $data)
				{
					return true;
				}
			}
			fclose($file);
		}
		
	}
	
	public function sessionCheck($session)
	{
		if($session == crc32($this->password))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function login($password)
	{
		if($password == $this->password)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function clearLogs()
	{
		$db = self::db();
		$sql = "TRUNCATE `".$this->dbtable."`";
		if(!$result = $db->query($sql))
		{
			die('There was an error running the query [' . $db->error . ']');
		}
	}
	
	public function addToBlacklist ($data)
	{
		// If data is set then run
		if(isset($data))
		{
			$handle = fopen(dirname(__DIR__).$this->path_to_blacklist, 'a') or die('Cannot open file:  '.$eventFileName);
			fwrite($handle, $data.PHP_EOL);
			fclose($handle);
		}
		
	}
	
	public function getBlacklistHTML ()
	{
		$file = fopen(dirname(__DIR__).$this->path_to_blacklist, "r") or exit("Unable to open file!");
		$blacklistHTML = "<div class=\"well well-sm\">";
		$i = 0;
		while(!feof($file))
		{
			$ip = trim(fgets($file));
			$blacklistHTML .= $ip."<br />";
			$i++;
		}
		if($i==1){$blacklistHTML .= "Nothing to see here!";}
		fclose($file);
		$blacklistHTML .= "</div>";
		return $blacklistHTML;
	}
	
	public function getAttackHistoryHTML()
	{
		$db = self::db();
		
		$sql = "SELECT * FROM `".$this->dbtable."`";
	
		if(!$result = $db->query($sql))
		{
			die('There was an error running the query [' . $db->error . ']');
		}
		
		$resultHTML = "<table id=\"example\" class=\"table table-striped table-bordered dt-responsive nowrap\" cellspacing=\"0\" width=\"100%\">";
		$resultHTML .= "<thead><tr>";
		$resultHTML .= "<th>Method</th>";
		$resultHTML .= "<th>Description</th>";
		$resultHTML .= "<th>Tags</th>";
		$resultHTML .= "<th>Key</th>";
		$resultHTML .= "<th>Value</th>";
		$resultHTML .= "<th>Impact</th>";
		$resultHTML .= "<th>Date/Time</th>";
		$resultHTML .= "<th>Ip</th>";
		$resultHTML .= "<th>RuleREGEX</th>";
		$resultHTML .= "<th>OS</th>";
		$resultHTML .= "<th>Browser</th>";
		$resultHTML .= "<th>User Agent</th>";
		$resultHTML .= "<th>Referrer</th>";
		$resultHTML .= "<th>URL</th>";
		$resultHTML .= "<th>Lat.</th>";
		$resultHTML .= "<th>Long.</th>";
		$resultHTML .= "<th>City</th>";
		$resultHTML .= "<th>Region</th>";
		$resultHTML .= "<th>Country</th>";
		$resultHTML .= "</tr></thead><tbody>";
		
		while($row = $result->fetch_assoc())
		{
			$resultHTML .= "<tr>
				<td>".$row["method"]."</td>
				<td>".$row["description"]."</td>
				<td>".$row["tags"]."</td>
				<td>".htmlentities($row["key"])."</td>
				<td>".htmlentities($row["value"])."</td>
				<td>".$row["impact"]."</td>
				<td>".$row["datetime"]."</td>
				<td>".htmlentities($row["ip"])."</td>
				<td>".$row["rule"]."</td>
				<td>".htmlentities($row["os"])."</td>
				<td>".htmlentities($row["browser"])."</td>
				<td>".htmlentities($row["user_agent"])."</td>
				<td>".htmlentities($row["referrer"])."</td>
				<td>".htmlentities($row["page_url"])."</td>
				<td>".$row["lat"]."</td>
				<td>".$row["long"]."</td>
				<td>".$row["city"]."</td>
				<td>".$row["region"]."</td>
				<td>".$row["country"]."</td>
			</tr>";
		}
		
		$resultHTML .= "</tbody></table>";
		return $resultHTML;
	}
	
	private function db ()
	{
		require(dirname(__DIR__)."/db.php");
		return $db;
	}
	
	private function log($data)
	{
		$db = self::db();
		
		require_once($this->path_to_geoip);
		require_once($this->path_to_geoipcity);
		require_once($this->path_to_geoipregionvars);
		
		$gi = geoip_open(dirname(__DIR__).$this->path_to_geolitecity, GEOIP_STANDARD);

		foreach($data as $bit)
		{
			$method = $bit["method"];
			$key = $db->escape_string($bit["key"]);
			$value = $db->escape_string($bit["value"]);
			$ruleID = $bit["ruleID"];
			$rule = $bit["rule"];
			$description = $bit["description"];
			$tags = "";
			if(is_array($bit["tags"]))
			{
				foreach($bit["tags"] as $tag_key => $tag_value)
				{
					$tags .= $tag_value .",";
				}
			}
			else
			{
				$tags = $bit["tags"];
			}
			$tags = rtrim($tags, ",");
			$impact = $bit["impact"];
			$ip = $db->escape_string(self::getIPAddress());
			$os = $db->escape_string(self::getOS());
			$browser = $db->escape_string(self::getBrowser());
			$datetime = date("Y-m-d H:i:s");
			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = $db->escape_string($url);
			$userAgent = $db->escape_string($_SERVER['HTTP_USER_AGENT']);
			if(isset($_SERVER['HTTP_REFERER']))
			{
				$referrer = $db->escape_string($_SERVER['HTTP_REFERER']);
			}
			else
			{
				$referrer = "";
			}
			
			$rsGeoData = geoip_record_by_addr($gi, $ip);
			if($rsGeoData)
			{
				$lat = $rsGeoData->latitude;
				$long = $rsGeoData->longitude;
				$city = $rsGeoData->city;
				$region = $rsGeoData->region;
				$country = $rsGeoData->country_name;
			}
			else
			{
				$lat = 0;
				$long = 0;
				$city = "";
				$region = "";
				$country = "";
			}
			
			
			$statement = $db->prepare("INSERT INTO `".$this->dbtable."` (`method`,`key`,`value`,`ruleID`,`rule`,`description`,`tags`,`impact`,`ip`,`os`,`browser`,`user_agent`,`referrer`,`datetime`,`page_url`,`lat`,`long`,`city`,`region`,`country`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			$statement->bind_param('sssisssissssssssssss', $method, $key, $value, $ruleID, $rule, $description, $tags, $impact, $ip, $os, $browser, $userAgent, $referrer, $datetime, $url, $lat, $long, $city, $region, $country);
			$statement->execute();
			$statement->close(); 
			
		}
		geoip_close($gi);
	}
	
	/**
	  * Retrieves the best guess of the client's actual IP address.
	  * Takes into account numerous HTTP proxy headers due to variations
	  * in how different ISPs handle IP addresses in headers between hops.
	  */
	 public function getIPAddress() 
	 {
		$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
		foreach ($ip_keys as $key)
		{
			if (array_key_exists($key, $_SERVER) === true)
			{
				foreach (explode(',', $_SERVER[$key]) as $ip)
				{
					$ip = trim($ip);
				}
			}
		}
		return $ip;
	 }
	 
	 private function getOS()
	 { 
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$os_platform = "Unknown OS Platform";
		$os_array = array(
			'/windows nt 10/i'     =>  'Windows 10',
			'/windows nt 6.3/i'     =>  'Windows 8.1',
			'/windows nt 6.2/i'     =>  'Windows 8',
			'/windows nt 6.1/i'     =>  'Windows 7',
			'/windows nt 6.0/i'     =>  'Windows Vista',
			'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     =>  'Windows XP',
			'/windows xp/i'         =>  'Windows XP',
			'/windows nt 5.0/i'     =>  'Windows 2000',
			'/windows me/i'         =>  'Windows ME',
			'/win98/i'              =>  'Windows 98',
			'/win95/i'              =>  'Windows 95',
			'/win16/i'              =>  'Windows 3.11',
			'/macintosh|mac os x/i' =>  'Mac OS X',
			'/mac_powerpc/i'        =>  'Mac OS 9',
			'/linux/i'              =>  'Linux',
			'/ubuntu/i'             =>  'Ubuntu',
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile'
		);
	
		foreach ($os_array as $regex => $value)
		{ 
			if (preg_match($regex, $user_agent))
			{
				$os_platform    =   $value;
			}
	
		}   
		return $os_platform;
	}
	 
	 
	
	 private function getBrowser()
	 {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browser = "Unknown Browser";
		$browser_array  =  array(
			'/msie/i'       =>  'Internet Explorer',
			'/firefox/i'    =>  'Firefox',
			'/safari/i'     =>  'Safari',
			'/chrome/i'     =>  'Chrome',
			'/edge/i'       =>  'Edge',
			'/opera/i'      =>  'Opera',
			'/netscape/i'   =>  'Netscape',
			'/maxthon/i'    =>  'Maxthon',
			'/konqueror/i'  =>  'Konqueror',
			'/mobile/i'     =>  'Handheld Browser'
		);
	
		foreach ($browser_array as $regex => $value)
		{ 
			if (preg_match($regex, $user_agent))
			{
				$browser    =   $value;
			}
	
		}
		return $browser;
	}
	
	private function filter ($DATA)
	{
		$NEWDATA = array();
		if(!empty($DATA))
		{
			foreach($DATA as $key => $value)
			{
				foreach($this->filter_rules["filters"] as $filter)
				{
					if(!is_array($key))
					{
						$excluded = self::ifExcluded($key);
						if($excluded)
						{
							continue;
						}
						$key = strtolower($key);
					}
					
					if(!is_array($value))
					{
						$value = strtolower($value);
						$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key);
						$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value);
	
						if($isMatchedKey)
						{
							$key = preg_replace('/' . $filter["rule"] . '/ms', "", $key);
						}
						if($isMatchedValue)
						{
							$value = preg_replace('/' . $filter["rule"] . '/ms', "", $value);
						}
					}
					elseif(is_array($value))
					{
						foreach($value as $key2 => $value2)
						{
							$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key2);
							$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value2);
		
							if($isMatchedKey)
							{
								$key = preg_replace('/' . $filter["rule"] . '/ms', "", $key2);
							}
							if($isMatchedValue)
							{
								$value = preg_replace('/' . $filter["rule"] . '/ms', "", $value2);
							}
						}
					}
					
				}
				$NEWDATA[$key] = $value;
			}
			return $NEWDATA;
		}
	}
	
	private function firewall ($GET, $POST)
	{
		$resultArray = array();
		
		if(!empty($GET))
		{
			foreach($GET as $key => $value)
			{
				foreach($this->filter_rules["filters"] as $filter)
				{
					if(!is_array($key))
					{
						$excluded = self::ifExcluded($key);
						if($excluded)
						{
							continue;
						}
						$key = strtolower($key);
					}
					if(is_array($value))
					{
						foreach($value as $key2 => $value2)
						{
							$value = strtolower($value2);
							$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key2);
							$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value2);
												
							if($isMatchedKey || $isMatchedValue)
							{
								$resultArray[] = array(
									"method"=>"GET",
									"key"=>$key2,
									"value"=>$value2,
									"ruleID"=>$filter["id"],
									"rule"=>$filter["rule"],
									"description"=>$filter["description"],
									"tags"=>$filter["tags"]["tag"],
									"impact"=>$filter["impact"]
								); 
							}
						}
					}
					elseif(!is_array($value))
					{
						$value = strtolower($value);
						$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key);
						$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value);
											
						if($isMatchedKey || $isMatchedValue)
						{
							$resultArray[] = array(
								"method"=>"GET",
								"key"=>$key,
								"value"=>$value,
								"ruleID"=>$filter["id"],
								"rule"=>$filter["rule"],
								"description"=>$filter["description"],
								"tags"=>$filter["tags"]["tag"],
								"impact"=>$filter["impact"]
							); 
						}
					}
					
				}
				//echo "key: ".$key." value:".$value;
			}
			
		}
		
		if(!empty($POST))
		{
			foreach($POST as $key => $value)
			{
				foreach($this->filter_rules["filters"] as $filter)
				{
					if(!is_array($key))
					{
						$excluded = self::ifExcluded($key);
						if($excluded)
						{
							continue;
						}
						$key = strtolower($key);
					}
					
					if(is_array($value))
					{
						foreach($value as $key2 => $value2)
						{
							$value = strtolower($value2);
							$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key2);
							$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value2);
												
							if($isMatchedKey || $isMatchedValue)
							{
								$resultArray[] = array(
									"method"=>"GET",
									"key"=>$key2,
									"value"=>$value2,
									"ruleID"=>$filter["id"],
									"rule"=>$filter["rule"],
									"description"=>$filter["description"],
									"tags"=>$filter["tags"]["tag"],
									"impact"=>$filter["impact"]
								); 
							}
						}
					}
					elseif(!is_array($value))
					{
						$value = strtolower($value);
						$isMatchedKey = (bool) preg_match('/' . $filter["rule"] . '/ms', $key);
						$isMatchedValue = (bool) preg_match('/' . $filter["rule"] . '/ms', $value);
											
						if($isMatchedKey || $isMatchedValue)
						{
							$resultArray[] = array(
								"method"=>"GET",
								"key"=>$key,
								"value"=>$value,
								"ruleID"=>$filter["id"],
								"rule"=>$filter["rule"],
								"description"=>$filter["description"],
								"tags"=>$filter["tags"]["tag"],
								"impact"=>$filter["impact"]
							); 
						}
					}
					
				}
				//echo "key: ".$key." value:".$value;
			}
		}
		
		return $resultArray;
		
	}

}
?>
