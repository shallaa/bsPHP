<?php
class tw{
	static private $key = '';
	static private $secret = '';
	static private $callBack = '';
	static private $conn = '';
	static private $api = array(
		'account' => array( 'account/verify_credentials', 'GET' ),
		
		'add' => array( 'statuses/update', 'POST', array( 'status', 'in_reply_to_status_id optional', 'lat', 'long', 'place_id', 'display_coordinates', 'trim_user' ) ),
		'del' => array( 'statuses/destroy/', 'POST', array( 'id', 'trim_user' ) ),
		
		'tlH' => array( 'statuses/home_timeline', 'GET', array( 'count', 'since_id', 'max_id', 'trim_user', 'contributor_details', 'include_entities' ) ),
		'tlU' => array( 'statuses/user_timeline', 'GET', array( 'count', 'since_id', 'max_id', 'trim_user', 'contributor_details', 'include_entities' ) ),
		'tlM' => array( 'statuses/mentions_timeline', 'GET', array( 'count', 'since_id', 'max_id', 'trim_user', 'contributor_details', 'include_entities' ) ),
		
		'dmR' => array( 'direct_messages', 'GET', array( 'since_id', 'max_id', 'count', 'page', 'include_entities', 'skip_status' ) ),
		'dmS' => array( 'direct_messages/sent', 'GET', array( 'since_id', 'max_id', 'count', 'page', 'include_entities' ) ),
		'dmAdd' => array( 'direct_messages/new', 'POST', array( 'user', 'text' ) ),
		'dmDel' => array( 'direct_messages/destroy', 'POST', array( 'id' ) ),
		
		'frAdd' => array( 'friendships/create', 'POST', array( 'id', 'follow' ) ),
		'frDel' => array( 'friendships/destroy', 'POST', array( 'id' ) ),
		
		'frList' => array( 'friends/list', 'GET', array( 'id', 'cursor', 'count', 'skip_status', 'include_user_entities' ) ),
		'foList' => array( 'followers/list', 'GET', array( 'id', 'cursor', 'count', 'skip_status', 'include_user_entities' ) ),
		'frIds' => array( 'friends/ids', 'GET', array( 'id', 'cursor', 'count', 'stringify_ids ' ) ),
		'foIds' => array( 'followers/ids', 'GET', array( 'id', 'cursor', 'count', 'stringify_ids ' ) ),
		
		'search' => array( 'search/tweets', 'GET',
			array( 'q', 'geocode', 'lang', 'locale', 'result_type', 'count', 'until', 'since_id', 'max_id', 'include_entities', 'callback' ) )
	);
	
