<?php
/*
	Plugin Name: Arqam
	Plugin URI: http://codecanyon.net/user/mo3aser/portfolio
	Description: WordPress Social Counter Plugin
	Author: Mo3aser
	Version: 1.1.3
	Author URI: http://tielabs.com/
*/

/*-----------------------------------------------------------------------------------*/
# Get Plugin Options and Transient
/*-----------------------------------------------------------------------------------*/
include 'inc/wc_class.php';
require_once ( 'arqam-panel.php' );
define ('ARQAM_Plugin' , 'Arqam' );
define ('ARQAM_Plugin_ver' , '1.0.0' );

$arq_transient	=	get_transient( 'arq_counters' );
$arq_options	=	get_option( 'arq_options' );
if( empty($arq_options)	)	$arq_options = array();
if( empty($arq_transient) || (false ===  $arq_transient) )	$arq_transient = array();

$arq_data = array();
$arq_social_items = array('facebook', 'twitter', 'google+', 'youtube', 'vimeo', 'dribbble', 'github', 'envato', 'soundcloud', 'behance', 'forrst', 'delicious', 'instagram', 'mailchimp', 'foursquare');

/*-----------------------------------------------------------------------------------*/
# Register and Enquee plugin's styles and scripts
/*-----------------------------------------------------------------------------------*/
function arqam_scripts_styles(){
	if( !is_admin()){
		wp_register_style( 'arqam-style' , plugins_url('assets/style.css' , __FILE__) ) ;
		wp_enqueue_style( 'arqam-style' );
	}	
}
add_action( 'init', 'arqam_scripts_styles' );


/*-----------------------------------------------------------------------------------*/
# Load Text Domain
/*-----------------------------------------------------------------------------------*/
add_action('plugins_loaded', 'arqam_init');
function arqam_init() {
	load_plugin_textdomain( 'arq' , false, dirname( plugin_basename( __FILE__ ) ).'/languages' ); 
}


/*-----------------------------------------------------------------------------------*/
# Store Defaults settings
/*-----------------------------------------------------------------------------------*/
if ( is_admin() && isset($_GET['activate'] ) && $pagenow == 'plugins.php' ) {
	global $arqam_default_data;
	if( !get_option('arqam_active') ){
	
		$default_data = array(
			'social' => array(
				'facebook' 		=> array(	'id' => 'tielabs',	 'text' => __( 'Fans' , 'arq' ) ),
				'twitter' 		=> array(	'id' => 'mo3aser',	 'text' => __( 'Followers' , 'arq' )  ),
				'google' 		=> array(	'id' => '106192958286631454676',	 'text' => __( 'Followers' , 'arq' ) ),
				'youtube'		=> array(	'id' => 'TEAMMESAI', 'text' => __( 'Subscribers' , 'arq' ) ,'type' => 'User'),
				'vimeo' 		=> array(						 'text' => __( 'Subscribers' , 'arq' ) ),
				'dribbble' 		=> array(	'id' => 'mo3aser',	 'text' => __( 'Followers' , 'arq' ) ),
				'envato' 		=> array(	'id' => 'mo3aser',	 'text' => __( 'Followers' , 'arq' ) ,'site' => 'themeforest'),
				'github'  		=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'soundcloud'  	=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'behance'  		=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'forrst'  		=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'instagram'  	=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'foursquare'  	=> array(						 'text' => __( 'Friends' , 'arq' ) ),
				'delicious'  	=> array(						 'text' => __( 'Followers' , 'arq' ) ),
				'mailchimp'  	=> array(						 'text' => __( 'Subscribers' , 'arq' ) )
			),
			'cache' => 2
		);
		
		update_option( 'arq_options' , $default_data);
		update_option( 'arqam_active' , ARQAM_Plugin_ver );
	}  
}


/*-----------------------------------------------------------------------------------*/
# Get Data From API's
/*-----------------------------------------------------------------------------------*/
function arq_remote_get( $url , $json = true) {
	$request = wp_remote_retrieve_body( wp_remote_get( $url , array( 'timeout' => 18 , 'sslverify' => false ) ) );
	if( $json ) $request = @json_decode( $request , true );
	return $request;  
}


