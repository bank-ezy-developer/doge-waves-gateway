<?php
require_once'auth.php';
require_once('db/db.php');
require_once('db/users2.php');

if($Logged_In!==20) {
   header("Location: index.php");
}

if($_SESSION['state'] == 1)
{
  $sessionstate = 1; 
}

if(isset($_POST['data1'])){
  $sessionstate = $_POST['data1'];
  $_POST['data1'] = array();
  $_SESSION['state'] = $sessionstate;
}

$userID = Users2::LoggedUserID();
$userData = Users2::GetUserData($userID);


function CheckIfWavesAddressValid($wavesaddresstocheck)
{

  $ch = curl_init(); 
  // set url 
  curl_setopt($ch, CURLOPT_URL, "http://localhost:9966/getwavesbalanceaddress/" . $wavesaddresstocheck); 
  //return the transfer as a string 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
  // $output contains the output string 
  $addressvalidreturn = curl_exec($ch);
  curl_close($ch);
  return $addressvalidreturn;
}



function SendDogeTokenstoUserWallet($userid)
{

  $ch = curl_init(); 

  // set url 
  curl_setopt($ch, CURLOPT_URL, "http://localhost:9966/senddogeuser/" . $userid); 
  //return the transfer as a string 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
  // $output contains the output string 
  $amountsent = curl_exec($ch);
  curl_close($ch);
  $amountsent = $amountsent/100000000;
  return $amountsent;
}

function SetDogeTokenUserAccount($userid)
{

  $url = "http://localhost:9966/account/" . $userid;
  if ($_SESSION['dogecoin_account_balance'] > 50000)
  {
        $amounttosend = 50000;
  }
  else
  {
       $amounttosend = $_SESSION['dogecoin_account_balance'];
  }

  $amounttosend = $amounttosend - 5 - $amounttosend*0.0025;
  $_SESSION['amountcoinssent'] = $amounttosend;
  $amountsatoshis = $amounttosend*100000000;
  $jsondata = '{"address" : "' . $_SESSION['dogetokenreceiveaddress'] .'", "amount" : "' . $amountsatoshis . '"}';
  $_SESSION['debug'] = $jsondata;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}


function GetNewWavesAccount()
{
  $ch = curl_init(); 

  // set url 
  curl_setopt($ch, CURLOPT_URL, "http://localhost:9966/newaccount"); 
  //return the transfer as a string 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
  // $output contains the output string 
  $newaccount_result = curl_exec($ch);
  curl_close($ch);
  $newaccount = json_decode($newaccount_result, TRUE); // Set second argument as TRUE
  $userIDstring = $_SESSION['user_id'];
  $url = "http://localhost:9966/account/" . $userIDstring;
  $newaccountstring = $newaccount['address'];
  $jsondata = '{"address" : "' . $newaccountstring .'"}';
  $_SESSION['debug'] = $jsondata;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
  $response = curl_exec($ch);
  curl_close($ch);
  return $newaccount;
}

if ($_SESSION['new_waves_address'] == false)
{
  $wavesaccount = GetNewWavesAccount();
  $wavesaddress = $wavesaccount['address'];
  $wavesaddress = str_replace('"', '', $wavesaddress);
  $privatekey = $wavesaccount['privatekey'];
  $privatekey = str_replace('"', '', $privatekey);
  Users2::SetWavesAddress($userID, $wavesaddress);
  Users2::SetWavesPrivatekey($userID, $privatekey);
  $_SESSION['wavesaddress'] = $wavesaddress;
  $_SESSION['new_waves_address'] = true;
  header("Location: cointoken.php");
}

$errormsg = Users2::GetError($userID);
$errormsgstatus = Users2::GetErrorStatus($userID);
if ($errormsgstatus['Display'] == 1)
{
   $withdraw_message = $errormsg['Errors'];
   $errormsg = Users2::ClearError($userID);
}


if ($_SESSION['new_coin_address'] == false)
{  
  $Dogecoind_accountaddress = $Dogecoind->getnewaddress($wallet_id);
  $_SESSION['dogecoinaddress'] = $Dogecoind_accountaddress;
  Users2::SetDogeAddress($userID, $Dogecoind_accountaddress);
  $_SESSION['new_coin_address'] = true;
  header("Location: cointoken.php");
}


$exchange_address = addslashes(strip_tags($_POST['address']));
if (!empty($exchange_address)) 
{
      $exchange_address = str_replace(' ', '', $exchange_address );
      $addressvalid = CheckIfWavesAddressValid($exchange_address);
      $addressvalid = str_replace('"', '', $addressvalid );     
      if ($addressvalid != "error")
      {
          $_SESSION['state'] = 2;
          $_SESSION['dogetokenreceiveaddress'] = $exchange_address;
          $tokens_sent = false;
          $waves_sent = false; 
          $_SESSION['currentstatetext'] = "Waiting for doge Coin Deposit 3 Confirmations required ...";
          $_SESSION['invalidaddress'] = "";
      }
      else
      {
        $_SESSION['invalidaddress'] = "Invalid doge Token address! Please enter a valid doge Token address!";
      }
      header("Location: cointoken.php");
}