	static function date( $str ){
		//Thu Jun 13 01:27:39 +0000 2013 
		$t0 = explode( ' ', $str );
		$month = array(
			0,
			'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
			'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
		);
		$m = $month[$t0[1]];
		$d = (int)$t0[2];
		$t = explode( ':', $t0[3] );
		$h = (int)$t[0];
		$i = (int)$t[1];
		$s = (int)$t[2];
		$y = (int)$t0[5];
		
		return $y.'-'.$m.'-'.$d.' '.$h.':'.$i.':'.$s;
	}
	static private function _url( $url, $params = array() ){
		$oauth_token = bs( 'http.ck( vttk )' );
		$oauth_token_secret = bs( 'http.ck( vttks )' );
		if( $oauth_token && $oauth_token_secret ){			
			$connection = new TwitterOAuth( self::$key, self::$secret, $oauth_token, $oauth_token_secret );
			return $connection;
		}else{
			return false;
		}
	}
	static function init(){
		$arguments = func_get_args();
		self::$key = $arguments[0];
		self::$secret = $arguments[1];
		self::$callBack = $arguments[2];
		if( func_num_args() > 3 ){
			self::$conn = $arguments[3];
		}
	}
	static function login1_2(){
		$connection = new TwitterOAuth( self::$key, self::$secret );
		$request_token = $connection -> getRequestToken( self::$callBack );
		switch( $connection -> http_code ){
		case 200:
			$token = $request_token['oauth_token'];
			bs( 'http.ck( vtot, '. $token .' )','_', 'http.ck( vtots, '. $request_token['oauth_token_secret'] .' )' );
			
			$url = $connection -> getAuthorizeURL( $token );
			http::go( $url );
			break;
		default:
			echo( 'Could not connect to Twitter. Refresh the page or try again later.' );
		}
	}
	static function login2_2(){
		if( isset( $_REQUEST['oauth_token'] ) && ( $t0 = bs( 'http.ck( vtot )' ) ) !== $_REQUEST['oauth_token'] ){
			tw::logout();
		}
		$connection = new TwitterOAuth( self::$key, self::$secret, $t0, bs( 'http.ck( vtots )' ) );
		$access_token = $connection -> getAccessToken( $_REQUEST['oauth_verifier'] );
		bs( 'http.ck( vttk, '. $access_token['oauth_token'] .' )','_',
			'http.ck( vttks, '. $access_token['oauth_token_secret'] .' )','_',
			'http.ck( vtot, null )','_', 'http.ck( vtots, null )'
		);
		if( 200 == $connection -> http_code ){
			if( self::$conn ){
				http::go( self::$conn );
			}else{
				return true;
			}			
		}else{
			bs( 'http.ck( vttk, null )','_', 'http.ck( vttks, null )' );
			tw::logout();
		}
	}
	static function logout(){
		$key = 'access_token';
		$_SESSION[$key] = NULL;
		unset( $_SESSION[$key] );		
		if( self::$conn ) http::go( self::$conn );
	}
	static function post( $url, $params = array(), $isReturn = NULL ){
		$connection = tw::_url( $url );
		if( $connection ){
			$content = $connection -> post( $url, $params, $isReturn );
			return $content;
		}else{
			return 2;
		}
	}
	static function get( $url, $params = array(), $isReturn = NULL ){
		$connection = tw::_url( $url );
		if( $connection ){
			$content = $connection -> get( $url, $params, $isReturn );
			return $content;
		}else{
			return 2;
		}
	}
	//-----------------------------------------------------------------------------------------------------------------------------
	static function at( $name ){
		$t0 = self::$api[$name];
		$url = $t0[0];
		$method = $t0[1];
		
		$params = array();
		if( count( $t0 ) > 2 ){
			$t0 = $t0[2];
			$arg = func_get_args();
			$i = 1;
			$j = func_num_args();
			while( $i < $j ){
				$key = $t0[$arg[$i++]];
				$val = $arg[$i++];
				
				if( $val ){
					if( gettype( $val ) == 'array' ) $val = implode( ',', $val );
					if( $val[0] == '@' ) $val = substr( $val, 1 );
					$params[$key] = $val;
				}
			}
		}
		if( $name == 'account' ){
			if( bs( 'D:tw', 'USER', tw::get( $url ) ) ){
				return true;
			}else{
				return false;
			}
		}else if( $name == 'del' ){
			tw::post( $url . substr( $params['id'], 1 ), NULL, 1 );
		}else{
			if( $method == 'GET' ){
				return tw::get( $url, $params );
			}else{
				return tw::post( $url, $params, 1 );
			}
		}
	}
}
class TwitterOAuth{
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public $host = "https://api.twitter.com/1.1/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30; 
	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;
	/* Respons format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = TRUE;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'TwitterOAuth v0.2.0-beta2';
	/* Immediately retry the API call if the response was not successful. */
	//public $retry = TRUE;
	
	/**
	* Set API URLS
	*/
	function accessTokenURL(){ return 'https://api.twitter.com/oauth/access_token'; }
	function authenticateURL(){ return 'https://api.twitter.com/oauth/authenticate'; }
	function authorizeURL(){ return 'https://api.twitter.com/oauth/authorize'; }
	function requestTokenURL(){ return 'https://api.twitter.com/oauth/request_token'; }
	
	/**
	* Debug helpers
	*/
	function lastStatusCode(){ return $this -> http_status; }
	function lastAPICall(){ return $this -> last_api_call; }
	
	/**
	* construct TwitterOAuth object
	*/
	function __construct( $consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL ){
		$this -> sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this -> consumer = new OAuthConsumer( $consumer_key, $consumer_secret );
		if( !empty( $oauth_token ) && !empty( $oauth_token_secret ) ){
			$this -> token = new OAuthConsumer( $oauth_token, $oauth_token_secret );
		}else{
			$this -> token = NULL;
		}
	}

	/**
	* Get a request_token from Twitter
	*
	* @returns a key/value array containing oauth_token and oauth_token_secret
	*/
	function getRequestToken( $oauth_callback = NULL ){
		$parameters = array();
		if( !empty( $oauth_callback ) ){
			$parameters['oauth_callback'] = $oauth_callback;
		}
		$request = $this -> oAuthRequest( $this -> requestTokenURL(), 'GET', $parameters );
		$token = OAuthUtil::parse_parameters( $request );
		$this -> token = new OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		return $token;
	}