/*-----------------------------------------------------------------------------------*/
# Update Options and Transient
/*-----------------------------------------------------------------------------------*/
function arq_update_count( $data ){
	global $arq_options, $arq_transient ;
	
	if( empty( $arq_options['cache'] ) || !is_int($arq_options['cache']) )
		$cache = 2 ;
	else $cache = $arq_options['cache'] ;
	
	if( is_array($data) ){
		foreach( $data as $item => $value ){
			$arq_transient[$item] = $value;
			$arq_options['data'][$item] = $value;
		}
	}
	set_transient( 'arq_counters', $arq_transient , $cache*60*60 );
	update_option( 'arq_options' , $arq_options );
}


/*-----------------------------------------------------------------------------------*/
# Number Format Function
/*-----------------------------------------------------------------------------------*/
function arq_format_num( $number ){
	if( !is_numeric( $number ) ) return $number ;
	
	if($number >= 1000000)
		return round( ($number/1000)/1000 , 1) . "M";
    	
	elseif($number >= 100000)
		return round( $number/1000, 0) . "k";
    
	else
		return @number_format( $number );
}


/*-----------------------------------------------------------------------------------*/
# Get Social Counters
/*-----------------------------------------------------------------------------------*/
function arq_get_counters( $layout, $dark, $width = '' ){
	global $arq_data, $arq_options, $arq_social_items ;

	if ( $layout == 'gray_1col' ) $layout = " arq-outer-frame arq-col1";
	elseif ( $layout == 'gray_2col' ) $layout = " arq-outer-frame arq-col2";
	elseif ( $layout == 'gray_3col' ) $layout = " arq-outer-frame arq-col3";
	elseif ( $layout == 'colored_1col' ) $layout = " arq-outer-frame arq-colored arq-col1";
	elseif ( $layout == 'colored_2col' ) $layout = " arq-outer-frame arq-colored arq-col2";
	elseif ( $layout == 'colored_3col' ) $layout = " arq-outer-frame arq-colored arq-col3";
	elseif ( $layout == 'flat_2col' ) $layout = " arq-flat arq-col2";
	elseif ( $layout == 'flat_3col' ) $layout = " arq-flat arq-col3";
	
	if( $dark ) $layout = $layout.' arq-dark';
	
	if( !empty($width) ) $width = ' style="width:'.$width.'px;"';
	
	$new_window = ' target="_blank" ';
	
	if( !empty( $arq_options['sort'] ) )
		$arq_sort_items = $arq_options['sort'];
	
	if( empty( $arq_options['sort'] ) || !is_array($arq_sort_items) || $arq_social_items != array_intersect($arq_social_items , $arq_sort_items ) ){
		$arq_sort_items = $arq_social_items ;
	}
	?>
	<div class="arqam-widget-counter<?php echo $layout; ?>">
		<ul>	
	<?php
foreach ( $arq_sort_items as $arq_item ){

	switch ( $arq_item ) {
		case 'facebook': 
		if( !empty($arq_options['social']['facebook']['id']) ){
			$text = __( 'Fans' , 'arq' );
			if( !empty($arq_options['social']['facebook']['text']) ) $text = $arq_options['social']['facebook']['text'];
		?>
			<li class="arq-facebook"<?php echo $width ?>>
				<a href="http://www.facebook.com/<?php echo $arq_options['social']['facebook']['id']; ?>"<?php echo $new_window ?>>
					<i class="arqicon-facebook"></i>
					<span><?php echo arq_format_num( arq_facebook_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'twitter':
		if( !empty($arq_options['social']['twitter']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['twitter']['text']) ) $text = $arq_options['social']['twitter']['text'];
		?>
			<li class="arq-twitter"<?php echo $width ?>>
				<a href="http://twitter.com/<?php echo $arq_options['social']['twitter']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-twitter"></i>
					<span><?php echo arq_format_num( arq_twitter_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'google+':
		if( !empty($arq_options['social']['google']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['google']['text']) ) $text = $arq_options['social']['google']['text'];
		?>
			<li class="arq-google"<?php echo $width ?>>
				<a href="http://plus.google.com/<?php echo $arq_options['social']['google']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-gplus"></i>
					<span><?php echo arq_format_num( arq_google_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'youtube':	
		if( !empty($arq_options['social']['youtube']['id']) ){
			$text = __( 'Subscribers' , 'arq' );
			if( !empty($arq_options['social']['youtube']['text']) ) $text = $arq_options['social']['youtube']['text'];
			
			$type = 'user';
			if( !empty($arq_options['social']['youtube']['type']) && $arq_options['social']['youtube']['type'] == 'Channel' ) $type = 'channel';
		?>
			<li class="arq-youtube"<?php echo $width ?>>
				<a href="http://youtube.com/<?php echo $type ?>/<?php echo $arq_options['social']['youtube']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-youtube"></i>
					<span><?php echo arq_format_num(  arq_youtube_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'vimeo':
 		if( !empty($arq_options['social']['vimeo']['id']) ){
			$text = __( 'Subscribers' , 'arq' );
			if( !empty($arq_options['social']['vimeo']['text']) ) $text = $arq_options['social']['vimeo']['text'];
		?>
			<li class="arq-vimeo"<?php echo $width ?>>
				<a href="https://vimeo.com/channels/<?php echo $arq_options['social']['vimeo']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-vimeo"></i> 
					<span><?php echo arq_format_num( arq_vimeo_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'github':
 		if( !empty($arq_options['social']['github']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['github']['text']) ) $text = $arq_options['social']['github']['text'];
		?>
			<li class="arq-github"<?php echo $width ?>>
				<a href="https://github.com/<?php echo $arq_options['social']['github']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-github-circled"></i> 
					<span><?php echo arq_format_num( arq_github_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'dribbble':
 		if( !empty($arq_options['social']['dribbble']['id']) ){ 
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['dribbble']['text']) ) $text = $arq_options['social']['dribbble']['text'];
		?>
			<li class="arq-dribbble"<?php echo $width ?>>
				<a href="http://dribbble.com/<?php echo $arq_options['social']['dribbble']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-dribbble"></i>
					<span><?php echo arq_format_num( arq_dribbble_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'forrst': 
		if( !empty($arq_options['social']['forrst']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['forrst']['text']) ) $text = $arq_options['social']['forrst']['text'];
		?>
			<li class="arq-forrst"<?php echo $width ?>>
				<a href="http://forrst.com/people/<?php echo $arq_options['social']['forrst']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-forrst"></i> 
					<span><?php echo arq_format_num( arq_forrst_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'envato': 
		if( !empty($arq_options['social']['envato']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['envato']['text']) ) $text = $arq_options['social']['envato']['text'];
		?>
			<li class="arq-envato"<?php echo $width ?>>
				<a href="http://<?php echo $arq_options['social']['envato']['site'] ?>.net/user/<?php echo $arq_options['social']['envato']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-envato"></i>
					<span><?php echo arq_format_num( arq_envato_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'soundcloud': 
		if( !empty($arq_options['social']['soundcloud']['id']) && !empty( $arq_options['social']['soundcloud']['api'] ) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['soundcloud']['text']) ) $text = $arq_options['social']['soundcloud']['text'];
		?>
			<li class="arq-soundcloud"<?php echo $width ?>>
				<a href="http://soundcloud.com/<?php echo $arq_options['social']['soundcloud']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-soundcloud"></i> 
					<span><?php echo arq_format_num( arq_soundcloud_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'behance': 
		if( !empty($arq_options['social']['behance']['id']) && !empty( $arq_options['social']['behance']['api'] ) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['behance']['text']) ) $text = $arq_options['social']['behance']['text'];
		?>
			<li class="arq-behance"<?php echo $width ?>>
				<a href="http://www.behance.net/<?php echo $arq_options['social']['behance']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-behance"></i> 
					<span><?php echo arq_format_num( arq_behance_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'delicious': 
		if( !empty($arq_options['social']['delicious']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['delicious']['text']) ) $text = $arq_options['social']['delicious']['text'];
		?>
			<li class="arq-delicious"<?php echo $width ?>>
				<a href="http://delicious.com/<?php echo $arq_options['social']['delicious']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-delicious"></i>
					<span><?php echo arq_format_num( arq_delicious_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'instagram': 
		if( !empty($arq_options['social']['instagram']['id']) ){
			$text = __( 'Followers' , 'arq' );
			if( !empty($arq_options['social']['instagram']['text']) ) $text = $arq_options['social']['instagram']['text'];
		?>
			<li class="arq-instagram"<?php echo $width ?>>
				<a href="http://instagram.com/<?php echo $arq_options['social']['instagram']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-instagram-filled"></i>
					<span><?php echo arq_format_num( arq_instagram_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'mailchimp': 
		if( !empty($arq_options['social']['mailchimp']['id']) ){
			$text = __( 'Subscribers' , 'arq' );
			if( !empty($arq_options['social']['mailchimp']['text']) ) $text = $arq_options['social']['mailchimp']['text'];
		?>
			<li class="arq-mailchimp"<?php echo $width ?>>
				<a href="<?php echo $arq_options['social']['mailchimp']['url'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-mail-alt"></i>
					<span><?php echo arq_format_num( arq_mailchimp_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
		case 'foursquare': 
		if( !empty($arq_options['social']['foursquare']['id']) ){
			$text = __( 'Friends' , 'arq' );
			if( !empty($arq_options['social']['foursquare']['text']) ) $text = $arq_options['social']['foursquare']['text'];
		?>
			<li class="arq-foursquare"<?php echo $width ?>>
				<a href="http://foursquare.com/<?php echo $arq_options['social']['foursquare']['id'] ?>"<?php echo $new_window ?>>
					<i class="arqicon-foursquare"></i>
					<span><?php echo arq_format_num( arq_foursquare_count() ) ?></span>
					<small><?php echo $text; ?></small>
				</a>
			</li>
		<?php
		}
		break;
	}
	
} //End Foreach ?>
							
			</ul>
		</div>
		<!-- Arqam Social Counter Plugin : http://codecanyon.net/user/mo3aser/portfolio?ref=mo3aser -->
<?php
	if( !empty ($arq_data) ){
		arq_update_count( $arq_data );
	}
}


/*-----------------------------------------------------------------------------------*/
# Functions to Get Counters
/*-----------------------------------------------------------------------------------*/
/* Twitter Followers */
function arq_twitter_count() {
	global $arq_data, $arq_options, $arq_transient;
	
	if( !empty($arq_transient['twitter']) ){
		$result = $arq_transient['twitter'];
	}
	elseif( empty($arq_transient['twitter']) && !empty($arq_data) && !empty( $arq_options['data']['twitter'] )  ){
		$result = $arq_options['data']['twitter'];
	}
	else{
		$id = $arq_options['social']['twitter']['id'];

		$consumerKey = $arq_options['social']['twitter']['key'];
		$consumerSecret = $arq_options['social']['twitter']['secret'];
		$token = get_option('arqam_TwitterToken');
	 
		// getting new auth bearer only if we don't have one
		if(!$token) {
			// preparing credentials
			$credentials = $consumerKey . ':' . $consumerSecret;
			$toSend = base64_encode($credentials);
	 
			// http post arguments
			$args = array(
				'method' => 'POST',
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Authorization' => 'Basic ' . $toSend,
					'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
				),
				'body' => array( 'grant_type' => 'client_credentials' )
			);
	 
			add_filter('https_ssl_verify', '__return_false');
			$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);
	 
			$keys = json_decode(wp_remote_retrieve_body($response));
	 
			if($keys) {
				// saving token to wp_options table
				update_option('arqam_TwitterToken', $keys->access_token);
				$token = $keys->access_token;
			}
		}
		
		// we have bearer token wether we obtained it from API or from options
		$args = array(
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array(
				'Authorization' => "Bearer $token"
			)
		);
	 
		add_filter('https_ssl_verify', '__return_false');
		$api_url = "https://api.twitter.com/1.1/users/show.json?screen_name=$id";
		$response = wp_remote_get($api_url, $args);
	 
		if (!is_wp_error($response)) {
			$followers = json_decode(wp_remote_retrieve_body($response));
			$result = $followers->followers_count;
		} else {
			$result = $arq_options['data']['twitter'];
			// uncomment below to debug
			//die($response->get_error_message());
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['twitter'] = $result; 
		
		if( empty( $result ) && !empty( $arq_options['data']['twitter'] ) ) //Get the stored data
			$result = $arq_options['data']['twitter'];	
	}
	return $result;
}

/* Facebook Fans */
function arq_facebook_count(){
	global $arq_data, $arq_options, $arq_transient;
		
	if( !empty($arq_transient['facebook']) ){
		$result = $arq_transient['facebook'];
	}
	elseif( empty($arq_transient['facebook']) && !empty($arq_data) && !empty( $arq_options['data']['facebook'] )  ){
		$result = $arq_options['data']['facebook'];
	}
	else{
		$id = $arq_options['social']['facebook']['id'];
		try {		
			$data = @arq_remote_get( "http://graph.facebook.com/$id");
			$result = (int) $data['likes'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['facebook'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['facebook'] ) ) //Get the stored data
			$result = $arq_options['data']['facebook'];	
	}
	return $result;
}

/* Google+ Followers */
function arq_google_count(){
	global $arq_data, $arq_options, $arq_transient;
	
	/*if( !empty($arq_transient['google']) ){
		$result = $arq_transient['google'];
	}
	elseif( empty($arq_transient['google']) && !empty($arq_data) && !empty( $arq_options['data']['google'] )  ){
		$result = $arq_options['data']['google'];
	}
	else{*/
		$id = $arq_options['social']['google']['id'];
		$id = str_replace('+', '%2B', $id);
		try {		
			//$data = @arq_remote_get( "https://apis.google.com/u/0/_/pages/badge?bsv&hl=en-US&width=200&url=https%3A%2F%2Fplus.google.com%2F$id%3Fprsrc%3D1" , false );
			$data = @arq_remote_get( "https://apis.google.com/u/0/_/widget/render/page?usegapi=1&bsv=o&width=180&href=https%3A%2F%2Fplus.google.com%2F$id&showcoverphoto=0&showtagline=0&hl=en-US" , false );
			
			if( empty($data) )
				//$data =	file_get_contents( "https://apis.google.com/u/0/_/pages/badge?bsv&hl=en-US&width=200&url=https%3A%2F%2Fplus.google.com%2F$id%3Fprsrc%3D1" );
				$data =	file_get_contents( "https://apis.google.com/u/0/_/widget/render/page?usegapi=1&bsv=o&width=180&href=https%3A%2F%2Fplus.google.com%2F$id&showcoverphoto=0&showtagline=0&hl=en-US" );
			
			preg_match('/<div class="rce kdd Z6d" style="font-size:11px;">(.*?)<\/div>/s', $data, $result);
			if (isset($result) && !empty($result)){
				$count = $result[1];
				$result = preg_replace('/[^0-9_]/', '', $count);
				$result = (int) $result;
			}
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['google'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['google'] ) ) //Get the stored data
			$result = $arq_options['data']['google'];	
	//}
	return $result;
}

/* Youtube Subscribers */
function arq_youtube_count(){
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['youtube']) ){
		$result = $arq_transient['youtube'];
	}
	elseif( empty($arq_transient['youtube']) && !empty($arq_data) && !empty( $arq_options['data']['youtube'] )  ){
		$result = $arq_options['data']['youtube'];
	}
	else{
		$id = $arq_options['social']['youtube']['id'];
		try {		
			$data = @arq_remote_get("http://gdata.youtube.com/feeds/api/users/$id?alt=json");
			$result = (int) $data['entry']['yt$statistics']['subscriberCount'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['youtube'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['youtube'] ) ) //Get the stored data
			$result = $arq_options['data']['youtube'];	
	}
	return $result;	
}

/* Vimeo Subscribers */
function arq_vimeo_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['vimeo']) ){
		$result = $arq_transient['vimeo'];
	}
	elseif( empty($arq_transient['vimeo']) && !empty($arq_data) && !empty( $arq_options['data']['vimeo'] )  ){
		$result = $arq_options['data']['vimeo'];
	}
	else{
		$id = $arq_options['social']['vimeo']['id'];
		try {		
			@$data = arq_remote_get( "http://vimeo.com/api/v2/channel/$id/info.json" );
			$result = (int) $data['total_subscribers'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['vimeo'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['vimeo'] ) ) //Get the stored data
			$result = $arq_options['data']['vimeo'];	
	}
	return $result;
}

/* Dribbble Followers */
function arq_dribbble_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['dribbble']) ){
		$result = $arq_transient['dribbble'];
	}
	elseif( empty($arq_transient['dribbble']) && !empty($arq_data) && !empty( $arq_options['data']['dribbble'] )  ){
		$result = $arq_options['data']['dribbble'];
	}else{
		$id = $arq_options['social']['dribbble']['id'];
		try {		
			$data = @arq_remote_get("http://api.dribbble.com/$id");
			$result = (int) $data['followers_count'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['dribbble'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['dribbble'] ) ) //Get the stored data
			$result = $arq_options['data']['dribbble'];	
	}
	return $result;
}

/* Github Followers */
function arq_github_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['github']) ){
		$result = $arq_transient['github'];
	}
	elseif( empty($arq_transient['github']) && !empty($arq_data) && !empty( $arq_options['data']['github'] )  ){
		$result = $arq_options['data']['github'];
	}
	else{
		$id = $arq_options['social']['github']['id'];
		try {		
			$data = @arq_remote_get("https://api.github.com/users/$id");
			$result = (int) $data['followers'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['github'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['github'] ) ) //Get the stored data
			$result = $arq_options['data']['github'];	
	}
	return $result;
}

/* Forrst Followers */
function arq_forrst_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['forrst']) ){
		$result = $arq_transient['forrst'];
	}
	elseif( empty($arq_transient['forrst']) && !empty($arq_data) && !empty( $arq_options['data']['forrst'] )  ){
		$result = $arq_options['data']['forrst'];
	}
	else{
		$id = $arq_options['social']['forrst']['id'];
		try {		
			$data = @arq_remote_get("http://forrst.com/api/v2/users/info?username=$id");
			$result = (int) $data['resp']['typecast_followers'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['forrst'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['forrst'] ) ) //Get the stored data
			$result = $arq_options['data']['forrst'];	
	}
	return $result;
}

/* Envato Followers */
function arq_envato_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['envato']) ){
		$result = $arq_transient['envato'];
	}
	elseif( empty($arq_transient['envato']) && !empty($arq_data) && !empty( $arq_options['data']['envato'] )  ){
		$result = $arq_options['data']['envato'];
	}
	else{
		$id = $arq_options['social']['envato']['id'];
		try {		
			$data = @arq_remote_get("http://marketplace.envato.com/api/edge/user:$id.json");
			$result = (int) $data['user']['followers'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['envato'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['envato'] ) ) //Get the stored data
			$result = $arq_options['data']['envato'];	
	}
	return $result;
}

/* SoundCloud Followers */
function arq_soundcloud_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['soundcloud']) ){
		$result = $arq_transient['soundcloud'];
	}
	elseif( empty($arq_transient['soundcloud']) && !empty($arq_data) && !empty( $arq_options['data']['soundcloud'] )  ){
		$result = $arq_options['data']['soundcloud'];
	}
	else{
		$id = $arq_options['social']['soundcloud']['id'];
		$api = $arq_options['social']['soundcloud']['api'];
		try {		
			$data = @arq_remote_get("http://api.soundcloud.com/users/$id.json?consumer_key=$api");
			$result = (int) $data['followers_count'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['soundcloud'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['soundcloud'] ) ) //Get the stored data
			$result = $arq_options['data']['soundcloud'];	
	}
	return $result;
}

/* Behance Followers */
function arq_behance_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['behance']) ){
		$result = $arq_transient['behance'];
	}
	elseif( empty($arq_transient['behance']) && !empty($arq_data) && !empty( $arq_options['data']['behance'] )  ){
		$result = $arq_options['data']['behance'];
	}
	else{
		$id = $arq_options['social']['behance']['id'];
		$api = $arq_options['social']['behance']['api'];
		try {		
			$data = @arq_remote_get("http://www.behance.net/v2/users/$id?api_key=$api");
			$result = (int) $data['user']['stats']['followers'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['behance'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['behance'] ) ) //Get the stored data
			$result = $arq_options['data']['behance'];	
	}
	return $result;
}

/* Delicious Followers */
function arq_delicious_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['delicious']) ){
		$result = $arq_transient['delicious'];
	}
	elseif( empty($arq_transient['delicious']) && !empty($arq_data) && !empty( $arq_options['data']['delicious'] )  ){
		$result = $arq_options['data']['delicious'];
	}
	else{
		$id = $arq_options['social']['delicious']['id'];
		try {		
			$data = @arq_remote_get("http://feeds.delicious.com/v2/json/userinfo/$id");
			$result = (int) $data[2]['n'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['delicious'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['delicious'] ) ) //Get the stored data
			$result = $arq_options['data']['delicious'];	
	}
	return $result;
}

