<?php
require_once'auth.php';
if($Logged_In===20 && $_SESSION['direction']==2) 
{
  header("Location: tokencoin.php");
}
if($Logged_In===20 && $_SESSION['direction']==1)
{
  header("Location: cointoken.php");
}

   // create curl resource 
$ch = curl_init(); 

// set url 
curl_setopt($ch, CURLOPT_URL, "http://localhost:9966/wavesbalance"); 
//return the transfer as a string 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
// $output contains the output string 
$wavesbalance = curl_exec($ch); 
$wavesbalance = $wavesbalance/100000000;
// close curl resource to free up system resources 
curl_close($ch);

$ch = curl_init(); 
// set url 
curl_setopt($ch, CURLOPT_URL, "http://localhost:9966/dogebalance"); 
//return the transfer as a string 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

// $output contains the output string 
$dogetokenbalance = curl_exec($ch); 
$dogetokenbalance = $dogetokenbalance/100000000;
// close curl resource to free up system resources 
curl_close($ch);

$dogeCoin_Balance = $Dogecoind->getbalance("dogecoinwallet",3);

$form_action = $_POST['action'];
if($form_action=="cointoken") {
$myusername = random_username();
$mypassword = random_password();
   if($myusername) {
      if($mypassword) {
            $uLength = strlen($myusername);
            $pLength = strlen($mypassword);
            if($uLength >= 3 && $uLength <= 30) {
               $return_error = "";
            } else {
              // $return_error = $return_error . "Username must be between 3 and 30 characters" . "<BR>";
            }
            if($pLength >= 3 && $pLength <= 30) {
              // $return_error = "";
            } else {
              // $return_error = $return_error . "Password must be between 3 and 30 characters" . "<BR>";
            }
           // if($return_error == "") {
               if($db_found) {
                  $myusername = addslashes(strip_tags($myusername));
                  $mypassword = md5(addslashes(strip_tags($mypassword)));
                  $SQL = "SELECT * FROM users2";
                  $result = mysql_query($SQL);
                  $num_tot_rows = mysql_num_rows($result);
                  $last_id = mysql_query("SELECT MAX(id) FROM users2");
                  $last_id_row = mysql_fetch_array($last_id);
                  $next_id = $last_id_row['MAX(id)'] + 1;
                  $SQL = "SELECT * FROM users2 WHERE username='$myusername'";
                  $result = mysql_query($SQL);
                  $num_rows = mysql_num_rows($result);
                  if($num_rows==1) {
                   //  $return_error = "Username already taken.";
                  } else 
                  {
                     if(!mysql_query("INSERT INTO users2 (id,date,ip,username,password,dogeaddress, wavesaddress, privatekey) VALUES ('$next_id','$date','$ip','$myusername','$mypassword', '', '', '')")) 
                     {
                        //$return_error = "System error.";
                     } else {
                        $return_error = "Logged in.";
                        $_SESSION['direction'] = 1;
                        $_SESSION['user_session'] = $myusername;
                        $_SESSION['user_id'] = $next_id;
                        $_SESSION['new_coin_address'] = false;
                        $_SESSION['new_waves_address'] = false;
                        $_SESSION['state'] = 1;
                        $_SESSION['send_doge'] = 0;
                        $_SESSION['dogetoken_account_balance'] = 0;
                        $_SESSION['waves_account_balance'] = 0;
                        $Logged_In = 7;
                        header ("Location: index.php");
                     }
               }
            }
         //}
      }
   }
}