	/**
	* Get the authorize URL
	*
	* @returns a string
	*/
	function getAuthorizeURL( $token, $sign_in_with_twitter = TRUE ){
		if( is_array( $token ) ){
			$token = $token['oauth_token'];
		}
		if( empty( $sign_in_with_twitter ) ){
			return $this -> authorizeURL() ."?oauth_token={$token}";
		}else{
			return $this -> authenticateURL() ."?oauth_token={$token}&force_login=true";
		}
	}

	/**
	* Exchange request token and secret for an access token and
	* secret, to sign API calls.
	*
	* @returns array( "oauth_token" => "the-access-token",
	*        "oauth_token_secret" => "the-access-secret",
	*        "user_id" => "9436992",
	*        "screen_name" => "abraham" )
	*/
	function getAccessToken( $oauth_verifier = FALSE ){
		global $bsStr;
		$parameters = array();
		if( !empty( $oauth_verifier ) ){
			$parameters['oauth_verifier'] = $oauth_verifier;
		}
		$request = $this -> oAuthRequest( $this -> accessTokenURL(), 'GET', $parameters );		
		$token = OAuthUtil::parse_parameters( $request );
		$this -> token = new OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		return $token;
	}

	/**
	* One time exchange of username and password for access token and secret.
	*
	* @returns array( "oauth_token" => "the-access-token",
	*        "oauth_token_secret" => "the-access-secret",
	*        "user_id" => "9436992",
	*        "screen_name" => "abraham",
	*        "x_auth_expires" => "0" )
	*/ 
	function getXAuthToken( $username, $password ){
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this -> oAuthRequest( $this -> accessTokenURL(), 'POST', $parameters );
		$token = OAuthUtil::parse_parameters( $request );
		$this -> token = new OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		return $token;
	}
	
	function _responseJson( $response ){
		$response = json_decode( $response, true );
		if( @$response['error'] ){
			bs( 'http.ck( vttk, null)','_',
				'http.ck( vttks, null )','_',
				'http('. 'error : '. $response['error'] .')'
			);
			return false;
		}else if( @$response['errors'] ){
			bs( 'http.ck( vttk, null )','_',
				'http.ck( vttks, null )','_',
				'http('. 'errors : '. $response['errors'][0]['message'] .')'
			);
			return false;
		}else{
			return $response;
		}
	}
	function _responseOriginal( $original ){
		$response = json_decode( $original, true );
		if( @$response['error'] ){
			bs( 'http.ck( vttk, null)','_',
				'http.ck( vttks, null )','_',
				'http('. '$data = "error : '. $response['error'] .'";' .')'
			);
		}else if( @$response['errors'] ){
			bs( 'http.ck( vttk, null)','_',
				'http.ck( vttks, null )','_',
				'http('. '$data = "error : '. $response['errors'][0]['message'] .'";' .')'
			);
		}else{
			echo( '$data = '. $original .';' );
		}
	}
	/**
	* GET wrapper for oAuthRequest.
	*/
	function get( $url, $parameters = array(), $isReturn = NULL ){
		$response = $this -> oAuthRequest( $url, 'GET', $parameters );
		if( $isReturn ){
			if( $this -> format === 'json' && $this -> decode_json ){
				return self::_responseOriginal( $response );
			}
		}
		if( $this -> format === 'json' && $this -> decode_json ){
			return self::_responseJson( $response );
		}
		return $response;
	}
 
	/**
	* POST wrapper for oAuthRequest.
	*/
	function post( $url, $parameters = array(), $isReturn = NULL ){
		$response = $this -> oAuthRequest( $url, 'POST', $parameters );
		if( $isReturn ){
			if( $this -> format === 'json' && $this -> decode_json ){
				return self::_responseOriginal( $response );
			}
		}
		if( $this -> format === 'json' && $this -> decode_json ){
			return self::_responseJson( $response );
		}
		return $response;
	}

	/**
	* DELETE wrapper for oAuthReqeust.
	*/
	function delete( $url, $parameters = array(), $isReturn = NULL ){
		$response = $this -> oAuthRequest( $url, 'DELETE', $parameters );
		if( $isReturn ){
			if( $this -> format === 'json' && $this -> decode_json ){
				return self::_responseOriginal( $response );
			}
		}
		if( $this -> format === 'json' && $this -> decode_json ){
			return self::_responseJson( $response );
		}
		return $response;
	}

