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


// include the options page
require_once( 'includes/class-settings.php' );


if( !class_exists('RT_Profile_Notifications') ){

	class RT_Profile_Notifications {

		private $fields;
		private $settings_class;

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
			// instantiate the settings class
			$this->settings_class = new RT_Profile_Notifications_settings();
			$this->settings_class->init( $this->fields );
		}


		public function on_profile_update( $user_id, $old_user_data ){
			// get the options
			$options = get_option( 'rt_profile_notifications_settings' );
			// empty changes string
			$changes = '';
			// get the current user object
			$new_user_data = get_userdata( $user_id );
			// loop through fields, check for changes
			foreach( $this->fields as $key => $label ){
				if( $new_user_data->{$key} != $old_user_data->{$key} && in_array( $key, $options['active_fields'] ) ){
					if( $key != 'user_pass' ){
						$changes .= $label . ': ' . $old_user_data->{$key} . ' -> ' . $new_user_data->{$key} . "\n";
					} else {
						$changes .= $label . ': (hidden) - (hidden)' . "\n";
					}
				}
			}
			// send mail
			if( !empty( $changes ) ){
				$options = get_option( 'rt_profile_notifications_settings' );
				$email = $options[ 'email_address' ];
				if( !is_email( $email ) ){
					$email = get_option('admin_email');
				}
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