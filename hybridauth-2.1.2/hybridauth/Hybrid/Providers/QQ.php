<?php
/** 
 * QQ OAuth Class
 * 
 * @package             HybridAuth additional providers package 
 * @author              David He<david.scnbhj@gmail.com>
 * @version             1.0
 * @license             BSD License
 */ 

/**
 * QQ provider adapter based on OAuth2 protocol
 *
 * http://hybridauth.sourceforge.net/userguide/IDProvider_info_QQ.html
 */
class Hybrid_Providers_QQ extends Hybrid_Provider_Model_OAuth2
{
	function initialize() 
	{
		// Use Beijing Timezone
		date_default_timezone_set ('Etc/GMT-8');
		
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url      = 'https://open.t.qq.com/api/';
		$this->api->authorize_url     = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';
		$this->api->token_url         = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
	}

    /**
	* load OAUTH2 GET/POST public params
	*/
    function oauth2QqParams()
    {
    	return $params = array(
    		"format"             => 'json',
			"oauth_consumer_key" => $_SESSION["PROVIDER::ID"],
			"openid"             => $_SESSION["QQ::OPENID"],			
			"oauth_version"      => "2.a",
			"scope"              => "all"
		);
    }

	/**
	* load the user profile from the IDp api client
	*/
	function getUserProfile()
	{
		$parameters = $this->oauth2QqParams();
		$response = $this->api->get('user/info', $parameters);

		// check the last HTTP status code returned
		if ( $this->api->http_code != 200 ){
			throw new Exception( 'User profile request failed! ' . $this->providerId . ' returned an error. ' . $this->errorMessageByStatus( $this->api->http_code ), 6 );
		}

		if ( ! is_object( $response ) ){
			throw new Exception( 'User profile request failed! ' . $this->providerId . ' api returned an invalid response.', 6 );
		}

		$profile = $response->data;

		$this->user->profile->identifier    = @ $profile->name;
		$this->user->profile->displayName  	= @ $profile->nick;
		$this->user->profile->address 		= @ $profile->location;
		$this->user->profile->profileURL 	= @ 'http://t.qq.com/'.$profile->name;
		$this->user->profile->photoURL 		= @ $profile->head;
		$this->user->profile->email 		= @ $profile->email;
		$this->user->profile->birthDay      = @ $profile->birth_day;
		$this->user->profile->birthMonth    = @ $profile->birth_month;
		$this->user->profile->birthYear     = @ $profile->birth_year;
		switch ( $profile->sex ) {
			case '1': $this->user->profile->gender = 'male'; break;
			case '2': $this->user->profile->gender = 'female'; break;
		}
		
		return $this->user->profile;
	}
	
	/**
	 * load the user contacts
	 */
	function getUserContacts()
	{
		$parameters = $this->oauth2QqParams();
		$parameters['reqnum'] = 10;
		
		$response = $this->api->get('friends/idollist', $parameters);

		if ( $this->api->http_code != 200 )
		{
			throw new Exception( 'User contacts request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
		}

		if ( !$response->data->info && ( $response->errcode != 0 ) )
		{
			return array();
		}
		
		$contacts = array();
		
		foreach( $response->data->info as $item ) {
			$uc = new Hybrid_User_Contact();

			$uc->identifier   = @ $item->fansnum;
			$uc->displayName  = @ $item->nick;
			$uc->profileURL   = 'http://t.qq.com/' . $item->name;
			$uc->photoURL     = $item->head . '/100';

			$contacts[] = $uc;
		}
		
		return $contacts;
	}
	
	/**
	 * update user status
	 */ 
	function setUserStatus( $status )
	{
		$parameters = $this->oauth2QqParams();
		$parameters['content']	= $status;
		$parameters['clientip']	= $this->getIP();

		$response = $this->api->post('t/add', $parameters); 
		
		if ( $this->api->http_code != 200 )
		{
			throw new Exception( 'Update user status failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
		}
		
		if ( $response->errcode != 0 )
		{
			throw new Exception( 'Update user status failed! ' . $this->providerId . ' returned an error: ' . $response->msg );
		}
		
		return $response;
	}
	
	/**
	 * load the user latest activity  
	 *    - timeline : all the stream
	 *    - me       : the user activity only  
	 */
	function getUserActivity( $stream )
	{
		$parameters = $this->oauth2QqParams();
		$parameters['reqnum'] = '10';
		
		if ( $stream == 'me' )
		{
			$url = 'statuses/broadcast_timeline';
		} else {
			$url = 'statuses/home_timeline';
		}
		
		$response = $this->api->get($url, $parameters); 
		
		if ( $this->api->http_code != 200 )
		{
			throw new Exception( 'User activity stream request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
		}
		
		$activities = array();
		
		if ( count( $response->data->info ) > 0 && ( $response->errcode != 0 ) ) 
		{
			return array();
		}
		
		foreach ( $response->data->info  as $item ) 
		{
			$ua = new Hybrid_User_Activity();
			$ua->id                 = @ $item->id;
			$ua->date               = @ $item->timestamp;
			$ua->text               = @ $item->origtext;
			$ua->user->identifier   = @ $item->name;
			$ua->user->displayName  = @ $item->nick;
			$ua->user->profileURL   = 'http://t.qq.com/' . $item->name;
			$ua->user->photoURL     = $item->head . '/100';
			
			$activities[] = $ua;
		}
		
		return $activities;
	}
	
	function getIP() {
		if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}
