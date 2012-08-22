<?php
/*
Please note: config.php file can slow down page loading time when refreshed, 
because it calls for fresh user session from Facebook everytime. 
To avoid this it should be replaced with your site’s built in user management system, 
which will instantly check logged in users. 
*/
include_once("config.php"); //Include our configuration file.
?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en-gb" lang="en-gb" >
<head>
<!-- Call jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<title>Ajax Facebook Connect With jQuery</title>
 <script>
 function AjaxResponse()
 {
 var myData = 'connect=1'; //For demo sake we will pass a post variable, Check process_facebook.php
 jQuery.ajax({
 type: "POST",
 url: "process_facebook.php", //Our Ajax processing file
 dataType:"html",
 data:myData,
 success:function(response){
 $("#results").html('<fieldset style="padding:20px">'+response+'</fieldset>'); //Our Result
 },
 error:function (xhr, ajaxOptions, thrownError){
 $("#results").html('<fieldset style="padding:20px;color:red;">'+thrownError+'</fieldset>'); //Error
 }
 });
 }

 function LodingAnimate() //Function to show loading Image
 {
 $("#LoginButton").hide(); //hide login button once user authorize the application
 $("#results").html('<img src="ajax-loader.gif" /> Please Wait Connecting...'); //show loading image while we process user
 }

 </script>
</head>

<body>
<noscript>Javascript Supported Browser is required to run Ajax Connect</noscript>

<div id="results"><!-- Replaced with Ajax Result or Error --></div>

<div id="LoginButton">
<?php

 if(!$fbuser) //Only show login button if user is not Logged In (Replace this with site's logged-in/logged-out checker)
 {
 echo '<div class="fb-login-button" onlogin="javascript:CallAfterLogin();" size="medium" scope="publish_stream,email">Connect With Facebook</div>';
 }
 else{
 //Show logout URL for Logged in User
 $params = array('next'=>RETURNURL.'?logout=1');  // We will add 'logout' GET variable to the URL, to destroy user session after. check config.php
 $logoutUrl = $facebook->getLogoutUrl($params);
 echo 'Hi '.$me["first_name"].'! You are Logged in to facebook, <a href="'.$logoutUrl.'">Log Out</a>.(Note: this will also log you out of facebook)';
 }
?>
</div>

<div id="fb-root"></div>
<script type="text/javascript">
window.fbAsyncInit = function() {
FB.init({appId: '<?=APPID?>',cookie: true,xfbml: true,oauth: true});};
(function() {var e = document.createElement('script');
e.async = true;e.src = document.location.protocol +'//connect.facebook.net/en_US/all.js';
document.getElementById('fb-root').appendChild(e);}());

function CallAfterLogin(){
 FB.login(function(response)
 {
 if (response.authResponse)
 {
 LodingAnimate(); //Inject our Animation function here
 FB.api('/me', function(response) {AjaxResponse()}); //Inject our Ajax function, when user Authorizes our App
 }
 });
}
</script>

</body>
</html>