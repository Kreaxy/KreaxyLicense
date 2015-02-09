<?php
/*
Plugin Name: Kreaxy Licenses
Plugin URI: http://kreaxy.com
Description: Licensing management for Kreaxy Digital Media's products.
Version: 0.1
Author: Kreaxy Digital Media
Author URI: http://kreaxy.com
 */

if ( ! defined( 'ABSPATH' ) ) die( 'Cheating, uh?' );

define( 'KREAXYLICENSES_VERSION', '0.1' );
define( 'KREAXYLICENSES_SLUG', 'kreaxylicenses' );
define( 'KREAXYLICENSES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

class KreaxyLicenses {
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'kl_activation' ) );
		add_action( 'admin_menu', array( $this, 'kl_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'kl_admin_init' ) );
	}

	public function kl_activation() {
		if ( !function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		global $wpdb;
		$tableLicenses = $wpdb->prefix . 'kl_licenses';
		$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';
		$tablePlugins = $wpdb->prefix . 'kl_plugins';

		$sqlLicenses = <<<SQL
CREATE TABLE {$tableLicenses} (
id INT(11) unsigned NOT NULL AUTO_INCREMENT,
id_plugin INT(11) NOT NULL,
email VARCHAR(255) NOT NULL,
status VARCHAR(28) NULL,
created_at DATETIME NOT NULL,
updated_at TIMESTAMP NOT NULL,
deleted_at DATETIME NULL,
PRIMARY KEY id (id)
) DEFAULT CHARACTER SET utf8, DEFAULT COLLATE utf8_general_ci;
SQL;
		dbDelta( $sqlLicenses );

		$sqlPluginCategories = <<<SQL
CREATE TABLE {$tablePluginCategories} (
id INT(11) unsigned NOT NULL AUTO_INCREMENT,
name VARCHAR(255) NOT NULL,
created_at DATETIME NOT NULL,
updated_at TIMESTAMP NOT NULL,
deleted_at DATETIME NULL,
PRIMARY KEY id (id)
) DEFAULT CHARACTER SET utf8, DEFAULT COLLATE utf8_general_ci;
SQL;
		dbDelta( $sqlPluginCategories );

		$sqlPlugins = <<<SQL
CREATE TABLE {$tablePlugins} (
id INT(11) unsigned NOT NULL AUTO_INCREMENT,
id_category INT(11) NOT NULL,
name VARCHAR(255) NOT NULL,
slug VARCHAR(255) NOT NULL,
created_at DATETIME NOT NULL,
updated_at TIMESTAMP NOT NULL,
deleted_at DATETIME NULL,
PRIMARY KEY id (id)
) DEFAULT CHARACTER SET utf8, DEFAULT COLLATE utf8_general_ci;
SQL;
		dbDelta( $sqlPlugins );
	}
	
	public function kl_admin_menu() {
		add_menu_page( __( 'Kreaxy Licenses', KREAXYLICENSES_SLUG ),
			__( 'Kreaxy Licenses', KREAXYLICENSES_SLUG ),
			'manage_options',
			KREAXYLICENSES_SLUG,
			array( $this, 'kl_display_page' ), 'dashicons-admin-network'
		);
	}

	public function kl_display_page() {
		$tab = sanitize_text_field( $_GET['tab'] );
		$node = sanitize_text_field( $_GET['node'] );

		switch ( $tab ) {
			case 'licenses':
			case '':
				switch ( $node ) {
					case 'license':
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/licenses/license.php' );
						break;
					default:
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'codes/licenses/licenses.table.class.php' );
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/licenses/licenses.php' );
						break;
				}
				break;
			case 'categories':
				switch ( $node ) {
					case 'category':
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/plugin.categories/plugin.category.php' );
						break;
					default:
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'codes/plugin.categories/plugin.categories.table.class.php' );
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/plugin.categories/plugin.categories.php' );
						break;
				}
				break;
			case 'plugins':
				switch ( $node ) {
					case 'plugin':
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/plugins/plugin.php' );
						break;
					default:
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'codes/plugins/plugins.table.class.php' );
						require_once( KREAXYLICENSES_PLUGIN_DIR . 'views/plugins/plugins.php' );
						break;
				}
				break;
		}
	}

	public function kl_admin_init() {
		global $wpdb;
		$tableLicenses = $wpdb->prefix . 'kl_licenses';
		$tablePluginCategories = $wpdb->prefix . 'kl_plugin_categories';
		$tablePlugins = $wpdb->prefix . 'kl_plugins';

		/**
		 * Plugin
		 */
		if ( isset( $_POST['kl_submit_add_plugin'] ) ) {
			if ( !isset( $_POST['kl_submit_add_plugin_nonce'] ) || !wp_verify_nonce( $_POST['kl_submit_add_plugin_nonce'], 'kl_add_plugin' ) ) {
				die( 'Cheating, uh?' );
			}

			$idPlugin = sanitize_text_field( $_POST['kl_hidden_id_plugin'] );
			$idCategory = sanitize_text_field( $_POST['kl_plugin_category'] );
			$name = sanitize_text_field( $_POST['kl_plugin_name'] );
			$slug = sanitize_text_field( $_POST['kl_plugin_slug'] );

			if ( empty( $idPlugin ) ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO $tablePlugins SET id_category = %d, name = %s, slug = %s, created_at = NOW()", $idCategory, $name, $slug ) );
				$idPlugin = $wpdb->insert_id;
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE $tablePlugins SET id_category = %d, name = %s, slug = %s, updated_at = NOW() WHERE id = %d", $idCategory, $name, $slug, $idPlugin ) );
			}

			wp_safe_redirect( admin_url() . 'admin.php?page=' . KREAXYLICENSES_SLUG . '&tab=plugins&node=plugin&plugin=' . $idPlugin );
			exit();
		}

		/**
		 * Category
		 */
		if ( isset( $_POST['kl_submit_add_plugin_category'] ) ) {
			if ( !isset( $_POST['kl_submit_add_plugin_category_nonce'] ) || !wp_verify_nonce( $_POST['kl_submit_add_plugin_category_nonce'], 'kl_add_plugin_category' ) ) {
				die( 'Cheating, uh?' );
			}

			$idCategory = sanitize_text_field( $_POST['kl_hidden_id_plugin_category'] );
			$name = sanitize_text_field( $_POST['kl_category_email'] );

			if ( empty( $idCategory ) ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO $tablePluginCategories SET name = %s, created_at = NOW()", $name ) );
				$idCategory = $wpdb->insert_id;
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE $tablePluginCategories SET name = %s, updated_at = NOW() WHERE id = %d", $name, $idCategory ) );
			}

			wp_safe_redirect( admin_url() . 'admin.php?page=' . KREAXYLICENSES_SLUG . '&tab=categories&node=category&category=' . $idCategory );
			exit();
		}

		/**
		 * License
		 */
		if ( isset( $_POST['kl_submit_add_license'] ) ) {
			if ( !isset( $_POST['kl_submit_add_license_nonce'] ) || !wp_verify_nonce( $_POST['kl_submit_add_license_nonce'], 'kl_add_license' ) ) {
				die( 'Cheating, uh?' );
			}

			$idLicense = sanitize_text_field( $_POST['kl_hidden_id_license'] );
			$idPlugin = sanitize_text_field( $_POST['kl_license_plugin'] );
			$email = sanitize_text_field( $_POST['kl_license_email'] );

			if ( empty( $idLicense ) ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO $tableLicenses SET id_plugin = %d, email = %s, created_at = NOW()", $idPlugin, $email ) );
				$idLicense = $wpdb->insert_id;
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE $tableLicenses SET id_plugin = %d, email = %s, updated_at = NOW() WHERE id = %d", $idPlugin, $email, $idLicense ) );
			}

			wp_safe_redirect( admin_url() . 'admin.php?page=' . KREAXYLICENSES_SLUG . '&tab=licenses&node=license&license=' . $idLicense );
			exit();
		}
	}
}new KreaxyLicenses();