/* Instagram Followers */
function arq_instagram_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['instagram']) ){
		$result = $arq_transient['instagram'];
	}
	elseif( empty($arq_transient['instagram']) && !empty($arq_data) && !empty( $arq_options['data']['instagram'] )  ){
		$result = $arq_options['data']['instagram'];
	}
	else{
		$api = $arq_options['social']['instagram']['api'];
		$id = explode(".", $api);
		try {		
			$data = @arq_remote_get("https://api.instagram.com/v1/users/$id[0]/?access_token=$api");
			$result = (int) $data['data']['counts']['followed_by'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['instagram'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['instagram'] ) ) //Get the stored data
			$result = $arq_options['data']['instagram'];	
	}
	return $result;
}

/* Foursquare Followers */
function arq_foursquare_count() {
	global $arq_data, $arq_options, $arq_transient;

	if( !empty($arq_transient['foursquare']) ){
		$result = $arq_transient['foursquare'];
	}
	elseif( empty($arq_transient['foursquare']) && !empty($arq_data) && !empty( $arq_options['data']['foursquare'] )  ){
		$result = $arq_options['data']['foursquare'];
	}
	else{
		$api = $arq_options['social']['foursquare']['api'];
		$id = explode(".", $api);
		$date = date("Ymd");
		try {		
			$data = @arq_remote_get("https://api.foursquare.com/v2/users/self?oauth_token=$api&v=$date");
			$result = (int) $data['response']['user']['friends']['count'];	
		} catch (Exception $e) {
			$result = 0;
		}
		
		if( !empty( $result ) ) //To update the stored data
			$arq_data['foursquare'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['foursquare'] ) ) //Get the stored data
			$result = $arq_options['data']['foursquare'];	
	}
	return $result;
}

