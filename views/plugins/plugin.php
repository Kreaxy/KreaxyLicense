<?php
	global $wpdb;
	$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';
	$tablePlugins = $wpdb->prefix . 'kl_plugins';

	$pluginCategories = $wpdb->get_results( "SELECT * FROM $tablePluginCategories WHERE deleted_at IS NULL ORDER BY name ASC" );

	$idPlugin = '';
	$idCategory = '';
	$name = '';
	$slug = '';
	$buttonText = 'Add Plugin';
	$suggestionLink = '';
	
	if ( isset( $_GET['plugin'] ) ) {
		$idPlugin = sanitize_text_field( $_GET['plugin'] );
	}
	
	$plugin = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tablePlugins WHERE id = %d AND deleted_at IS NULL", $idPlugin ) );
	if ( !empty( $plugin ) ) {
		$idCategory = $plugin->id_category;
		$name = $plugin->name;
		$slug = $plugin->slug;
		$buttonText = 'Update Plugin';
		$suggestionLink = '<a class="add-new-h2" href="?page=' . KREAXYLICENSES_SLUG . '&tab=plugins&node=plugin">Add New</a>';
	}
?>
<div class="wrap">
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2>
				<span class="dashicons dashicons-plus-alt"></span> Add Plugin
				<a class="add-new-h2" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=plugins">Back</a>
				<?php echo $suggestionLink; ?>
			</h2>
			<div class="welcome-panel-column-container">
				<p>
					<form action="" method="POST">
						<table style="border:1px groove #eeeeee;width:100%;padding:5px;margin-bottom:5px;">
							<tr>
								<td>Category</td>
								<td>
									<select name="kl_plugin_category" style="width:50%;">
										<?php
											if ( !empty( $pluginCategories ) ) {
												foreach ( $pluginCategories as $pluginCategory ) {
													$selected = '';
													if ( $pluginCategory->id == $idCategory ) {
														$selected = "selected='selected'";
													}
													echo '<option value="' . $pluginCategory->id . '" ' . $selected . '>' . $pluginCategory->name . '</option>';
												}
											}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Name</td>
								<td>
									<input type="text" name="kl_plugin_name" style="width:50%" value="<?php echo $name; ?>" />
								</td>
							</tr>
							<tr>
								<td>Slug</td>
								<td>
									<input type="text" name="kl_plugin_slug" style="width:30%" value="<?php echo $slug; ?>" />
								</td>
							</tr>
						</table>
						<p>
							<?php wp_nonce_field( 'kl_add_plugin', 'kl_submit_add_plugin_nonce' ); ?>
							<input type="hidden" name="kl_hidden_id_plugin" value="<?php echo $idPlugin; ?>">
							<input type="submit" name="kl_submit_add_plugin" class="button-primary" value="<?php echo $buttonText; ?>"/>
						</p>
					</form>
				</p>
			</div>
		</div>
	</div>
</div>
