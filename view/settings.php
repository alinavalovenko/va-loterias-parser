<h1>Plugin Title</h1>
<form method="post" action=""  id="lxp-submit-form">
	<?php settings_fields( LXP_SLUG . '_option_group' ); ?>
	<?php do_settings_sections( LXP_SLUG ); ?>
	<button class="btn btn-primary" type="submit">Save Settings</button>
    <button id="lxp-update-data" type="button" class="btn btn-outline-info"">Update Data</button>
</form>