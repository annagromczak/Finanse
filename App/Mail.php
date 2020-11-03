<?php

namespace App;

use App\Config;
use Mailgun\Mailgun;

/**
 * Mail
 *
 * PHP version 7.4
 */
class Mail
{

    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     *
     * @return mixed
     */
    public static function send($to, $subject, $text, $html)
    {
        # Instantiate the client.
		$mgClient = Mailgun::create(Config::MAILGUN_API_KEY, Config::MAILGUN_HOSTNAME);
		$domain = Config::MAILGUN_DOMAIN;
		$params = array(
			'from'    => 'kontakt@annagromczak.it',
			'to'      => $to,
			'subject' => $subject,
			'html'    => $html
		);
		
		# Make the call to the client.
		$result = $mgClient->messages()->send($domain, $params);
	}
}
