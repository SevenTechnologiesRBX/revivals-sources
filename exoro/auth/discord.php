<?php
session_start();
$host = "localhost";
$dbname = "exoro_database";
$dbuser = "exoro_owner";
$dbpass = "codexisfat";

try {
    $db = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $dbuser, $dbpass, array(
    PDO::ATTR_PERSISTENT => true
));
// set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e)
    {
    echo "Internal Error: " . $e->getMessage();
    }

    $logged = false;
    
    if(isset($_SESSION['loggedin'])){
    $logged = true;
    
    $lid = $_SESSION['id'];
    $usrquery = $db->query("SELECT * FROM users WHERE id = '$lid'");
    $usr = $usrquery->fetch();
    
    if(!$usr){
    echo "An unexpected error occured.";
    }
    
    $uID = $usr['id'];
    }
    
if(!$logged && $usr['approved'] == "no"){header('Location: index.php');}else{
$code = $_GET['code'];
if(!isset($code)) {die("ERR");}
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://discord.com/api/v8/oauth2/token");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
    "client_id=901530916973871206&client_secret=JH0nXDQhqYy-wHOUwcWCEW3M2ZOtk9dx&grant_type=authorization_code&code=".$code."&redirect_uri=https%3A%2F%2Fwww.exoro.cf%2Fauth%2Fdiscord.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = json_decode(curl_exec($ch));
curl_close($ch);
if(!isset($server_output->access_token) || $server_output->scope != "identify guilds") {die("Looks like something went wrong.");}
$curl = curl_init();
$headers = array(
    "Authorization: Bearer ".$server_output->access_token
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, 'https://discord.com/api/v8/users/@me/guilds');
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
$out = json_decode(curl_exec($curl));
curl_close($curl);
foreach($out as $guild) {
    if($guild->id == "900841091421573220") {
        $db->query("UPDATE users SET approved = 'yes' WHERE id = $uID");

        $curl = curl_init();
        $headers = array(
        "Authorization: Bearer ".$server_output->access_token
       );
       curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($curl, CURLOPT_URL, 'https://discord.com/api/v8/users/@me');
       curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
       $out2 = json_decode(curl_exec($curl));
       curl_close($curl); 

        $db->query("UPDATE users SET DiscordID = $out2->id WHERE id = $uID");  
        Header("Location: ../home");
    }
}
die("You have not been invited to Exoro. Your account has been scheduled for deletion. If you joined the server and this was an error, please try going back to home page.");
}
?>