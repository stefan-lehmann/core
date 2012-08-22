<?php 

//check our post variable from index.php, just to insure user isn't accessing this page directly.
// You can replace this with strong functions
if(!isset($_POST["connect"])) 
{
	die();
}
else
{
        include_once("config.php"); //Include our configuration file.
	
        // redirect user to facebook login page if empty data or fresh login requires
        if (!$fbuser){
            $loginUrl = $facebook->getLoginUrl(array('redirect_uri'=>$return_url, false));
            header('Location: '.$loginUrl);
        }
	//user details
	$fullname = $me['first_name'].' '.$me['last_name'];
	$email = $me['email'];
	
	/* connect to mysql */
	$connecDB = mysql_connect(DBHOSTNAME, DBUSERNAME, DBPASSWORD)or die("Unable to connect to MySQL");	
	mysql_select_db(DBNAME,$connecDB);
	
	//Check user in our database
	$result = mysql_query("SELECT id FROM usertable WHERE fbid=$uid");
        
        $LocallogoutUrl = RETURNURL.'?logout=1';

    if(mysql_num_rows($result))
    {
         //User exist, Show welcome back message
        echo 'Ajax Response :<br />Welcome back '. $me['first_name'] . ' '. $me['last_name'].' ( Facebook ID : '.$uid.')! [<a href="'.$LocallogoutUrl.'">Log Out</a>]';
 
        //print user facebook data
        echo '<pre>';
        print_r($me);
        echo '</pre>';
 
    }else{
        //User is new, Show connected message and store info in our Database
        echo 'Ajax Response :<br />Hi '. $me['first_name'] . ' '. $me['last_name'].' ('.$uid.')! <br /> Now that you are logged in to Facebook using jQuery Ajax [<a href="'.$LocallogoutUrl.'">Log Out</a>].
        <br />the information can be stored in database <br />';
 
        //print user facebook data
        echo '<pre>';
        print_r($me);
        echo '</pre>';
 
        // Insert user into Database.
        @mysql_query("INSERT INTO usertable (fbid, fullname, email) VALUES ($uid, '$fullname','$email')");
 
    }

	
	mysql_close($connecDB);
}
?>