<?php
class ApiUtilities {
	private $tablePlugins;
	
	public function __construct() {
		global $wpdb;
		$this->tablePlugins = $wpdb->prefix . 'kl_plugins';
	}

	public function getPluginSlug( $idPlugin ) {
		global $wpdb;
		$plugin = $wpdb->get_row( $wpdb->prepare( "SELECT slug FROM $this->tablePlugins WHERE id = %d AND deleted_at IS NULL", $idPlugin ) );
		
		$pluginSlug = '';
		if ( !empty( $plugin ) ) {
			$pluginSlug = $plugin->slug;
		}
		return $pluginSlug;
	}

	public function getIdPlugin( $pluginSlug ) {
		global $wpdb;
		$plugin = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $this->tablePlugins WHERE slug = %s AND deleted_at IS NULL", $pluginSlug ) );
		
		$idPlugin = '';
		if ( !empty( $plugin ) ) {
			$idPlugin = $plugin->id;
		}
		return (int)$idPlugin;
	}
}