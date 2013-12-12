<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="zh-TW">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<?php
	// session_start(); 

	// config and includes
   	$config = dirname(__FILE__) . '/../../hybridauth/config.php';
    require_once( "../../hybridauth/Hybrid/Auth.php" );

	try{
		// hybridauth EP
		$hybridauth = new Hybrid_Auth( $config );

		// automatically try to login with Sina
		$sina = $hybridauth->authenticate( "Sina" );

		// return TRUE or False <= generally will be used to check if the user is connected to Sina before getting user profile, posting stuffs, etc..
		$is_user_logged_in = $sina->isUserConnected();

		// get the user profile 
		$user_profile = $sina->getUserProfile();

		// access user profile data
		echo "Ohai there! U are connected with: <b>{$sina->id}</b><br />";
		echo "As: <b>{$user_profile->displayName}</b><br />";
		echo "And your provider user identifier is: <b>{$user_profile->identifier}</b><br />";  

		// or even inspect it
		echo "<pre>" . print_r( $user_profile, true ) . "</pre><br />";

		// logout
		echo "Logging out.."; 
		$sina->logout(); 
	}
	catch( Exception $e ){  
		// In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
		// let hybridauth forget all about the user so we can try to authenticate again.

		// Display the recived error, 
		// to know more please refer to Exceptions handling section on the userguide
		switch( $e->getCode() ){ 
			case 0 : echo "Unspecified error."; break;
			case 1 : echo "Hybridauth configuration error."; break;
			case 2 : echo "Provider not properly configured."; break;
			case 3 : echo "Unknown or disabled provider."; break;
			case 4 : echo "Missing provider application credentials."; break;
			case 5 : echo "Authentication failed. " 
					  . "The user has canceled the authentication or the provider refused the connection."; 
				   break;
			case 6 : echo "User profile request failed. Most likely the user is not connected "
					  . "to the provider and he should to authenticate again."; 
				   $sina->logout();
				   break;
			case 7 : echo "User not connected to the provider."; 
				   $sina->logout();
				   break;
			case 8 : echo "Provider does not support this feature."; break;
		} 

		// well, basically your should not display this to the end user, just give him a hint and move on..
		echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
		echo "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>"; 
	}
?>
<body>
</body>
</html>