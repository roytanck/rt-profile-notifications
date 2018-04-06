<?php
/*
	Plugin Name: RT Profile Notifications
	Plugin URI:  http://www.this-play.nl
	Description: Send notification e-mails when a user updates their profile info
	Version:     1.0
	Author:      Roy Tanck
	Author URI:  http://www.this-play.nl
	Text Domain: rt-profile-notifications
	Domain path: /languages
	License:     GPL
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('RT_Profile_Notifications') ){

	class RT_Profile_Notifications {

		private $fields;

		/**
		 * Constructor
		 */
		function __construct() {
			$this->fields = array(
				'display_name' => __( 'Display name', 'rt-profile-notifications' ),
				'user_email' => __( 'Email address', 'rt-profile-notifications' ),
				'user_url' => __( 'Website URL', 'rt-profile-notifications' ),
				'user_pass' => __( 'Password', 'rt-profile-notifications' )
			);
		}


		public function init(){
			// hook into profile updates
			add_action( 'profile_update', array( $this, 'on_profile_update' ), 10, 2 ); 
		}


		public function on_profile_update( $user_id, $old_user_data ){
			// empty changes string
			$changes = '';
			// get the current user object
			$new_user_data = get_userdata( $user_id );
			// loop through fields, check for changes
			foreach( $this->fields as $key => $label ){
				if( $new_user_data->{$key} != $old_user_data->{$key} ){
					if( $key != 'user_pass' ){
						$changes .= $label . ': ' . $old_user_data->{$key} . ' -> ' . $new_user_data->{$key} . "\n";
					} else {
						$changes .= $label . ': (hidden) - (hidden)' . "\n";
					}
				}
			}
			// send mail
			if( !empty( $changes ) ){
				$email = get_option('admin_email');
				$subject = get_option('blogname') . ' - ' . __('User profile updated.','rt-profile-notifications');
				$message = sprintf( __( "User '%s' has updated their profile.", 'rt-profile-notifications' ), $old_user_data->user_login ) . "\n\n";
				$message .= $changes;
				wp_mail( $email, $subject, $message );
			}
		}


		/**
		 * Load the translated strings
		 */
		function load_textdomain(){
			load_plugin_textdomain( 'rt-profile-notifications', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

	}

	// create an instance of the class
	$rt_profile_notications = new RT_Profile_Notifications();
	$rt_profile_notications->init();

}

?>