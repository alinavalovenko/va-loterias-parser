<?php
/*
	Plugin Name: LotteAds
	Description: Convert XML offers into WP posts
	Version: 1.0
	Author: Alina Valovenko
	Author URI: http://www.valovenko.pro
	License: GPL2
*/

/**
 * This plugin is used for process the XML document received through the API request.
 * The data converts into a 'lottery' post type and store in the database.
 * Data synchronization occurs on schedule every day at 01:00.
 * Ability to start synchronization on the settings page has also implemented
 */

if ( ! class_exists( "Loterias_XML_Parser" ) ) {

	require_once( 'core/class-lxp-admin.php' );
	require_once( 'core/class-lxp-connector.php' );

	class Loterias_XML_Parser {

		function __construct() {

			DEFINE( 'LXP_DIR', plugin_dir_path( __FILE__ ) );
			DEFINE( 'LXP_URL', plugin_dir_url( __FILE__ ) );
			DEFINE( 'LXP_NAME', 'LotteAds' );
			DEFINE( 'LXP_SLUG', 'loterias-xml-parser' );
			DEFINE( 'LXP_CORE', LXP_DIR . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR );
			DEFINE( 'LXP_VIEW', LXP_DIR . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR );
			DEFINE( 'LXP_TEMP', LXP_DIR . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR );
			DEFINE( 'LXP_CSS', LXP_URL . 'assets/css/' );
			DEFINE( 'LXP_JS', LXP_URL . 'assets/js/' );

			register_activation_hook( plugin_basename( __FILE__ ), array( $this, 'lxp_activate' ) );
			register_deactivation_hook( plugin_basename( __FILE__ ), array( $this, 'lxp_deactivate' ) );
			register_uninstall_hook( plugin_basename( __FILE__ ), array( 'Loterias_XML_Parser', 'lxp_uninstall' ) );

			add_action( 'init', array( &$this, 'lxp_register_post_type' ) );
			add_action( 'add_meta_boxes', array( &$this, 'lxp_add_custom_fields' ), 1 );
			add_action( 'save_post_lottery', array( &$this, 'save_post_lottery_callback' ) );

			add_action( 'loterias_xml_parser_cron_event', array( $this, 'run_lxp_api_connector' ) );

			$settings_page = new LXP_Admin();
		}

		public function lxp_activate() {
			wp_clear_scheduled_hook( 'loterias_xml_parser_cron_event' );
			if ( ! wp_next_scheduled( 'loterias_xml_parser_cron_event' ) ) {
				wp_schedule_event( time(), 'hourly', 'loterias_xml_parser_cron_event' );
			}
		}

		public function lxp_deactivate() {
			wp_clear_scheduled_hook( 'loterias_xml_parser_cron_event' );

			return true;
		}

		public function lxp_uninstall() {
			wp_clear_scheduled_hook( 'loterias_xml_parser_cron_event' );
		}

		/**
		 * Register 'lottery' post type
		 */
		function lxp_register_post_type() {
			register_post_type( 'lottery', array(
				'labels'             => array(
					'name'          => 'LotteAds', // Основное название типа записи
					'singular_name' => 'LotteAd', // отдельное название записи типа Book
					'add_new'       => 'Add new',
					'add_new_item'  => 'Add new LotteAd',
					'edit_item'     => 'Edit LotteAd',
					'new_item'      => 'New LotteAd',
					'view_item'     => 'View LotteAd',
					'search_items'  => 'Find LotteAd',
					'menu_name'     => 'LotteAds'

				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail' )
			) );
		}

		/**
		 * Add Meta box for 'lottery' post type
		 */
		function lxp_add_custom_fields() {
			add_meta_box( 'extra_fields', 'LotteAds properties', array(
				$this,
				'entry_properties_callback'
			), 'lottery', 'normal', 'high' );

		}

		/**
		 * Markup of custom meta fields
		 *
		 * @param $post
		 */
		function entry_properties_callback( $post ) {
			?>
            <div class="lpx-metabox">
                <label>Lottery id: <input readonly="readonly" type="text" name="entry_properties[lottery_id]"
                                          value="<?php echo get_post_meta( $post->ID, 'lottery_id', 1 ); ?>"/></label>
                <label>Lottery id (link): <input type="text" name="entry_properties[link_id]"
                                                 value="<?php echo get_post_meta( $post->ID, 'link_id', 1 ); ?>"/></label>
                <label>Lottery logo: <input type="text" name="entry_properties[lottery_logo]"
                                            value="<?php echo get_post_meta( $post->ID, 'lottery_logo', 1 ); ?>"/></label>
                <label>Last draw date: <input type="text" name="entry_properties[last_draw_date]"
                                              value="<?php echo get_post_meta( $post->ID, 'last_draw_date', 1 ); ?>"/></label>
                <label>Last draw results: <input type="text" name="entry_properties[last_draw_results]"
                                                 value="<?php echo get_post_meta( $post->ID, 'last_draw_results', 1 ); ?>"/></label>
                <label>Next draw date: <input type="text" name="entry_properties[last_draw_results]"
                                              value="<?php echo get_post_meta( $post->ID, 'next_draw_date', 1 ); ?>"/></label>
                <label>Next draw jackpot: <input type="text" name="entry_properties[next_draw_jackpot]"
                                                 value="<?php echo get_post_meta( $post->ID, 'next_draw_jackpot', 1 ); ?>"/></label>
                <label>Next draw close date: <input type="text" name="entry_properties[next_draw_close_date]"
                                                    value="<?php echo get_post_meta( $post->ID, 'next_draw_close_date', 1 ); ?>"/></label>
                <label>Play link: <input type="text" name="entry_properties[play_link]"
                                         value="<?php echo get_post_meta( $post->ID, 'play_link', 1 ); ?>"/></label>

            </div>
			<?php
		}

		/**
		 * @param $post_id
		 *
		 * Update value of 'lottery' entity
		 *
		 * @return $post_id
		 */
		function save_post_lottery_callback( $post_id ) {
			//check if entry_properties were pass
			if ( isset( $_POST['entry_properties'] ) ) {
				$data = $_POST['entry_properties'];
				foreach ( $data as $key => $value ) {
					update_post_meta( $post_id, $key, $value );
				}
			}

			return $post_id;
		}

		/**
		 * Cron action - run sync on a schedule
		 */
		function run_lxp_api_connector() {
			try {
				$options = get_option( LXP_SLUG . '_option' );
				$api_url = 'https://www.thelotter.com/rss.xml?languageId=2&lotteryIds=11,25,60,88,99,113,146,151,152,153,161,162,174,193,194,195,205&tl_affid=9578&chan=loteriasonline';
				if ( ! empty( $options['lxp-domain'] ) ) {
					$api_url = $options['lxp-domain'] .
					           '?languageId=' . $options['lxp-language-id'] .
					           '&tl_affid=' . $options['lxp-tl-aff-id'] .
					           '&chan=' . $options['lxp-chan-id'];
				}
				$status = new Lxp_Connector( $api_url );
				error_log( $status );
			} catch ( Exception $ex ) {
				error_log( $ex->getMessage() );
			}
		}
	}
}
global $lotteAds;
$lotteAds = new Loterias_XML_Parser();