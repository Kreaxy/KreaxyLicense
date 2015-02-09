<?php
	global $wpdb;
	$tableLicenses = $wpdb->prefix . 'kl_licenses';
	$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';
	$tablePlugins = $wpdb->prefix . 'kl_plugins';

	$pluginCategories = $wpdb->get_results( "SELECT * FROM $tablePluginCategories WHERE deleted_at IS NULL ORDER BY name ASC" );

	$idLicense = '';
	$idPlugin = '';
	$name = '';
	$slug = '';
	$buttonText = 'Add License';
	$suggestionLink = '';
	
	if ( isset( $_GET['license'] ) ) {
		$idLicense = sanitize_text_field( $_GET['license'] );
	}
	
	$license = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tableLicenses WHERE id = %d AND deleted_at IS NULL", $idLicense ) );
	if ( !empty( $license ) ) {
		$idPlugin = $license->id_plugin;
		$email = $license->email;
		$buttonText = 'Update License';
		$suggestionLink = '<a class="add-new-h2" href="?page=' . KREAXYLICENSES_SLUG . '&tab=licenses&node=license">Add New</a>';
	}
?>
<div class="wrap">
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2>
				<span class="dashicons dashicons-plus-alt"></span> Add License
				<a class="add-new-h2" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=licenses">Back</a>
				<?php echo $suggestionLink; ?>
			</h2>
			<div class="welcome-panel-column-container">
				<p>
					<form action="" method="POST">
						<table style="border:1px groove #eeeeee;width:100%;padding:5px;margin-bottom:5px;">
							<tr>
								<td>Plugin</td>
								<td>
									<select name="kl_license_plugin" style="width:50%;">
										<?php
											if ( !empty( $pluginCategories ) ) {
												foreach ( $pluginCategories as $pluginCategory ) {
													echo '<optgroup label="' . $pluginCategory->name . '">';
													$plugins = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tablePlugins WHERE id_category = %d AND deleted_at IS NULL ORDER BY name ASC", $pluginCategory->id ) );
													if ( !empty( $plugins ) ) {
														foreach ( $plugins as $plugin ) {
															$selected = '';
															if ( $plugin->id == $idPlugin ) {
																$selected = "selected='selected'";
															}
															echo '<option value="' . $plugin->id . '" ' . $selected . '>' . $plugin->name . '</option>';
														}
													}
													echo '</optgroup>';
												}
											}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td style="width:250px;">Email</td>
								<td>
									<input type="email" name="kl_license_email" style="width:50%" value="<?php echo $email; ?>" />
								</td>
							</tr>
						</table>
						<p>
							<?php wp_nonce_field( 'kl_add_license', 'kl_submit_add_license_nonce' ); ?>
							<input type="hidden" name="kl_hidden_id_license" value="<?php echo $idLicense; ?>">
							<input type="submit" name="kl_submit_add_license" class="button-primary" value="<?php echo $buttonText; ?>"/>
						</p>
					</form>
				</p>
			</div>
		</div>
	</div>
</div>