/* Mailchimp Subscribers */
function arq_mailchimp_count() {
	global $arq_data, $arq_options, $arq_transient;
		
	if( !empty($arq_transient['mailchimp']) ){
		$result = $arq_transient['mailchimp'];
	}
	elseif( empty($arq_transient['mailchimp']) && !empty($arq_data) && !empty( $arq_options['data']['mailchimp'] )  ){
		$result = $arq_options['data']['mailchimp'];
	}
	else{
		if (!class_exists('MCAPI')) require_once 'inc/MCAPI.class.php';

		$apikey = $arq_options['social']['mailchimp']['api'];
		$listId = $arq_options['social']['mailchimp']['id'];
		
		$api = new MCAPI($apikey);
		$retval = $api->lists();
		$result = 0;
		
		foreach ($retval['data'] as $list){
			if($list['id'] == $listId){
				$result = $list['stats']['member_count'];
				break;
			}
		}
			
		if( !empty( $result ) ) //To update the stored data
			$arq_data['mailchimp'] = $result; 

		if( empty( $result ) && !empty( $arq_options['data']['mailchimp'] ) ) //Get the stored data
			$result = $arq_options['data']['mailchimp'];	
	}
	return $result;
}

/*-----------------------------------------------------------------------------------*/
# Social Counter Widget
/*-----------------------------------------------------------------------------------*/
add_action( 'widgets_init', 'arqam_counter_widget_box' );
function arqam_counter_widget_box() {
	register_widget( 'arqam_counter_widget' );
}
class arqam_counter_widget extends WP_Widget {

