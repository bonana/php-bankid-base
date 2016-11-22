<?php

/**
 * Class to handle BankID Integration
 *
 * @link        http://livetime.nu
 * @since       1.0.0
 * @author      Alexander Karlsson <alexander@livetime.nu>
 * @package     BankID
 */

namespace BankID;

require_once( __DIR__ . '/class-utils.php' );

class BankID
{
    /**
     * SOAPClient to make API requests to the BankID central server.
     *
     * @since       1.0.0
     * @var         SOAPClient      $soap       Current SOAPClient
     */
    protected $_soap;

    /**
     * Certificates to verify against BankID.
     *
     * @since       1.0.0
     * @var         string          $certs      File path to certificate files.
     */
    protected $_certs;

    /**
     * All the API related URLs.
     *
     * @since       1.0.0
     * @var         string          $api_url        URL to the API.
     * @var         string          $wsdl_url       URL to the API structure.
     * @var         string          $verify_cert    Path to the local CA file to verify the central BankID Server.
     */
    protected $_api_url;
    protected $_wsdl_url;
    protected $_verify_cert;

    /**
     * CN to match.
     *
     * @since       1.0.0
     * @var         string          $_cn_match      The CN to match when making requests.
     */
    protected $_cn_match;

    /**
     * During the initialization of our class we will set the correct urls and ceritifications depending on if
     * it's run as a test or not.
     *
     * @since       1.0.0
     * @param       string      $certs      Name of the certifications to load.
     * @param       bool        $test       Whether or not to run in test mode.
     */
    public function __construct( $certs, $test = false )
    {
        try {
            $this->_certs = Utils::get_certificate( $certs );

            if ( $test ) {
                $this->_api_url = 'https://appapi.test.bankid.com/rp/v4';
                $this->_wsdl_url = 'https://appapi.test.bankid.com/rp/v4?wsdl';
                $this->_verify_cert = Utils::get_certificate( 'appapi.test.bankid.com.pem' );
                $this->_cn_match = 'BankID SSL Root Certification Authority TEST';
            } else {
                $this->_api_url = 'https://appapi.bankid.com/rp/v4';
                $this->_wsdl_url = 'https://appapi.bankid.com/rp/v4?wsdl';
                $this->_verify_cert = Utils::get_certificate( 'appapi.bankid.com.pem' );
                $this->_cn_match = 'BankID SSL Root Certification Authority';
            }

            // Since PHP has quite insecure options from the get go, let's fix that.
            $context_options = array(
                'ssl' => array(
                    'verify_peer'   => true,
                    'cafile'        => $this->_verify_cert,
                    'verify_depth'  => 5,
                    'CN_match'      => $this->_cn_match,
                    'disable_compression'   => true,
                    'SNI_enabled'           => true,
                    'ciphers'               => 'ALL!EXPORT!EXPORT40!EXPORT56!aNULL!LOW!RC4'
                )
            );

            $ssl_context = stream_context_create( $context_options );

            // Connect and store our SOAP connection.
            $this->_soap = new \SOAPClient( $this->_wsdl_url, array(
                'local_cert' => $this->_certs,
                'stream_context' => $ssl_context
            ) );
        } catch ( \SoapFault $fault ) {
            // pass
        }
    }

    /**
     * Start an authentication process for a user against BankID.
     *
     * @since       1.0.0
     * @param       string      $ssn        The SSN of the person you want to authenticate, format YYYYMMDDXXXX
     * @param       string      $kwargs     Keyword argument array to allow any number of the additional BankID settings.
     * @return      string                  Valid API response or null
     */
    public function authenticate( $ssn, $kwargs = array() )
    {
        try {
            $kwargs['personalNumber'] = $ssn;
            $out = $this->_soap->Authenticate( $kwargs );
        } catch ( \SoapFault $fault ) {
            $out = null;
        }

        return $out;
    }

    /**
     * Start a signing process for a user against BankID.
     *
     * @since       1.0.0
     * @param       string      $ssn            The SSN of the person you want to sign the data.
     * @param       string      $visible_data   The data that the user will be prompted to sign.
     * @param       string      $hidden_data    The data that will be held at BankIDs servers. Example use: Verify that the data signed is correct and hasn't been tampered with.
     * @param       array       $kwargs         Keyword argument array to allow any of the additional BankID settings.
     * @return                                  Valid API response or null
     */
    public function sign( $ssn, $visible_data, $hidden_data = '', $kwargs = array() )
    {
        try {
            $kwargs['personalNumber'] = $ssn;
            $kwargs['userVisibleData'] = Utils::normalize_text( base64_encode( $visible_data ) );
            $kwargs['userNonVisibleData'] = Utils::normalize_text( base64_encode( $hidden_data ) );
            $out = $this->_soap->Sign( $kwargs );
        } catch ( \SoapFault $fault ) {
            $out = null;
        }

        return $out;
    }

    /**
     * Collect a response from an ongoing order.
     *
     * @since       1.0.0
     * @param       string      $order_ref      The order reference to collect from.
     * @return                                  Valid BankID response or null
     */
    public function collect( $order_ref )
    {
        try {
            $out = $this->_soap->Collect( $order_ref );
        } catch ( \SoapFault $fault ) {
            $out = null;
        }

        return $out;
    }
}