<div class="lxp-wrapper">
    <div class="loader-wrap">
    <div class="loader"></div>
    </div>

    <form method="post" action="" id="lxp-submit-form">
		<?php settings_fields( LXP_SLUG . '_option_group' ); ?>
	    <?php $next_event = wp_next_scheduled('loterias_xml_parser_cron_event'); ?>
		<?php do_settings_sections( LXP_SLUG ); ?>
        <button class="btn btn-primary" type="submit">Save Settings</button>
        <button id="lxp-update-data" type="button" class="btn btn-outline-info"
        ">Update Data</button>
    </form>
    <div >Next Sync will be <strong><?php echo date( 'd-m-Y', $next_event); ?> at <?php echo date( 'h A', $next_event); ?> </strong></div>
    <div class="lxp-status">
        <div class="status-message-success">&#10003;<span> Complete<br/> All items were updated.</span></div>
        <div class="status-message-error">&#10005;<span> Error<br/> Please try again later.</span></div>
    </div>
</div>