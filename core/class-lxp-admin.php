<?php
require_once ('class-lxp-connector.php');
class LXP_Admin {
	private $options;

	public function __construct() {
		$this->options = get_option( LXP_SLUG . '_opstion' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action('admin_enqueue_scripts', array( $this,'custom_metabox_styles'), 11);
		add_action( 'admin_menu', array( $this, 'lpx_add_admin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'wp_ajax_lpx_save_options', array( $this, 'save_options' ) );
		add_action( 'wp_ajax_lpx_update_date', array( $this, 'update_data' ) );
	}

	public function lpx_add_admin_page() {
		add_menu_page(
			LXP_NAME . 'Settings',
			LXP_NAME . ' Settings',
			'manage_options',
			LXP_SLUG,
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Create plugin settings page
	 *
	 * @return void
	 */
	public function create_admin_page() {
		$this->enqueue_scripts();
		$this->options = get_option( LXP_SLUG . '_option' );
		require_once( LXP_VIEW . 'settings.php' );
	}

	public function enqueue_scripts() {
		wp_register_style( LXP_SLUG . '-bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css' );
		wp_register_script( LXP_SLUG . '-bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array( 'jquery' ), '4.1', true );
		wp_register_script( LXP_SLUG . '-scripts', LXP_URL . '/assets/js/scripts.js', array( 'jquery' ), '0.1', true );

		wp_enqueue_style( LXP_SLUG . '-bootstrap-css' );
		wp_enqueue_style( LXP_SLUG . '-styles', LXP_URL . 'assets/css/styles.css' );
		wp_enqueue_script( LXP_SLUG . '-bootstrap-js' );
		wp_enqueue_script( LXP_SLUG . '-scripts' );

		wp_localize_script( LXP_SLUG . '-scripts', 'lxpObject',
			array(
				'url' => admin_url( 'admin-ajax.php' )
			) );
	}

	public function custom_metabox_styles(){
		wp_enqueue_style( LXP_SLUG . '-styles', LXP_URL . 'assets/css/styles.css' );
	}

	public function page_init() {
		register_setting(
			LXP_SLUG . '_option_group',
			LXP_SLUG . 'lxp_option',
			array( $this, 'sanitize' )
		);


		add_settings_section(
			LXP_SLUG . '_option',
			'LotteAds Settings',
			array( $this, 'print_section_info' ),
			LXP_SLUG
		);

		add_settings_field(
			'lxp-api-url',
			'Full api url',
			array( $this, 'lxp_api_url_callback' ),
			LXP_SLUG,
			LXP_SLUG . '_option'
		);

		add_settings_field(
			'lxp-domain',
			'Domain',
			array( $this, 'lxp_domain_callback' ),
			LXP_SLUG,
			LXP_SLUG . '_option'
		);

		add_settings_field(
			'lxp-language-id',
			'Language id',
			array( $this, 'lxp_language_id_callback' ),
			LXP_SLUG,
			LXP_SLUG . '_option'
		);

		add_settings_field(
			'lxp-tl-aff-id',
			'Affiliate id',
			array( $this, 'lxp_affiliate_id_callback' ),
			LXP_SLUG,
			LXP_SLUG . '_option'
		);

		add_settings_field(
			'lxp-chan-id',
			'Chan',
			array( $this, 'lxp_chan_callback' ),
			LXP_SLUG,
			LXP_SLUG . '_option'
		);


	}

	/**
	 * Sanitize fields value
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
	}

	public function print_section_info() {
		echo '';
	}

	public function lxp_api_url_callback() {
		printf(
			'<input type="text"   name="' . LXP_SLUG . '_option[lxp-api-url]" value="%s"/>',
			isset( $this->options['lxp-api-url'] ) ? esc_attr( $this->options['lxp-api-url'] ) : 'https://www.thelotter.com/rss.xml?languageId=2&tl_affid=8831&chan=loteriasonline'
		);
	}
	public function lxp_domain_callback() {
		printf(
			'<input type="text"   name="' . LXP_SLUG . '_option[lxp-domain]" value="%s"/>',
			isset( $this->options['lxp-domain'] ) ? esc_attr( $this->options['lxp-domain'] ) : 'https://www.thelotter.com/rss.xml'
		);
	}

	public function lxp_language_id_callback() {
		printf(
			'<input type="text"   name="' . LXP_SLUG . '_option[lxp-language-id]" value="%s"/>',
			isset( $this->options['lxp-language-id'] ) ? esc_attr( $this->options['lxp-language-id'] ) : '2'
		);
	}

	public function lxp_affiliate_id_callback() {
		printf(
			'<input type="text"   name="' . LXP_SLUG . '_option[lxp-tl-aff-id]" value="%s"/>',
			isset( $this->options['lxp-tl-aff-id'] ) ? esc_attr( $this->options['lxp-tl-aff-id'] ) : '8831'
		);
	}

	public function lxp_chan_callback() {
		printf(
			'<input type="text"   name="' . LXP_SLUG . '_option[lxp-chan-id]" value="%s"/>',
			isset( $this->options['lxp-chan-id'] ) ? esc_attr( $this->options['lxp-chan-id'] ) : 'loteriasonline'
		);
	}

	function save_options() {
		try {
			$options['lxp-api-url']      = $_POST['apiurl'];
			$options['lxp-domain']      = $_POST['domain'];
			$options['lxp-language-id'] = $_POST['langid'];
			$options['lxp-tl-aff-id']   = $_POST['affid'];
			$options['lxp-chan-id']     = $_POST['chan'];
			update_option( LXP_SLUG . '_option', $options, 'no' );
			echo 'Setting saved!';
		} catch ( Exception $ex ) {
			echo $ex->getMessage();
		}
		wp_die();
	}

	function update_data() {
		try {
			$options = get_option(LXP_SLUG . '_option');
			$api_url = 'https://www.thelotter.com/rss.xml?languageId=2&tl_affid=8831&chan=loteriasonline';
			if(!empty($options['lxp-api-url'])){
				$api_url = $options['lxp-api-url'];
			} elseif ( empty($options['lxp-domain'] ) ) {
				$api_url = $options['lxp-domain'] .
				           '?languageId=' . $options['lxp-language-id'] .
				           '&tl_affid=' . $options['lxp-tl-aff-id'] .
				           '&chan=' . $options['lxp-chan-id'];
			}
			$xml_content = new Lxp_Connector($api_url);
			echo $xml_content->data;
		} catch ( Exception $ex ) {
			echo $ex->getMessage();
		}
		wp_die();
	}

}