	/**
	* Format and sign an OAuth / API request
	*/
	function oAuthRequest( $url, $method, $parameters ){
		if( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ){
			$url = "{$this -> host}{$url}.{$this -> format}";
		}
		$request = OAuthRequest::from_consumer_and_token( $this -> consumer, $this -> token, $method, $url, $parameters );
		$request -> sign_request( $this -> sha1_method, $this -> consumer, $this -> token );
		switch( $method ){
		case'GET':
			return $this -> http( $request -> to_url(), 'GET' );
		default:
			return $this -> http( $request -> get_normalized_http_url(), $method, $request -> to_postdata() );
		}
	}

	/**
	* Make an HTTP request
	*
	* @return API results
	*/
	function http( $url, $method, $postfields = NULL ){
		$this -> http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt( $ci, CURLOPT_USERAGENT, $this -> useragent );
		curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, $this -> connecttimeout );
		curl_setopt( $ci, CURLOPT_TIMEOUT, $this -> timeout );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ci, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, $this -> ssl_verifypeer );
		curl_setopt( $ci, CURLOPT_HEADERFUNCTION, array( $this, 'getHeader' ) );
		curl_setopt( $ci, CURLOPT_HEADER, FALSE );
		
		//if( count( $postfields ) ) echo( $method.'<br>'.$url.'<br>'.implode( $postfields, '<br>' ) );
		switch( $method ){
		case'POST':
			curl_setopt( $ci, CURLOPT_POST, TRUE );
			if( !empty( $postfields ) ){
				curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
			}
			break;
		case'DELETE':
			curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'DELETE' );
			if( !empty( $postfields ) ){
				$url = "{$url}?{$postfields}";
			}
		}
		curl_setopt( $ci, CURLOPT_URL, $url );
		$response = curl_exec( $ci );
		$this -> http_code = curl_getinfo( $ci, CURLINFO_HTTP_CODE );
		$this -> http_info = array_merge( $this -> http_info, curl_getinfo( $ci ) );
		$this -> url = $url;
		curl_close( $ci );
		return $response;
	}

	/**
	* Get the header info to store.
	*/
	function getHeader( $ch, $header ){
		$i = strpos( $header, ':' );
		if( !empty( $i ) ){
			$key = str_replace( '-', '_', strtolower( substr( $header, 0, $i ) ) );
			$value = trim( substr( $header, $i + 2 ) );
			$this -> http_header[$key] = $value;
		}
		return strlen( $header );
	}
}
//---- OAuth -----------------------------------------------------------------------------------------------------------------
class OAuthException extends Exception{}
class OAuthConsumer{
	public $key;
	public $secret;
	function __construct( $key, $secret, $callback_url = NULL ){
		$this -> key = $key;
		$this -> secret = $secret;
		$this -> callback_url = $callback_url;
	}
	function __toString(){
		return "OAuthConsumer[key=$this->key,secret=$this->secret]";
	}
}
class OAuthToken{
	public $key;
	public $secret;
	function __construct( $key, $secret ){
		$this -> key = $key;
		$this -> secret = $secret;
	}
	function to_string(){
		return	"oauth_token=". OAuthUtil::urlencode_rfc3986( $this -> key ) ."&".
				"oauth_token_secret=". OAuthUtil::urlencode_rfc3986( $this -> secret );
	}
	function __toString(){
		return $this -> to_string();
	}
}
abstract class OAuthSignatureMethod{
	abstract public function get_name();
	abstract public function build_signature( $request, $consumer, $token );
	public function check_signature( $request, $consumer, $token, $signature ){
		$built = $this -> build_signature( $request, $consumer, $token );
		if( strlen( $built ) == 0 || strlen( $signature ) == 0 ){
			return false;
		}
		if( strlen( $built ) != strlen( $signature ) ){
			return false;
		}
		$result = 0;
		for( $i = 0; $i < strlen( $signature ); $i++ ){
			$result |= ord( $built{$i} ) ^ ord( $signature{$i} );
		}
		if( $token ){
			//echo( 'OAuthSignatureMethod:'. $result .'-'. $token .'-'. $built .'-'. $signature .'<br>' );
			return true;
		}
		return $result == 0;
	}
}
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod{
	function get_name(){
		return "HMAC-SHA1";
	}
	public function build_signature( $request, $consumer, $token ){
		$base_string = $request -> get_signature_base_string();
		$request -> base_string = $base_string;
		$key_parts = array( $consumer -> secret,( $token ) ? $token -> secret : "" );
		$key_parts = OAuthUtil::urlencode_rfc3986( $key_parts );
		$key = implode( '&', $key_parts );
		return base64_encode( hash_hmac( 'sha1', $base_string, $key, true ) );
	}
}
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod{
	public function get_name(){
		return "PLAINTEXT";
	}
	public function build_signature( $request, $consumer, $token ){
		$key_parts = array( $consumer -> secret,( $token ) ? $token -> secret : "" );
		$key_parts = OAuthUtil::urlencode_rfc3986( $key_parts );
		$key = implode( '&', $key_parts );
		$request -> base_string = $key;
		return $key;
	}
}
class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod{
	public function get_name(){
		return "RSA-SHA1";
	}/*
	protected abstract function fetch_public_cert( &$request );
	protected abstract function fetch_private_cert( &$request );*/
	public function build_signature( $request, $consumer, $token ){
		$base_string = $request -> get_signature_base_string();
		$request -> base_string = $base_string;
		$cert = $this -> fetch_private_cert( $request );
		$privatekeyid = openssl_get_privatekey( $cert );
		$ok = openssl_sign( $base_string, $signature, $privatekeyid );
		openssl_free_key( $privatekeyid );
		return base64_encode( $signature );
	}
	public function check_signature( $request, $consumer, $token, $signature ){
		$decoded_sig = base64_decode( $signature );
		$base_string = $request -> get_signature_base_string();
		$cert = $this -> fetch_public_cert( $request );
		$publickeyid = openssl_get_publickey( $cert );
		$ok = openssl_verify( $base_string, $decoded_sig, $publickeyid );
		openssl_free_key( $publickeyid );
		return $ok == 1;
	}
}
class OAuthRequest{
	private $parameters;
	private $http_method;
	private $http_url;
	public $base_string;
	public static $version = '1.0';
	public static $POST_INPUT = 'php://input';
	
