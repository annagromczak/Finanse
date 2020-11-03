<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.4
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = '';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = '';

    /**
     * Database user
     * @var string
     */
    const DB_USER = '';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = false;
	
	/**
	* Secret key for hashing
	* @var string
	*/
	const SECRET_KEY = '';
	
	/**
	* Mailgun API key
	* @var string
	*/
	const MAILGUN_API_KEY = '';
	
	/**
	* Mailgun domain
	* @var string
	*/
	const MAILGUN_DOMAIN = "";
	
	/**
	* MAILGUN API HOSTNAME
	* @var string
	*/
	const MAILGUN_HOSTNAME = "";
	
	/**
	* ReCaptcha
	* @var string
	*/
	const secretReCaptcha = "";
	
}
