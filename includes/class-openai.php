<?php
class gcgoai_OpenAI {
	public static function plugin_activation() {
    	
	}
	public static function plugin_deactivation() {
		
	}
	public static function get_openai_apikey() {
		return apply_filters( 'openai_get_apikey', defined('OPENAI_APIKEY') ? constant('OPENAI_APIKEY') : get_option('gcgoai_wpopenai_key') );
	}

	public static function view( $name ) {
		$file = GCGOAI_PLUGIN_DIR . 'templates/'. $name . '.php';
		include( $file );
	}

}