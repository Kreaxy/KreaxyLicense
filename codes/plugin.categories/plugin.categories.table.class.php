<?php
if ( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class pluginCategoriesTable extends WP_List_Table {
	public function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular' => 'plugincategory',
			'plural' => 'plugincategories',
			'ajax' => false
		));
  }

	private function plugincategories_data() {
		global $wpdb;
		$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';

		if ( isset( $_POST['s'] ) ) {
			$searchString = sanitize_text_field( $_POST['s'] );
		} else {
			$searchString = '';
		}

		if ( empty( $searchString ) ) {
			$query = "SELECT * FROM $tablePluginCategories WHERE deleted_at IS NULL";
			$orderby = !empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'updated_at';
			$order = !empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
			if ( !empty( $orderby ) && !empty( $order ) ) { $query .=' ORDER BY ' . $orderby . ' ' . $order; }
			$getDatas = $wpdb->get_results( $query );
		} else {
			$query = "SELECT * FROM $tablePluginCategories WHERE name LIKE '%$searchString%' AND deleted_at IS NULL ORDER BY name ASC";
			$getDatas = $wpdb->get_results( $query );
		}

		if ( !empty( $getDatas ) ) {
			foreach ( $getDatas as $getData ) {
				$tableFields[] = array(
					'id' => $getData->id,
					'name' => $getData->name,
					'created_at' => $this->relative_time( strtotime( $getData->created_at ) ),
					'updated_at' => $this->relative_time( strtotime( $getData->updated_at ) )
				);
			}
		} else {
			$tableFields = array();
		}

		return $tableFields;
	}
	
	private function relative_time( $ptime ) {
		$etime = time() - $ptime;

		if ($etime < 1) {
		return 'just now';
		}

		$a = array( 12 * 30 * 24 * 60 * 60 => 'year',
		30 * 24 * 60 * 60 => 'month',
		24 * 60 * 60 => 'day',
		60 * 60 => 'hour',
		60 => 'minute',
		1 => 'second'
		);

		foreach ($a as $secs => $str) {
			$d = $etime / $secs;
			if ($d >= 1) {
			$r = round($d);
			return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
			}
		}
	}
	
	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => 'Name',
			'updated_at' => 'Last Updated'
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', false )
		);
		return $sortable_columns;
	}
	
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
			case 'updated_at':
				return $item[$column_name];
			default:
				return print_r( $item, true );
		}
	}

	public function column_name( $item ) {
		$actions = array(
			'edit' => sprintf( '<span class="dashicons dashicons-editor-justify"></span> <a href="?page=%s&tab=%s&node=%s&category=%d">Edit</a>', KREAXYLICENSES_SLUG, 'categories', 'category', $item['id'] ),
			'trash' => sprintf('<span class="dashicons dashicons-trash"></span> <a href="#" class="kl_trash_category" data-id="%d" data-name="%s">Trash</a>', $item['id'], $item['name'] )
		);
		return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		$actions = array(
			'trash' => 'Trash'
		);
		return $actions;
	}

	public function column_cb( $item ) {
    return sprintf(
     	'<input type="checkbox" name="%1$s[]" value="%2$s" />',
      $this->_args['singular'],
      $item['id']
    );
  }

  public function process_bulk_action() {
    if ( 'trash' === $this->current_action() ) {
      if ( !empty( $_POST['plugincategory'] ) ) {
				$runDeleteCategories = false;
				foreach ( $_POST['plugincategory'] as $idCategory ) {
					global $wpdb;
					$tablePluginCategories = $wpdb->prefix. 'kl_plugin_categories';
					$wpdb->query( $wpdb->prepare( "UPDATE $tablePluginCategories SET deleted_at = NOW() WHERE id = %d", $idCategory ) );
				}
			} else {
				echo '<div class="error"><p>Please select the category.</p></div>';
			}
    }
	}
	
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$per_page = 10;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->plugincategories_data() );
		$this->found_data = array_slice( $this->plugincategories_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( array(
			'total_items' => $total_items, 
			'per_page' => $per_page
		) );
		$this->items = $this->found_data;
	}
}
?>