if($form_action=="tokencoin") {
$myusername = random_username();
$mypassword = random_password();
   if($myusername) {
      if($mypassword) {
            $uLength = strlen($myusername);
            $pLength = strlen($mypassword);
            if($uLength >= 3 && $uLength <= 30) {
               $return_error = "";
            } else {
              // $return_error = $return_error . "Username must be between 3 and 30 characters" . "<BR>";
            }
            if($pLength >= 3 && $pLength <= 30) {
              // $return_error = "";
            } else {
              // $return_error = $return_error . "Password must be between 3 and 30 characters" . "<BR>";
            }
           // if($return_error == "") {
               if($db_found) {
                  $myusername = addslashes(strip_tags($myusername));
                  $mypassword = md5(addslashes(strip_tags($mypassword)));
                  $SQL = "SELECT * FROM users";
                  $result = mysql_query($SQL);
                  $num_tot_rows = mysql_num_rows($result);
                  $last_id = mysql_query("SELECT MAX(id) FROM users");
                  $last_id_row = mysql_fetch_array($last_id);
                  $next_id = $last_id_row['MAX(id)'] + 1;
                  $SQL = "SELECT * FROM users WHERE username='$myusername'";
                  $result = mysql_query($SQL);
                  $num_rows = mysql_num_rows($result);
                  if($num_rows==1) {
                   //  $return_error = "Username already taken.";
                  } else 
                  {
                     if(!mysql_query("INSERT INTO users (id,date,ip,username,password, dogeaddress, wavesaddress, privatekey) VALUES ('$next_id','$date','$ip','$myusername','$mypassword', '', '', '')")) 
                     {
                        //$return_error = "System error.";
                     } else {
                        $return_error = "Logged in.";
                        $_SESSION['direction'] = 2;
                        $_SESSION['user_session'] = $myusername;
                        $_SESSION['user_id'] = $next_id;
                        $_SESSION['new_coin_address'] = false;
                        $_SESSION['new_waves_address'] = false;
                        $_SESSION['state'] = 1;
                        $_SESSION['send_doge'] = 0;
                        $_SESSION['dogetoken_account_balance'] = 0;
                        $_SESSION['waves_account_balance'] = 0;
                        $Logged_In = 7;
                        header ("Location: index.php");
                     }
               }
            }
         //}
      }
   }
}

function random_username()
{
 $new_name = bin2hex(openssl_random_pseudo_bytes(32));
 check_user_name($new_name);
 return $new_name; 
}

function random_password()
{
 $new_name = bin2hex(openssl_random_pseudo_bytes(32));
 check_user_name($new_name);
 return $new_name; 
}

function check_user_name($new_name)
{
 $select = mysql_query("select * from users where username='$new_name'");

 if(mysql_num_rows($select))
 {
  random_username();
  return false;
 }
 else
 {
  //echo $new_name;
  return true;
 }
}

?>
<html>
<head>
   <title>Doge Token Coin Exchange </title>
   <style>
      body { background: #bcdfe5; color: #000000; font-family: times; font-size: 14px; margin: 0px; padding: 0px; }
      table { font-size: 14px; }
      a { text-decoration: none; color: #148fb7; }
      input { height: 22px; border: 1px solid #148fb7; border-radius: 6px; -moz-border-radius: 6px; }
      .button { height: 200px; background: #0b4c61; border: 1px solid #0b4c61; color: #FFFFFF; font-weight: bold; border-radius: 15px; -moz-border-radius: 6px; text-align: center;   box-shadow: 0 9px #999; font-size: 24px; cursor: pointer; outline: none; white-space: normal;}

      .button:hover {background-color: #148fb7}

      .button:active {
        background-color: #148fb7;
        box-shadow: 0 5px #666;
        transform: translateY(4px);
      }
   </style>
</head>
<body>
</script>
   <center>
    <div>
    <p><a href="https://localhost/index.php">
      <center><img align="middle" border="0" alt="doge Exchange" src="DOGE.png" width="90" height="90"></center>
    </span>
     <center><h1>Doge Token to coin and doge Coin to token exchange</h1></center>
      </a>
      </p>
   <p></p>
   <div align="center" style="width: 700px; background: #FFFFFF; font-weight: bold; border: 4px solid #148fb7; padding:10px; border-radius: 15px; -moz-border-radius: 15px;">
     
<!--   <input type="hidden" name="action" value="cointoken"> -->
   <table style="width: 100%; height: 50px;">
     <tr>
         <td align="middle">
          <form action="index.php" method="POST">
            <input type="hidden" name="action" value="tokencoin">
            <input type="submit" class="button" name="submit" value="doge tokens to coins" width="200" height="200"></td>
          </form>
         <td align="middle">
            <form action="index.php" method="POST">
            <input type="hidden" name="action" value="cointoken">
            <input type="submit" class="button" name="submit" value="doge coins to tokens" width="200" height="200"></td>
          </form>
     </tr>
   </table>
   </div>
   <p></p>
   </center>
</body>
</html>
<?php require'footer.php'; ?>

