<?php
class gcgoai_OpenAI_Admin {
	private static $initiated = false;
	private static $notices   = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {

		self::$initiated = true;

		add_action( 'admin_menu', array( 'gcgoai_OpenAI_Admin', 'admin_menu' ), 5 );
		add_action( 'admin_enqueue_scripts', array( 'gcgoai_OpenAI_Admin', 'styles_and_scripts_enqueue' ) );
	}
	public static function admin_menu() {

		add_options_page( __('WP OpenAI', 'wpopenai'), __('Wp OpenAI', 'wpopenai'), 'manage_options', 'wpopenai-configkey', array( 'gcgoai_OpenAI_Admin', 'show_plugin_page' ) );
		
	}

	public static function show_plugin_page() {
		?>
		<div id="wpopenai-plugin-container">
			<div class="wpopenai-masthead">
				<div class="wpopenai-masthead__inside-container">
					<div class="wpopenai-masthead__logo-container">
						<img class="wpopenai-masthead__logo" src="<?php echo esc_url( GCGOAI_PLUGIN_PATH .'assets/images/blue-dark-logo.png'); ?>" alt="OpenAi" />
					</div>
				</div>
			</div>
			<div class="wpopenai-lower">
				<div class="wpopenai-boxes">
					
				<?php gcgoai_OpenAI::view( 'enterdata' ); ?>
				
				</div>
			</div>
		</div>
		<?php
	}

	public static function styles_and_scripts_enqueue() {
		wp_register_style( 'wpopenai.css', GCGOAI_PLUGIN_PATH . 'assets/css/wpopenai.css', array(), GCGOAI_VERSION );
		wp_enqueue_style( 'wpopenai.css');

		wp_register_script( 'wpopenai.js', GCGOAI_PLUGIN_PATH . 'assets/js/wpopenai.js', array('jquery'), GCGOAI_VERSION );
		wp_enqueue_script( 'wpopenai.js' );

	}

}