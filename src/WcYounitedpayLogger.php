<?php

namespace Younitedpay\WcYounitedpayGateway;

/**
 * Class WcYounitedpayLogger {
 */
class WcYounitedpayLogger {


	/**
	 * Write an entry to a log file in the uploads directory.
	 *
	 * @since x.x.x
	 *
	 * @param mixed  $entry String or array of the information to write to the log.
	 * @param string $file Optional. The file basename for the .log file.
	 * @param string $mode Optional. The type of write. See 'mode' at https://www.php.net/manual/en/function.fopen.php.
	 * @return boolean|int Number of bytes written to the lof file, false otherwise.
	 */
	public static function log( $entry, $mode = 'a', $file = 'younitedpay' ) {
		// Get WordPress uploads directory.
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'] . '/wc-logs';

		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir );
		}

		// If the entry is array, json_encode.
		if ( is_array( $entry ) ) {
			$entry = json_encode( $entry );
		}

		// Write the log file.
		$file  = $upload_dir . '/' . $file . '_' . gmdate( 'd-m-Y' ) . '.log';
		$file  = fopen( $file, $mode );
		$bytes = fwrite( $file, current_time( 'mysql' ) . '::' . $entry . "\n" );
		fclose( $file );
		return $bytes;
	}

	private static function getContentOfFile( $filename, $max_size ) {
		$filesize = filesize( $filename );
		if ( $filesize <= $max_size ) {
			$offset = 0;
		} else {
			$offset = $filesize - $max_size;
		}

		$handle = fopen( $filename, 'r' );
		fseek( $handle, $offset );
		$data = fread( $handle, $max_size );
		fclose( $handle );

		return $data;
	}

	public static function getContent() {
		$upload_dir    = wp_upload_dir();
		$log_day_array = array( gmdate( 'd-m-Y' ), gmdate( 'd-m-Y', strtotime( 'yesterday' ) ) );
		$content       = '';

		// cela permet de récupérer au maximum 1mo de logs
		$max_size = 1048576;
		foreach ( $log_day_array as $log_day ) {
			$max_size = $max_size - strlen( $content );
			if ( $max_size <= 0 ) {
				break;
			}

			$filename = $upload_dir['basedir'] . "/wc-logs/younitedpay_$log_day.log";
			if ( ! file_exists( $filename ) ) {
				continue;
			}

			$content .= "LOGS $log_day\n\n";
			$content .= self::getContentOfFile( $filename, $max_size );
		}
		return $content;
	}
}
