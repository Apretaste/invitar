<?php

use Apretaste\Email;
use Apretaste\Level;
use Apretaste\Person;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Framework\Alert;
use Framework\Config;
use Framework\Database;
use Framework\GoogleAnalytics;

class Service
{
	/**
	 * Show the invitation form
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _main(Request $request, Response $response)
	{
		// create invitation link
		$invitationLink = "http://www.apretaste.me/invitar/{$request->person->username}";

		// get the content
		$content = [
			'link' => $invitationLink,
			'sent' => []
		];

		// send response to the view
		$response->setCache();
		$response->setTemplate('home.ejs', $content);
	}
}
