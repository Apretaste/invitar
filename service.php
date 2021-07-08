<?php

use Apretaste\Bucket;
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
			SELECT A.username, A.picture, A.gender, A.avatarColor, B.accepted
			FROM person A JOIN _email_invitations B
			ON A.email = B.email_to
			WHERE id_from = {$request->person->id}
			AND B.accepted IS NOT NULL
			ORDER BY B.accepted DESC");

		// get the full path to the image
		foreach($invited as $item) {
			if($item->picture) $item->picture = Bucket::get('perfil', $request->person->picture);
		}

		// get the content
		$content = [
			'username' => $request->person->username,
			'osType' => $request->input->osType,
			'invited' => $invited
		];

		// send response to the view
		$response->setCache();
		$response->setComponent('Main', $content);
	}
}
