<style type="text/css">
	.wp-list-table .column-email {
		width: 25% !important;
	}
	.wp-list-table .column-plugin {
		width: 25% !important;
	}
	.wp-list-table .column-status {
		width: 20% !important;
	}
	.wp-list-table .column-created_at {
		width: 15% !important;
		text-align: center;
	}
	.wp-list-table .column-updated_at {
		width: 15% !important;
		text-align: center;
	}
	.row-actions {
		visibility : visible !important;
	}
</style>
<div class="wrap">
	<h2 class="nav-tab-wrapper" style="padding-left:0px !important;">
		<?php
		$tab = isset($_GET['tab']) ? $_GET['tab'] : 'licenses';
		?>
		<a class="nav-tab <?php if($tab == 'licenses') { echo 'nav-tab-active'; }?>" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=licenses">
			<span class="dashicons dashicons-admin-network"></span> Licenses
		</a>
		<a class="nav-tab <?php if($tab == 'categories') { echo 'nav-tab-active'; }?>" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=categories">
			<span class="dashicons dashicons-category"></span> Categories
		</a>
		<a class="nav-tab <?php if($tab == 'plugins') { echo 'nav-tab-active'; }?>" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=plugins">
			<span class="dashicons dashicons-admin-plugins"></span> Plugins
		</a>
	</h2>
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2>
				<span class="dashicons dashicons-admin-network"></span> Licenses
				<a class="add-new-h2" href="?page=<?php echo KREAXYLICENSES_SLUG; ?>&tab=licenses&node=license">Add New</a>
			</h2>
			<div class="welcome-panel-column-container">
				<p>
					<form action="" method="POST">
						<?php
							$tables = new licensesTable();
							$tables->prepare_items();
							$tables->search_box( 'search', 'search_id' );
							$tables->display();
						?>
					</form>
				</p>
			</div>
		</div>
	</div>
</div>
