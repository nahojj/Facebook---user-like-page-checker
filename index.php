<?php
// Include SDK
require __DIR__ . '/facebook-php-sdk-v4/autoload.php';

// include required files form Facebook SDK
use Facebook\Entities\AccessToken;
use Facebook\Entities\SignedRequest;

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookOtherException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphSessionInfo;

// start session
session_start();

// init app with app id and secret
FacebookSession::setDefaultApplication('APP_ID','SECRET_CODE');

// login helper with redirect_uri
$helper = new FacebookRedirectLoginHelper( 'REDIRECT URL' );

// see if a existing session exists
if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
	// create new session from saved access_token
	$session = new FacebookSession( $_SESSION['fb_token'] );

	// validate the access_token to make sure it's still valid
	try {
		if ( !$session->validate() ) {
			$session = null;
		}
	} catch ( Exception $e ) {
		// catch any exceptions
		$session = null;
	}
}

if ( !isset( $session ) || $session === null ) {
	// no session exists

	try {
		$session = $helper->getSessionFromRedirect();
	} catch( FacebookRequestException $ex ) {
		// When Facebook returns an error
		// handle this better in production code
		print_r( $ex );
	} catch( Exception $ex ) {
		// When validation fails or other local issues
		// handle this better in production code
		print_r( $ex );
	}

}

// see if we have a session
if ( isset( $session ) ) {

	// save the session
	$_SESSION['fb_token'] = $session -> getToken();

	// create a session using saved token or the new one we generated at login
	$session = new FacebookSession( $session -> getToken() );

	// graph api request for user information
	$request_me = new FacebookRequest( $session, 'GET', '/me' );
	$response_me = $request_me -> execute();
	$graphObject_me = $response_me -> getGraphObject() -> asArray();

	// graph api request for if user likes the page (id)
	$request_page_like = new FacebookRequest( $session, 'GET', "/me/likes/PAGE_ID" );
	$response_page_like = $request_page_like -> execute();

	// get response
	$graphObject_page_like = $response_page_like -> getGraphObject() -> asArray();

	// print logout url using session and redirect_uri (logout.php page should destroy the session)
	echo '<a href="' . $helper -> getLogoutUrl( $session, 'logout.php' ) . '">Logout</a>';

	}

?>



<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Facebook LikePage</title>

	<!-- LÈ CSS -->
	<link rel="stylesheet" href="style.css?v=1">
	<link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">

	<!-- Fonts -->
	<link href='http://fonts.googleapis.com/css?family=Roboto:400italic,700,100italic,300italic,400' rel='stylesheet' type='text/css'>

	<script type="text/javascript">
		window.fbAsyncInit = function() {
			FB.Canvas.setSize();
		}
	</script>
</head>

<body>

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=864267033617650&version=v2.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<header id="header">
		<h1>Båtaccenten - Tävling</h1>

		<div class="pull-right">
			<?php if(isset( $session )) {
				echo '<a href="' . $helper -> getLogoutUrl( $session, 'logout.php' ) . '">Logout</a>';
			} else {
				echo '<a href="' . $helper -> getLoginUrl( array( 'email', 'user_friends', 'user_likes' ) ) . '">Login</a>';
			} ?>
		</div>
		<div class="clearfix"></div>
	</header>

	<main class="main">
		<div class="container">
			<div class="jumbotron">
				<?php
					if(isset($graphObject_page_like) && is_array($graphObject_page_like) && count($graphObject_page_like) > 0) {
					echo '
						<p>User Like Page</p>
					';
					} else {
						echo '
							<p>User Unlike Page</p>
						';
					} ?>
			</div>
		</div>
	</main>

	<!-- LÈ SCRIPTS -->
	<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
	<script src="/bower_components/jquery/dist/jquery.min.js"></script>
	<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="/js/bootstrapValidator.min.js"></script>
	<script src="js/main.js?3"></script>

</body>
</html>
