<?php
/**
 * This file is a general purpose click handling file. When a user clicks a button or submits
 * a form, send them to the admin_post action, which directs them here.  All results should
 * be echoed per admin_post documentation: https://codex.wordpress.org/Plugin_API/Action_Reference/admin_post_%28action%29
 */

if( !class_exists( 'ABD_Click_Handler' ) ) {
	class ABD_Click_Handler {
		public static function create_bcc_plugin() {
			check_admin_referer( 'user instructed anti-adblock fallback plugin creation' );
			$res = ABD_Anti_Adblock::create_bcc_plugin();

			$res ? $sv = 'create_bcc_success' : $sv = 'create_bcc_failure';
			
			if( $res ) {
				//	Automatic creation successful... let's delete the manual one so we're not doubled up
				ABD_Anti_Adblock::delete_bcc_manual_plugin();
			}

			
			self::redirect( $sv );
		}

		public static function reset_bcc_plugin_name() {
			check_admin_referer( 'user instructed anti-adblock fallback plugin rename' );
			$dres = ABD_Anti_Adblock::delete_bcc_plugin();
			$gres = ABD_Anti_Adblock::generate_new_dir_name();
			$cres = ABD_Anti_Adblock::create_bcc_plugin();

			($dres && $gres && $cres) ? $sv = 'rename_bcc_success' : $sv = 'rename_bcc_failure';
			
			self::redirect( $sv );	
		}

		public static function delete_bcc_plugin() {
			check_admin_referer( 'user instructed anti-adblock fallback plugin deletion' );
			$res = ABD_Anti_Adblock::delete_bcc_plugin();

			$res ? $sv = 'delete_bcc_success' : $sv = 'delete_bcc_failure';

			self::redirect( $sv );
		}

		public static function delete_manual_bcc_plugin() {
			check_admin_referer( 'user instructed manual anti-adblock fallback plugin deletion' );
			$res = ABD_Anti_Adblock::delete_bcc_manual_plugin();

			$res ? $sv = 'delete_bcc_success' : $sv = 'delete_bcc_failure';

			self::redirect( $sv );
		}

		public static function clear_log() {
			check_admin_referer( 'user instructed deletion of all log entries' );

			ABD_Log::clear_log();

			self::redirect( 'clear_log_success' );
		}

		public static function delete_shortcode() {
			if( array_key_exists( 'id', $_GET ) && !empty( $_GET['id'] ) ) {
				$id = $_GET['id'];
			}
			else {
				//	No ID given
				self::redirect( 'delete_shortcode_failure_no_id' );
			}

			check_admin_referer( 'user instructed shortcode delete id equals ' . $id );

			$res = ABD_Database::delete_shortcode( $id );

			$res ? $sv = 'delete_shortcode_success' : 'delete_shortcode_failure_unknown';

			self::redirect( $sv );
		}


		public static function send_usage_info() {
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			check_admin_referer( 'user instructed sending usage info to dev' );

			$email_address = 'abd_usage_reports@johnmorris.me';
			$headers = array(
				'From: ABD AUTO EMAIL <' . $email_address . '><br />',
				'Content-Type: text/html; charset=UTF-8'
			);

			$contents = 'Website: ' . get_home_url() . '<br /><br />';
			$contents .= ABD_Perf_Tools::get_readable_server_config_data( '<br />' );
			$contents .= '<br /><br /><br /><br />SESSION LOG<br />======================<br />';
			$contents .= ABD_Log::get_readable_log( 0, '    >>   ', '<br /><br />' );

			$res = wp_mail( $email_address, 'ABD Usage Info: ' . get_home_url(), $contents, $headers );

			if( !$res ) {
				ABD_Log::error( 'Unknown error sending usage info email to developer.' );
			}
			else {
				ABD_Log::info( 'Successfully emailed developer usage info.' );
			}

			ABD_Log::perf_summary( 'ABD_Click_Handler::send_usage_info()', $start_time, $start_mem );

			self::redirect( 'send_usage_info_success' );
		}


		public static function delete_all_statistics() {
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			check_admin_referer( 'user instructed deletion of all statistics table rows' );

			$res = ABD_Database::delete_all_stats();

			ABD_Log::perf_summary( 'ABD_Click_Handler::send_usage_info()', $start_time, $start_mem );

			self::redirect( 'delete_all_stats_success' );
		}


		



		protected static function redirect( $query_arg_value = 'generic success', $query_arg = 'msg-code' ) {
			$url = $_SERVER['HTTP_REFERER'];

			$url = add_query_arg( $query_arg, $query_arg_value, $url );


			header( 'Location: ' . $url );
			exit;
		}
	}	//	end class
}	//	end if( !class_exists( ...