if ($_SESSION['state'] == 2)
{
    
    $Dogecoind_Balance = $Dogecoind->getbalance($wallet_id,3);
    $_SESSION['dogecoin_account_balance'] = $Dogecoind_Balance;
    $_SESSION['state'] = 9;
    header("Location: cointoken.php");
}

if ($_SESSION['state'] == 3)
if (!empty($_SESSION['dogetokenreceiveaddress']))
{
{
  $_SESSION['state'] = 4;
  if ($waves_sent == false)
  {
    if ($_SESSION['dogecoin_account_balance'] >= 50)
    {
                $a = SetDogeTokenUserAccount($_SESSION['user_id']);
                $waves_sent = true; 
                $_SESSION['currentstatetext'] = "Received " . $AmountCoins . " doge Coins - Exchanging ...";
                header("Location: cointoken.php");    
    }
    else 
    {
          $_SESSION['state'] = 100;
    }

  }
   
}
}

if ($_SESSION['state'] == 4)
{
    $Dogecoind_Balance = $Dogecoind->getbalance($wallet_id,3);
    $Dogecoind_Withdraw_From = $Dogecoind->move($wallet_id, "newlycreated(main_doge_wallet)", (float)$_SESSION['dogecoin_account_balance'],3);
    $_SESSION['state'] = 11;
    header("Location: cointoken.php");
}


if ($_SESSION['state'] == 5){
if (!empty($_SESSION['dogetokenreceiveaddress']))
{
   $_SESSION['state'] = 6;
   $b = SendDogeTokenstoUserWallet($_SESSION['user_id']);
   $AmountCoins = $_SESSION['dogecoin_account_balance'];
   $_SESSION['currentstatetext'] = "";
   $_SESSION['laststatetext'] = "Transaction Complete";
  header("Location: cointoken.php");
}
}

if ($_SESSION['state'] == 100){
if (!empty($_SESSION['dogetokenreceiveaddress']))
{
   $Dogecoind_Balance = $Dogecoind->getbalance($wallet_id,3);
   $Dogecoind_Withdraw_From = $Dogecoind->move($wallet_id, "newlycreated(main_doge_wallet)", (float)$_SESSION['dogecoin_account_balance'],3);
   $_SESSION['currentstatetext'] = "";
   $_SESSION['laststatetext'] = "Transaction Failed! Less than minimum!";
   $_SESSION['state'] = 1000;
   header("Location: cointoken.php");
}
}


