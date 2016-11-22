<?php

/**
 * Class to contain Util functions.
 *
 * @link        http://livetime.nu
 * @since       1.0.0
 * @author      Alexander Karlsson <alexander@livetime.nu>
 * @package     BankID
 */

namespace BankID;

class Utils
{
    /**
     * Normalize the given input to UTF-8
     *
     * @since       1.0.0
     * @param       string      The data to normalize
     * @return      string      The data converted to UTF-8
     */
    public static function normalize_text( $input )
    {
        return iconv( mb_detect_encoding( $input, mb_detect_order(), true ), "UTF-8", $input );
    }

    /**
     * Get the path for the specificed certificate.
     *
     * @since       1.0.0
     * @param       string      $name       The certificate name.
     * @return      string                  Full certificate path.
     */
    public static function get_certificate( $name )
    {
        $cert_path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . $name;

        if ( file_exists( $cert_path ) )
            return $cert_path;

        return null;
    }
}