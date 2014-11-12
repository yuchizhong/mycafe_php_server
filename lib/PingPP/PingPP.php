<?php

abstract class PingPP
{
    /**
     * @var string The PingPP API key to be used for requests.
     */
    public static $apiKey;
    /**
     * @var string The base URL for the PingPP API.
     */
    public static $apiBase = 'https://api.pingplusplus.com/';
    /**
     * @var string|null The version of the PingPP API to use for requests.
     */
    public static $apiVersion = "2014-10-10";
    /**
     * @var boolean Defaults to true.
     */
    public static $verifySslCerts = true;
    const VERSION = '1.0.2';

    /**
     * @return string The API key used for requests.
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * Sets the API key to be used for requests.
     *
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *    latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return boolean
     */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /**
     * @param boolean $verify
     */
    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }
}
