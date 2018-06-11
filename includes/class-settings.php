<?php

// check if called directly
if( !defined( 'ABSPATH' ) ){ exit; }


if( !class_exists('RT_Profile_Notifications_settings') ){

	class RT_Profile_Notifications_settings {

		private $options;
		private $fields;

		/**
		 * Constructor
		 */
		public function __construct(){
			// get the current settings
			$this->options = get_option( 'rt_profile_notifications_settings' );
		}


		/**
		 * Init function that adds functions to hooks
		 */
		public function init( $fields ){
			// register the settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			// remember the available fields
			$this->fields = $fields;
		}


		public function register_settings(){
			register_setting(
				'general', // Option group
				'rt_profile_notifications_settings', // Option name
				array( $this, 'sanitize' ) // Sanitize
			);

			add_settings_section(
				'rt_profile_notifications_section', // ID
				__( 'Profile notification settings', 'rt-profile-notifications' ), // Title
				array( $this, 'section_info_main' ), // Callback
				'general' // Page
			);  

			add_settings_field(
				'active_fields', // ID
				__( 'Send notifications when these fields change', 'rt-profile-notifications' ), // Title 
				array( $this, 'active_fields_callback' ), // Callback
				'general', // Page
				'rt_profile_notifications_section' // Section           
			);

			add_settings_field(
				'email_address', // ID
				__( 'Notification email address', 'rt-profile-notifications' ), // Title 
				array( $this, 'email_address_callback' ), // Callback
				'general', // Page
				'rt_profile_notifications_section' // Section           
			);

		}


		public function section_info_main(){
			echo __( 'Please set your preferences below.', 'rt-profile-notifications' );
		}


		public function active_fields_callback(){
			echo '<fieldset>';
			foreach( $this->fields as $key=>$field ){
				echo '<label for="active_fields[' . $key . ']">';
				echo '<input type="checkbox" id="active_fields[' . $key . ']" name="rt_profile_notifications_settings[active_fields][' . $key . ']" value="' . $key . '" ';
				if( in_array( $key, (array) $this->options['active_fields'] ) ){
					echo 'checked="checked"';
				}
				echo ' />';
				echo '&nbsp;' . $field . '</label><br />';
			}
			echo '</fieldset>';
		}


		public function email_address_callback(){
			printf(
				'<input type="email" id="email_address" name="rt_profile_notifications_settings[email_address]" value="%s" class="regular-text ltr" />',
				$this->options['email_address']
			);
		}


		public function sanitize( $input ){
			$new_input = array();
			
			// store which fields to send notifications for
			$active_fields = array();
			if( is_array( $input['active_fields'] ) ){
				foreach( $input['active_fields'] as $field ){
					$active_fields[] = sanitize_text_field( $field );
				}
			}
			$new_input['active_fields'] = $active_fields;

			// store the email address
			$new_input['email_address'] = is_email( $input['email_address'] ) ? $input['email_address'] : null ;

			return $new_input;
		}

	}

}


?>