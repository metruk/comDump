<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */

function checker($ip,$high_ip,$low_ip,$operator,$country){
	
    if ($ip <= $high_ip && $low_ip <= $ip) {
        writeToFileMobile(mobiles.csv,$operator,$country,$ip);
    }else{
		//writeUser(usual.csv,$ip);
		//writeToFileMobile(mobiles.csv,$operator,$country,$ip);
    }
}

function writeToFileMobile($filename,$operator,$country,$userIp){
    $currentHours = date('H:i');
    $myfile = file_put_contents($filename, $operator.";".$country.";".$userIp.";".$currentHours."\n" , FILE_APPEND | LOCK_EX);
}

function writeUser($filename,$userIp){
    $currentHours = date('H:i');
    $myfile = file_put_contents($filename, $userIp.";".$currentHours."\n" , FILE_APPEND | LOCK_EX);
}

function readXml($convertedIp){
    $url = "custom/operators.xml";
    $xml = simplexml_load_file($url) or die("feed not loading");
    foreach ($xml as $value){
        $operator=$value->attributes()->name;
        $country=$value->attributes()->country;

        foreach ($value as $v){
            $ip1=$v->attributes()->ip1;
            //print $ip1."\n";
            $ip2=$v->attributes()->ip2;
            //print $ip2."\n";
            checker($convertedIp,$ip2,$ip1,$operator,$country);
        }
       // print "-----"."\n";
    }

}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$convertedIp= ip2long($ipAddress);

readXml($convertedIp);

define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );