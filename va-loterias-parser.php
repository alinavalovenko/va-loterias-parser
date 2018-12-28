<?php
/*
	Plugin Name: Loterias XML Parser
	Description: Convert XML offers into WP posts
	Version: 1.0
	Author: Alina Valovenko
	Author URI: http://www.valovenko.pro
	License: GPL2
*/
if ( ! class_exists( "Loterias_XML_Parser" ) ) {
	require_once ('core/class-lxp-admin.php');

    class Loterias_XML_Parser
    {
        function __construct()
        {
            DEFINE('LXP_DIR', plugin_dir_path(__FILE__));
            DEFINE('LXP_URL', plugin_dir_url(__FILE__));
            DEFINE('LXP_NAME', 'Loterias XML Parser');
            DEFINE('LXP_SLUG', 'loterias-xml-parser');
            DEFINE('LXP_CORE', LXP_DIR . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR);
            DEFINE('LXP_VIEW', LXP_DIR . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);
            DEFINE('LXP_TEMP', LXP_DIR . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
            DEFINE('LXP_CSS', LXP_URL . 'assets/css/');
            DEFINE('LXP_JS', LXP_URL . 'assets/js/');

            register_activation_hook(LXP_NAME, array($this, 'activate'));
            register_deactivation_hook(LXP_NAME, array($this, 'deactivate'));
            register_uninstall_hook( LXP_NAME, array( &$this, 'uninstall' ) );

	        $page = new LXP_Admin();
        }
        
        public function activate(){
        	
        }

	    public function deactivate(  ) {
		    return true;
        }

	    public function uninstall(  ) {
		    
        }
    }

    new Loterias_XML_Parser();
}