	function __construct( $http_method, $http_url, $parameters = NULL ){
		@$parameters or $parameters = array();
		$parameters = array_merge( OAuthUtil::parse_parameters( parse_url( $http_url, PHP_URL_QUERY ) ), $parameters );
		$this -> parameters = $parameters;
		$this -> http_method = $http_method;
		$this -> http_url = $http_url;
	}
	public static function from_request( $http_method = NULL, $http_url = NULL, $parameters = NULL ){
		$scheme = ( !isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] != "on" ) ? 'http' : 'https';
		@$http_url or $http_url = $scheme .'://'. $_SERVER['HTTP_HOST'] .':'. $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		@$http_method or $http_method = $_SERVER['REQUEST_METHOD'];
		if( !$parameters ){
			$request_headers = OAuthUtil::get_headers();
			$parameters = OAuthUtil::parse_parameters( $_SERVER['QUERY_STRING'] );
			if( $http_method == "POST" && @strstr( $request_headers["Content-Type"], "application/x-www-form-urlencoded" ) ){
				$post_data = OAuthUtil::parse_parameters( file_get_contents(self::$POST_INPUT ) );
				$parameters = array_merge( $parameters, $post_data );
			}
			if( @substr( $request_headers['Authorization'], 0, 6 ) == "OAuth " ){
				$header_parameters = OAuthUtil::split_header( $request_headers['Authorization'] );
				$parameters = array_merge( $parameters, $header_parameters );
			}
		}
		return new OAuthRequest( $http_method, $http_url, $parameters );
	}
	public static function from_consumer_and_token( $consumer, $token, $http_method, $http_url, $parameters=NULL ){
		@$parameters or $parameters = array();
		$defaults = array( "oauth_version" => OAuthRequest::$version,
			   "oauth_nonce" => OAuthRequest::generate_nonce(),
			   "oauth_timestamp" => OAuthRequest::generate_timestamp(),
			   "oauth_consumer_key" => $consumer -> key );
		if( $token ) $defaults['oauth_token'] = $token -> key;
		$parameters = array_merge( $defaults, $parameters );
		return new OAuthRequest( $http_method, $http_url, $parameters );
	}
	public function set_parameter( $name, $value, $allow_duplicates = true ){
		if( $allow_duplicates && isset( $this -> parameters[$name] ) ){
			if(is_scalar( $this -> parameters[$name] ) ){
				$this -> parameters[$name] = array( $this -> parameters[$name] );
			}			
			$this -> parameters[$name][] = $value;
		}else{
			$this -> parameters[$name] = $value;
		}
	}
	public function get_parameter( $name ){
		return isset( $this -> parameters[$name] ) ? $this -> parameters[$name] : null;
	}
	public function get_parameters(){
		return $this -> parameters;
	}
	public function unset_parameter( $name ){
		unset( $this -> parameters[$name] );
	}
	public function get_signable_parameters(){
		$params = $this -> parameters;
		if( isset( $params['oauth_signature'] ) ){
			unset( $params['oauth_signature'] );
		}		
		return OAuthUtil::build_http_query( $params );
	}
	public function get_signature_base_string(){
		$parts = array(
			$this -> get_normalized_http_method(),
			$this -> get_normalized_http_url(),
			$this -> get_signable_parameters()
		);		
		$parts = OAuthUtil::urlencode_rfc3986( $parts );		
		return implode( '&', $parts );
	}
	public function get_normalized_http_method(){
		return strtoupper( $this -> http_method );
	}
	public function get_normalized_http_url(){
		$parts = parse_url( $this -> http_url );
		
		$port = @$parts['port'];
		$scheme = $parts['scheme'];
		$host = $parts['host'];
		$path = @$parts['path'];
		
		$port or $port = ( $scheme == 'https' ) ? '443' : '80';
		
		if( ( $scheme == 'https' && $port != '443' )||( $scheme == 'http' && $port != '80' ) ){
			$host = "$host:$port";
		}
		return "$scheme://$host$path";
	}
	public function to_url(){
		$post_data = $this -> to_postdata();
		$out = $this -> get_normalized_http_url();
		if( $post_data ){
			$out .= '?'. $post_data;
		}
		return $out;
	}
	public function to_postdata(){
		return OAuthUtil::build_http_query( $this -> parameters );
	}
	public function to_header( $realm=null ){
		$first = true;
		if( $realm ){
			$out = 'Authorization: OAuth realm="'. OAuthUtil::urlencode_rfc3986( $realm ) .'"';
			$first = false;
		}else
			$out = 'Authorization: OAuth';
			
			$total = array();
			foreach( $this -> parameters as $k => $v ){
				if(substr( $k, 0, 5 ) != "oauth" ) continue;
				if(is_array( $v ) ){
				throw new OAuthException( 'Arrays not supported in headers' );
			}
			$out .=( $first ) ? ' ' : ',';
			$out .= OAuthUtil::urlencode_rfc3986( $k ) .'="'. OAuthUtil::urlencode_rfc3986( $v ) .'"';
			$first = false;
		}
		return $out;
	}
	public function __toString(){
		return $this -> to_url();
	}
	public function sign_request( $signature_method, $consumer, $token ){
		$this -> set_parameter( "oauth_signature_method", $signature_method -> get_name(), false );
		$signature = $this -> build_signature( $signature_method, $consumer, $token );
		$this -> set_parameter( "oauth_signature", $signature, false );
	}
	public function build_signature( $signature_method, $consumer, $token ){
		$signature = $signature_method -> build_signature( $this, $consumer, $token );
		return $signature;
	}
	private static function generate_timestamp(){
		return time();
	}
	private static function generate_nonce(){
		$mt = microtime();
		$rand = mt_rand();
		
		return md5( $mt . $rand );
	}
}
class OAuthServer{
	protected $timestamp_threshold = 300;
	protected $version = '1.0';
	protected $signature_methods = array();
	protected $data_store;
	function __construct( $data_store ){
		$this -> data_store = $data_store;
	}	
	public function add_signature_method( $signature_method ){
		$this -> signature_methods[$signature_method -> get_name()] = $signature_method;
	}
	public function fetch_request_token( &$request ){
		$this -> get_version( $request );
		$consumer = $this -> get_consumer( $request );
		$token = NULL;
		$this -> check_signature( $request, $consumer, $token );
		$callback = $request -> get_parameter( 'oauth_callback' );
		$new_token = $this -> data_store -> new_request_token( $consumer, $callback );		
		return $new_token;
	}
	public function fetch_access_token( &$request ){
		$this -> get_version( $request );
		$consumer = $this -> get_consumer( $request );
		$token = $this -> get_token( $request, $consumer, "request" );
		$this -> check_signature( $request, $consumer, $token );
		$verifier = $request -> get_parameter( 'oauth_verifier' );
		$new_token = $this -> data_store -> new_access_token( $token, $consumer, $verifier );		
		return $new_token;
	}
	public function verify_request( &$request ){
		$this -> get_version( $request );
		$consumer = $this -> get_consumer( $request );
		$token = $this -> get_token( $request, $consumer, "access" );
		$this -> check_signature( $request, $consumer, $token );
		return array( $consumer, $token );
	}
	private function get_version( &$request ){
		$version = $request -> get_parameter( "oauth_version" );
		if( !$version ){
			$version = '1.0';
		}
		if( $version !== $this -> version ){
			throw new OAuthException( "OAuth version '$version' not supported" );
		}
		return $version;
	}
	private function get_signature_method( &$request ){
		$signature_method = @$request -> get_parameter( "oauth_signature_method" );
		if( !$signature_method ){
			throw new OAuthException( 'No signature method parameter. This parameter is required' );
		}
		if( !in_array( $signature_method, array_keys( $this -> signature_methods ) ) ){
			throw new OAuthException(
				"Signature method '$signature_method' not supported " .
				"try one of the following: " .
				implode( ", ", array_keys( $this -> signature_methods ) )
			);
		}
		return $this -> signature_methods[$signature_method];
	}
	public function get_signature_methods(){
		return $this -> signature_methods;
	}
	private function get_consumer( &$request ){
		$consumer_key = @$request -> get_parameter( "oauth_consumer_key" );
		if( !$consumer_key ){
			throw new OAuthException( "Invalid consumer key" );
		}
		$consumer = $this -> data_store -> lookup_consumer( $consumer_key );
		if( !$consumer ){
			throw new OAuthException( "Invalid consumer" );
		}
		return $consumer;
	}
	private function get_token( &$request, $consumer, $token_type = "access" ){
		$token_field = @$request -> get_parameter( 'oauth_token' );
		$token = $this -> data_store -> lookup_token( $consumer, $token_type, $token_field );
		if( !$token ){
			throw new OAuthException( "Invalid $token_type token: $token_field" );
		}
		return $token;
	}
	private function check_signature( &$request, $consumer, $token ){
		$timestamp = @$request -> get_parameter( 'oauth_timestamp' );
		$nonce = @$request -> get_parameter( 'oauth_nonce' );
		
		$this -> check_timestamp( $timestamp );
		$this -> check_nonce( $consumer, $token, $nonce, $timestamp );
		
		$signature_method = $this -> get_signature_method( $request );
		$signature = $request -> get_parameter( 'oauth_signature' );
		$valid_sig = $signature_method -> check_signature(
			$request,
			$consumer,
			$token,
			$signature
		);
		if( !$valid_sig ){
			throw new OAuthException( "Invalid signature" );
		}
	}
	private function check_timestamp( $timestamp ){
		if( !$timestamp )
			throw new OAuthException(
				'Missing timestamp parameter. The parameter is required'
			);
		$now = time();
		if(abs( $now - $timestamp ) > $this -> timestamp_threshold ){
			throw new OAuthException(
				"Expired timestamp, yours $timestamp, ours $now"
			);
		}
	}
	private function check_nonce( $consumer, $token, $nonce, $timestamp ){
		if( !$nonce )
			throw new OAuthException(
				'Missing nonce parameter. The parameter is required'
			);
		$found = $this -> data_store -> lookup_nonce(
			$consumer,
			$token,
			$nonce,
			$timestamp
		);
		if( $found ){
			throw new OAuthException( "Nonce already used: $nonce" );
		}
	}
}
class OAuthDataStore{
	private $consumer;
    private $request_token;
    private $access_token;
    private $nonce;
	function __construct( $request = NULL ){
		global $bsOauth;
		$mToken = $bsOauth['key']();
		$this -> consumer = new OAuthConsumer( CONSUMER_KEY, CONSUMER_SECRET, NULL );
		if( $request ){
			$token = $request -> get_parameter( 'oauth_token' );
		}else{
			$token = $mToken;
		}
		$secret = $bsOauth['key']( true );
		$this -> request_token = new OAuthToken( $token, $secret, 1 );
		$this -> access_token = new OAuthToken( $mToken, $secret, 1 );
		
		$this -> nonce = "nonce";
	}
	function lookup_consumer( $consumer_key ){
		if( $consumer_key == $this -> consumer -> key ){
			return $this -> consumer;
		}
		return NULL;
	}
	function lookup_token( $consumer, $token_type, $token ){
		$token_attrib = $token_type . "_token";
		if( $consumer -> key == $this -> consumer -> key && $token == $this -> $token_attrib -> key ){
			return $this -> $token_attrib;
		}
		return NULL;
	}
	function lookup_nonce( $consumer, $token, $nonce, $timestamp ){
		if( $consumer -> key == $this -> consumer -> key && (($token && $token -> key == $this -> request_token -> key ) ||
			($token && $token -> key == $this -> access_token -> key)) && $nonce == $this -> nonce
		){
			return $this -> nonce;
		}
		return NULL;
	}
	function new_request_token( $consumer ){
		if( $consumer -> key == $this -> consumer -> key ){
			return $this -> request_token;
		}
		return NULL;
	}
	function new_access_token( $token, $consumer ){
		if( $consumer -> key == $this -> consumer -> key && $token -> key == $this -> request_token -> key ){
			return $this -> access_token;
		}
		return NULL;
	}
}
class OAuthUtil{
	public static function urlencode_rfc3986( $input ){
		if( is_array( $input ) ){
			return array_map( array( 'OAuthUtil', 'urlencode_rfc3986' ), $input );
		}else if( is_scalar( $input ) ){
			return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $input ) ) );
		}else{
			return '';
		}
	}
	public static function urldecode_rfc3986( $string ){
		return urldecode( $string );
	}
	public static function split_header( $header, $only_allow_oauth_parameters = true ){
		$pattern = '/(([-_a-z]* )=( "([^"]* )"|([^,]* ) ),? )/';
		$offset = 0;
		$params = array();
		while( preg_match( $pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset ) > 0 ){
			$match = $matches[0];
			$header_name = $matches[2][0];
			$header_content =(isset( $matches[5] ) ) ? $matches[5][0] : $matches[4][0];
			if( preg_match( '/^oauth_/', $header_name ) || !$only_allow_oauth_parameters ){
				$params[$header_name] = OAuthUtil::urldecode_rfc3986( $header_content );
			}
			$offset = $match[1] + strlen( $match[0] );
		}
		if( isset( $params['realm'] ) ){
			unset( $params['realm'] );
		}
		return $params;
	}
	public static function get_headers(){
		if( function_exists( 'apache_request_headers' ) ){
			$headers = apache_request_headers();
			$out = array();
			foreach( $headers AS $key => $value ){
				$key = str_replace( " ", "-", ucwords( strtolower( str_replace( "-", " ", $key ) ) ) );
				$out[$key] = $value;
			}
		}else{
			$out = array();
			if( isset( $_SERVER['CONTENT_TYPE'] ) )
				$out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
			if( isset( $_ENV['CONTENT_TYPE'] ) )
				$out['Content-Type'] = $_ENV['CONTENT_TYPE'];
			
			foreach( $_SERVER as $key => $value ){
				if( substr( $key, 0, 5 ) == "HTTP_" ){
					$key = str_replace( " ", "-", ucwords( strtolower( str_replace( "_", " ", substr( $key, 5 ) ) ) ) );
					$out[$key] = $value;
				}
			}
		}
		return $out;
	}
	public static function parse_parameters( $input ){
		if( !isset( $input ) || !$input ) return array();
		$pairs = explode( '&', $input );
		$parsed_parameters = array();
		foreach( $pairs as $pair ){
			$split = explode( '=', $pair, 2 );
			$parameter = OAuthUtil::urldecode_rfc3986( $split[0] );
			$value = isset( $split[1] ) ? OAuthUtil::urldecode_rfc3986( $split[1] ) : '';
			if( isset( $parsed_parameters[$parameter] ) ){
				if( is_scalar( $parsed_parameters[$parameter] ) ){
					$parsed_parameters[$parameter] = array( $parsed_parameters[$parameter] );
				}				
				$parsed_parameters[$parameter][] = $value;
			}else{
				$parsed_parameters[$parameter] = $value;
			}
		}
		return $parsed_parameters;
	}

	public static function build_http_query( $params ){
		if( !$params ) return '';
		$keys = OAuthUtil::urlencode_rfc3986( array_keys( $params ) );
		$values = OAuthUtil::urlencode_rfc3986( array_values( $params ) );
		$params = array_combine( $keys, $values );
		uksort( $params, 'strcmp' );
		
		$pairs = array();
		foreach( $params as $parameter => $value ){
			if(is_array( $value ) ){
				natsort( $value );
				foreach( $value as $duplicate_value ){
					$pairs[] = $parameter . '=' . $duplicate_value;
				}
			}else{
				$pairs[] = $parameter . '=' . $value;
			}
		}
		return implode( '&', $pairs );
	}
}
?>