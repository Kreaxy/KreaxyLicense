<?php
if ( !class_exists( 'KreaxyLicenseApi' ) ) {
	class KreaxyLicenseApi {
		public function activateLicense( $email, $plugin ) {
			if ( empty( $email ) || empty( $plugin ) ) {
				$response = array( 'code' => 'INVALID', 'message' => 'Invalid email or plugin slug.' );
				return json_encode( $response );
			}

			global $wpdb;
			$tableLicenses = $wpdb->prefix . 'kl_licenses';

			$license = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tableLicenses WHERE email = %s AND id_plugin = %d", $email, $plugin ) );
			if ( !empty( $license ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $tableLicenses SET status = %s, updated_at = NOW() WHERE email = %s AND id_plugin = %d", 'Activated', $email, $plugin ) );
				$response = array( 'code' => 'SUCCESS', 'message' => 'Congratulation, your license is active.' );
			}
			return json_encode( $response );
		}
	}
}