	function arqam_counter_widget() {
		$widget_ops = array( 'classname' => 'arqam_counter-widget', 'description' => ''  );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'arqam_counter-widget' );
		$this->WP_Widget( 'arqam_counter-widget', ARQAM_Plugin. ' - Social Counter', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {

		extract( $args );

		$title = $instance['title'] ;
		$layout = $instance['layout'] ;
		$dark = $instance['dark'] ;
		$width = $instance['width'] ;
		$box_only = $instance['box_only'] ;

		if( empty($box_only) )	echo $before_widget . $before_title . $title . $after_title;
		arq_get_counters( $layout, $dark, $width );
		if( empty($box_only) )	echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['layout'] = $new_instance['layout'] ;
		$instance['title'] =  $new_instance['title'] ;
		$instance['dark'] =  $new_instance['dark'] ;
		$instance['width'] =  $new_instance['width'] ;
		$instance['box_only'] =  $new_instance['box_only'] ;

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __( 'Social' , 'arq' )  , 'layout' => 'gray_3col' , 'dark' => false, 'box_only' => false );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title :' , 'arq' ) ?> </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Style :' , 'arq' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" >
				<optgroup label="<?php _e( 'Gray Icons' , 'arq' ) ?>">
					<option value="gray_1col" <?php if( $instance['layout'] == 'gray_1col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '1 Column' , 'arq' ) ?></option>
					<option value="gray_2col" <?php if( $instance['layout'] == 'gray_2col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '2 Columns' , 'arq' ) ?></option>
					<option value="gray_3col" <?php if( $instance['layout'] == 'gray_3col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '3 Columns' , 'arq' ) ?></option>
				</optgroup>	
				
				<optgroup label="<?php _e( 'Colored Icons' , 'arq' ) ?>">
					<option value="colored_1col" <?php if( $instance['layout'] == 'colored_1col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '1 Column' , 'arq' ) ?></option>
					<option value="colored_2col" <?php if( $instance['layout'] == 'colored_2col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '2 Columns' , 'arq' ) ?></option>
					<option value="colored_3col" <?php if( $instance['layout'] == 'colored_3col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '3 Columns' , 'arq' ) ?></option>
				</optgroup>	
				
				<optgroup label="<?php _e( 'Flat Icons' , 'arq' ) ?>">
					<option value="flat_2col" <?php if( $instance['layout'] == 'flat_2col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '2 Columns' , 'arq' ) ?></option>
					<option value="flat_3col" <?php if( $instance['layout'] == 'flat_3col' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '3 Columns' , 'arq' ) ?></option>
				</optgroup>		
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'dark' ); ?>"><?php _e( 'Dark Skin :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'dark' ); ?>" name="<?php echo $this->get_field_name( 'dark' ); ?>" value="true" <?php if( $instance['dark'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'box_only' ); ?>"><?php _e( 'Show the Social Box only :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'box_only' ); ?>" name="<?php echo $this->get_field_name( 'box_only' ); ?>" value="true" <?php if( $instance['box_only'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br /><small><?php _e( 'Will avoid the theme widget design and hide the widget title .' , 'arq' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Forced Items Width :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php if(isset( $instance['width'] )) echo $instance['width']; ?>" style="width:40px;" type="text" /> <?php _e( 'px' , 'arq' ) ?>
		</p>
		
	<?php
	}
}
?>