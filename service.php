<?php

use Apretaste\Request;
use Apretaste\Response;
use Framework\Config;
use Framework\Database;

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
		$link = "apretaste.me/join/me/{$request->person->username}";

		// get users invited
		$invited = Database::queryCache("
			SELECT username, gender, avatar, avatarColor, insertion_date 
			FROM person 
			WHERE invited_by = {$request->person->id}
			ORDER BY insertion_date DESC");

		// get the content
		$content = [
			'link' => $link,
			'invited' => $invited
		];

		// send response to the view
		$response->setCache();
		$response->setTemplate('home.ejs', $content);
	}
}
