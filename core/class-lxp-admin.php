<?php

class LXP_Admin {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'lpx_add_admin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}
}