?>
<html>
<head>
   <title>Doge Token to Doge Coin Exchange 
   </title>
   <style>
      body { background: #bcdfe5; color: #000000; font-family: times; font-size: 14px; margin: 0px; padding: 0px; }
      hr { height: 1px; background: #0b4c61; }
      table { font-size: 14px; }
      a { text-decoration: none; color: #0b4c61; }
      input { height: 22px; border: 1px solid #0b4c61; border-radius: 6px; -moz-border-radius: 6px; }
      .button { height: 66px; background: #0b4c61; border: 1px solid #0b4c61; color: #FFFFFF; font-weight: bold; border-radius: 6px; -moz-border-radius: 6px; }
      #myProgress {
        width: 100%;
        background-color: #ddd;
      }

      #myBar {
        width: 10%;
        height: 30px;
        background-color: #0B6121;
        text-align: center;
        line-height: 30px;
        color: white;
      }
     .blink_text {

    animation:1s blinker linear infinite;
    -webkit-animation:1s blinker linear infinite;
    -moz-animation:1s blinker linear infinite;

     color: red;
    }
     .invalidaddress_text {
     color: red;
    }



    @-moz-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @-webkit-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }
   </style>
</head>
<body>
   <center>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
        var myVar = setInterval(myTimer, 10000);
        var session_state = <?php echo $_SESSION['state']; ?>;
        var newaccountdogebalance = <?php echo $_SESSION['dogecoin_account_balance']; ?>;     
        function myTimer() {
              session_state = <?php echo $_SESSION['state']; ?>;
              newaccountdogebalance = <?php echo $_SESSION['dogecoin_account_balance']; ?>;
              if (session_state == 100)
              {
                  session_state = 1000;
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              if (session_state == 9)
              {
                if (newaccountdogebalance == 0)
                {
                  session_state = 2;
                }
                else
                {
                  session_state = 3;
                }
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (session_state == 10)
              {
                session_state = 4;
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (session_state == 11)
              {
                  session_state = 5;
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (session_state == 5)
              {
                session_state = 6;
                clearTimeout(myTimer); 
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (session_state == 4)
              {
                session_state = 5;
                //clearTimeout(myTimer); 
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){           
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (session_state == 3)
              {
                session_state = 4;
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){          
                 }).fail(function(html){
                });
                location.reload();
              }
              else if (newaccountdogebalance > 0 && session_state == 2)
              {
                session_state = 3; 
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){            
                 }).fail(function(html){
                });
                location.reload();
              }
              else
              {
                $.ajax({
                  method: "POST",
                  async: false,
                  url: "cointoken.php",
                  data: { data1: session_state}
                   }).done(function(html){            
                 }).fail(function(html){
                });
                location.reload();
              }
          }       
    </script>

   <div align="center" style="width: 700px; background: #FFFFFF; font-weight: bold; border: 10px solid #0b4c61; padding:10px; border-radius: 15px; -moz-border-radius: 15px;">
   <table style="width: 650px;">

      <tr>
         <td colspan="2" align="left" valign="top" style="padding: 5px;" nowrap>
            <center>
            <h1>Doge Coin to Doge Token Exchange</h1>
            <form id="addressform" style="display:inline;" action="cointoken.php" method="POST">
            <table>
              <tr>
                <h2>Enter Doge Token address for receiving Doge tokens exchanged from Coins</h2>
                <br>
              </tr>
               <tr>
 
                  <td align="right" nowrap><b>Receive Doge Token Address</b></td>
                  <td align="left" nowrap><input id="addressinput" type="text" name="address" style="width: 360px;"></td>
                  <td align="left" nowrap><input type="submit" class="button" name="submit" value="Exchange"></td>
                  <div id="addresserror"><span class="invalidaddress_text"><?php echo $_SESSION['invalidaddress']; ?></span></div>
               </tr>
            </table>
          </form>
            </center>
            <hr>
            <center>
            <table>
               <tr>
                  <td align="left" style="padding: 3px;" nowrap>
                    <?php
                      if ($_SESSION['state'] ==1)
                      {
                          echo "<center><a href=https://localhost/logout.php>Cancel transaction and start over</a></center>";
                      }
                      if ($_SESSION['state'] ==2 || $_SESSION['state'] ==9 )
                      {
                        echo "<h2>Please send the coins you want to exchange to this address</h2>";
                        echo "<center><h1 style='color:Red;'>". $_SESSION['dogecoinaddress']. "</h1></center>";
                        echo "<center><h3>Maximum amount of coins you can exchange is 50000</h3></center>";
                        echo "<center><h3>Minimum amount of coins you can exchange is 50</h3></center>";
                        echo "<center><h3>Exchange fees are 5 doge Tokens + 0.25%<h3></center>";
                        echo "<h2>You will receive tokens to the following address</h2>";
                        echo "<center><h1 style='color:Red;'>". $_SESSION['dogetokenreceiveaddress']. "</h1></center>";
                        echo "<center><a href=https://localhost/logout.php>Cancel transaction and start over</a></center>";
                      }
                      if ($_SESSION['state'] ==3 || $_SESSION['state'] ==4 || $_SESSION['state'] ==5 || $_SESSION['state'] ==10 || $_SESSION['state'] ==11)
                      {
                        echo "<h2>Coins successfully received. You have sent: </h2>";
                        echo "<center><h1 style='color:Red;'>". $_SESSION['dogecoin_account_balance']. "</h1></center>";
                        echo "<h2>You will receive tokens to the following address</h2>";
                        echo "<center><h1 style='color:Red;'>". $_SESSION['dogetokenreceiveaddress']. "</h1></center>";
                      }
                      if ($_SESSION['state'] ==6 )
                      {
                        echo "<h2>Coins successfully exchanged for tokens</h2>";
                        echo "<center><h2>Amount of tokens sent</h3></center>";
                        echo "<center><h2 style='color:Red;'>". $_SESSION['amountcoinssent']. "</h2></center>";
                      }
                      if ($_SESSION['state'] ==100 || $_SESSION['state'] == 1000)
                      {
                        echo "<h2>You have sent less than the minimum amount of coins per transaction 50</h2>";
                        echo "<center><h2>Amount of coins sent</h3></center>";
                        echo "<center><h2 style='color:Red;'>". $_SESSION['dogecoin_account_balance']. "</h2></center>";
                        echo "<center><h2><a href=https://localhost/logout.php>Please click here to start over and send more than 50 coins</a></h2></center>";
                      }  
                    ?>
                  </td>
               </tr>
            </table>
            </center>
            <hr>
            <center><h2>Doge Coin to Token Exchange Progress</h2></center>
            <div id="currentstatetext">
            <center><h1><span class="blink_text"><?php echo $_SESSION['currentstatetext']; ?></span></h1></center>
            </div>
            <div id="laststatetext">
              <center><h1><?php echo $_SESSION['laststatetext']; ?></h1></center>
            </div>
            <div id="backtomain">
              <center><h1><a href="https://localhost/logout.php">Back to Main Menu!</a></h1></center>
            </div>
        </td>
      </tr>

   </table>
   </div>
   </center>
   <script type="text/javascript">
        if (<?php echo $_SESSION['state']; ?> > 1)
        {
          document.getElementById('addressform').style.visibility = 'hidden';
        } 
        if (<?php echo $_SESSION['state']; ?> == 6)
        {
          document.getElementById('backtomain').style.visibility = 'visible';
        } 
        else
        {
         document.getElementById('backtomain').style.visibility = 'hidden'; 
        }
   </script>
</body>
</html>
<?php require'footer.php'; ?>
