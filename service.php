<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Database;

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
		// get users invited
		$invited = Database::query("
			SELECT A.username, A.gender, A.avatar, A.avatarColor, B.accepted
			FROM person A JOIN _email_invitations B
			ON A.email = B.email_to
			WHERE id_from = {$request->person->id}
			AND B.accepted IS NOT NULL
			ORDER BY B.accepted DESC");

		// get the content
		$content = [
			'username' => $request->person->username,
			'invited' => $invited
		];

		// send response to the view
		$response->setCache();
		$response->setTemplate('home.ejs', $content);
	}
}
