<?php
	global $wpdb;
	$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';

	$idCategory = '';
	$name = '';
	$buttonText = 'Add Category';
	$suggestionLink = '';
	
	if ( isset( $_GET['category'] ) ) {
		$idCategory = sanitize_text_field( $_GET['category'] );
	}
	
	$category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tablePluginCategories WHERE id = %d AND deleted_at IS NULL", $idCategory ) );
	if ( !empty( $category ) ) {
		$name = $category->name;
		$buttonText = 'Update Category';
		$suggestionLink = '<a class="add-new-h2" href="?page=' . KREAXYLICENSES_SLUG . '&tab=categories&node=category">Add New</a>';
	}
?>
<div class="wrap">
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2>
				<span class="dashicons dashicons-plus-alt"></span> Add Category
				<a class="add-new-h2" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=categories">Back</a>
				<?php echo $suggestionLink; ?>
			</h2>
			<div class="welcome-panel-column-container">
				<p>
					<form action="" method="POST">
						<table style="border:1px groove #eeeeee;width:100%;padding:5px;margin-bottom:5px;">
							<tr>
								<td style="width:250px;">Name</td>
								<td>
									<input type="text" name="kl_category_email" style="width:50%" value="<?php echo $name; ?>" />
								</td>
							</tr>
						</table>
						<p>
							<?php wp_nonce_field( 'kl_add_plugin_category', 'kl_submit_add_plugin_category_nonce' ); ?>
							<input type="hidden" name="kl_hidden_id_plugin_category" value="<?php echo $idCategory; ?>">
							<input type="submit" name="kl_submit_add_plugin_category" class="button-primary" value="<?php echo $buttonText; ?>"/>
						</p>
					</form>
				</p>
			</div>
		</div>
	</div>
</div>
