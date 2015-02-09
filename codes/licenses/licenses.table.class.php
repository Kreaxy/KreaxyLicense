<?php
if ( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class licensesTable extends WP_List_Table {
	public function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular' => 'licens',
			'plural' => 'licenses',
			'ajax' => false
		));
  }

	private function plugins_data() {
		global $wpdb;
		$tableLicenses = $wpdb->prefix . 'kl_licenses';
		$tablePlugins = $wpdb->prefix . 'kl_plugins';

		if ( isset( $_POST['s'] ) ) {
			$searchString = sanitize_text_field( $_POST['s'] );
		} else {
			$searchString = '';
		}

		if ( empty( $searchString ) ) {
			$query = "SELECT lc.id, lc.email, lc.status, pl.name AS plugin, lc.created_at, lc.updated_at FROM $tableLicenses AS lc LEFT JOIN $tablePlugins AS pl
				ON lc.id_plugin = pl.id
				WHERE lc.deleted_at IS NULL";
			
			$fieldOrderBy = '';
			if ( isset( $_GET['orderby'] ) ) {
				if ( $_GET['orderby'] == 'plugin' ) {
					$fieldOrderBy = 'pl.name';
				}

				if ( $_GET['orderby'] == 'email' ) {
					$fieldOrderBy = 'lc.email';
				}
			}

			$orderby = !empty( $_GET['orderby'] ) ? $fieldOrderBy : 'lc.updated_at';
			$order = !empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
			if ( !empty( $orderby ) && !empty( $order ) ) { $query .=' ORDER BY ' . $orderby . ' ' . $order; }
			$getDatas = $wpdb->get_results( $query );
		} else {
			$query = "SELECT lc.id, lc.email, lc.status, pl.name AS plugin, lc.created_at, lc.updated_at FROM $tableLicenses AS lc LEFT JOIN $tablePlugins AS pl
				ON lc.id_plugin = pl.id
				WHERE lc.email LIKE '%$searchString%' OR pc.name LIKE '%$searchString%'
				AND lc.deleted_at IS NULL
				ORDER BY lc.email ASC";
			$getDatas = $wpdb->get_results( $query );
		}

		if ( !empty( $getDatas ) ) {
			foreach ( $getDatas as $getData ) {
				if ( is_null( $getData->status ) ) {
					$licenseStatus = 'Inactive';
				} else {
					$licenseStatus = 'Activated';
				}
				$tableFields[] = array(
					'id' => $getData->id,
					'email' => $getData->email,
					'plugin' => $getData->plugin,
					'status' => $licenseStatus,
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
			'email' => 'Email',
			'plugin' => 'Plugin',
			'status' => 'Status',
			'created_at' => 'Created',
			'updated_at' => 'Last Updated'
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'email' => array( 'email', false ),
			'plugin' => array( 'plugin', false )
		);
		return $sortable_columns;
	}
	
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'plugin':
			case 'status':
			case 'created_at':
			case 'updated_at':
				return $item[$column_name];
			default:
				return print_r( $item, true );
		}
	}

	public function column_email( $item ) {
		$actions = array(
			'edit' => sprintf( '<span class="dashicons dashicons-editor-justify"></span> <a href="?page=%s&tab=%s&node=%s&license=%d">Edit</a>', KREAXYLICENSES_SLUG, 'licenses', 'license', $item['id'] ),
			'trash' => sprintf('<span class="dashicons dashicons-trash"></span> <a href="#" class="kl_trash_license" data-id="%d" data-email="%s">Trash</a>', $item['id'], $item['email'] )
		);
		return sprintf( '%1$s %2$s', $item['email'], $this->row_actions( $actions ) );
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
      if ( !empty( $_POST['licens'] ) ) {
				foreach ( $_POST['licens'] as $idLicense ) {
					global $wpdb;
					$tableLicenses = $wpdb->prefix. 'kl_licenses';
					$wpdb->query( $wpdb->prepare( "UPDATE $tableLicenses SET deleted_at = NOW() WHERE id = %d", $idLicense ) );
				}
			} else {
				echo '<div class="error"><p>Please select the License.</p></div>';
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
		$total_items = count( $this->plugins_data() );
		$this->found_data = array_slice( $this->plugins_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( array(
			'total_items' => $total_items, 
			'per_page' => $per_page
		) );
		$this->items = $this->found_data;
	}
}
?>