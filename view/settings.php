<h1>Plugin Title</h1>
<form method="post" action=""  id="lxp-submit-form">
	<?php settings_fields( LXP_SLUG . '_option_group' ); ?>
	<?php do_settings_sections( LXP_SLUG ); ?>
	<?php submit_button('Save settings'); ?>
</form>