<?php
session_start();
error_reporting(0);
require_once'jsonRPCClient.php';

// function by zelles to modify the number to bitcoin format ex. 0.00120000
function satoshitize($satoshitize) {
   return sprintf("%.8f", $satoshitize);
}

// function by zelles to trim trailing zeroes and decimal if need
function satoshitrim($satoshitrim) {
   return rtrim(rtrim($satoshitrim, "0"), ".");
}

$server_url = $_SERVER['SERVER_NAME'];  // website url
$ip = $_SERVER['REMOTE_ADDR'];          // get visitors ip address
$date = date("n/j/Y g:i a");;           // get the current date and time

$dbhst = "localhost";       // database host
$dbusr = "dbuser";   // database username
$dbpwd = "dbpassword";   // database password
$dbtbl = "dbtable";   // database name

// connect to the database
$db_handle = mysql_connect($dbhst, $dbusr, $dbpwd)or die("cannot connect");
$db_found = mysql_select_db($dbtbl)or die("cannot select DB");

   $RPC_Host = "hostaddress";         // host for bitcoin rpc
   $RPC_Port = "rpcport";              // port for bitcoin rpc
   $RPC_User = "rpcusername";     // username for bitcoin rpc
   $RPC_Pass = "rpcpassword";     // password for bitcoin rpc
   
   // dont change below here
   $somerandomvarialbe = "http://".$RPC_User.":".$RPC_Pass."@".$RPC_Host.":".$RPC_Port."/";
   $Dogecoind = new jsonRPCClient($somerandomvarialbe);

$user_session = $_SESSION['user_session'];  // set session and check if logged in
if(!$user_session) {
   $Logged_In = 99;
} else {
   $Logged_In = 20;


   $RPC_Host = "hostaddress";         // host for bitcoin rpc
   $RPC_Port = "rpcport";              // port for bitcoin rpc
   $RPC_User = "rpcusername";     // username for bitcoin rpc
   $RPC_Pass = "rpcpassword";     // password for bitcoin rpc
   
   // dont change below here
   $somerandomvarialbe = "http://".$RPC_User.":".$RPC_Pass."@".$RPC_Host.":".$RPC_Port."/";
   $Dogecoind = new jsonRPCClient($somerandomvarialbe);
   $wallet_id = "newlycreated(".$user_session.")";
   $Dogecoind_Balance = $Dogecoind->getbalance($wallet_id,3);
   $Dogecoind_accountaddresses = $Dogecoind->getaddressesbyaccount($wallet_id);
   $Dogecoind_List_Transactions = $Dogecoind->listtransactions($wallet_id,10);
}
?>
