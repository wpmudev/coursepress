<?php

class CP_Encryption {

	var $security_key = NONCE_KEY;

	function safe_b64encode( $string ) {
		$data = base64_encode( $string );
		$data = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), $data );

		return $data;
	}

	function safe_b64decode( $string ) {
		$data = str_replace( array( '-', '_' ), array( '+', '/' ), $string );
		$mod4 = strlen( $data ) % 4;
		if ( $mod4 ) {
			$data .= substr( '====', $mod4 );
		}

		return base64_decode( $data );
	}

	function encode( $value ) {
		if ( extension_loaded( 'mcrypt' ) && function_exists( 'mcrypt_module_open' ) ) {
			if ( ! $value ) {
				return false;
			}

			$text      = $value;
			$iv_size   = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
			$iv        = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
			$crypttext = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, mb_substr( $this->security_key, 0, 24 ), $text, MCRYPT_MODE_ECB, $iv );

			return trim( $this->safe_b64encode( $crypttext ) );
		} else {
			return $value;
		}
	}

	function decode( $value ) {
		if ( extension_loaded( 'mcrypt' ) && function_exists( 'mcrypt_module_open' ) ) {
			if ( ! $value ) {
				return false;
			}

			$crypttext   = $this->safe_b64decode( $value );
			$iv_size     = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
			$iv          = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
			$decrypttext = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, mb_substr( $this->security_key, 0, 24 ), $crypttext, MCRYPT_MODE_ECB, $iv );

			return trim( $decrypttext );
		} else {
			return $value;
		}
	}

}

?>
