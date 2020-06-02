<?php

use Framework\Config;
use Framework\Database;
use Apretaste\Email;
use Apretaste\Level;
use Apretaste\Person;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

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
		$response->setCache('year');
		$response->setTemplate('home.ejs');
	}

	/**
	 * Show the list of invitations
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _list(Request $request, Response $response)
	{
		// get list of people invited
		$invitations = Database::query("
			SELECT accepted, email_to, 
				TIMESTAMPDIFF(DAY, send_date, NOW()) AS days,
				DATE_FORMAT(send_date, '%e/%c/%Y') AS send_date
			FROM _email_invitations 
			WHERE id_from = {$request->person->id}");

		// send response to the view
		$response->setTemplate('list.ejs', ['invitations' => $invitations]);
	}

	/**
	 * Invite or remind a user to use the app
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _invitar(Request $request, Response $response)
	{
		// get the email of the host
		$email = $request->input->data->email;

		// do not invite a user twice
		if (Person::find($email)) {
			$response->setTemplate('message.ejs', [
				'header' => 'El usuario ya existe',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => "El email $email ya forma parte de nuestros usuarios, por lo cual no lo podemos invitar a la app."
			]);
		}

		// get the days the invitation is due
		$invitation = Database::query("
			SELECT TIMESTAMPDIFF(DAY,send_date, NOW()) AS days 
			FROM _email_invitations 
			WHERE id_from = {$request->person->id}
			AND email_to = '$email'");

		// do not resend invitations before the three days
		$resend = false;
		if (!empty($invitation)) {
			$resend = $invitation[0]->days >= 3;
			if (!$resend) {
				$response->setTemplate('message.ejs', [
					'header' => 'Lo sentimos',
					'icon' => 'sentiment_very_dissatisfied',
					'text' => "Ya enviaste una invitación a $email hace menos de 3 días, por favor espera antes de reenviar la invitación."
				]);
			}
		}

		// get host name or username if it does not exist
		$name = !empty($request->person->first_name) ? $request->person->first_name : '@' . $request->person->username;

		// create the invitation variables
		$content = [
			'link' => 'http://tiny.cc/apretaste', 
			'username' => $request->person->username,
			'support' => Config::pick('general')['support_email'],
			'name' => $name];

		// send the email
		$sender = new Email();
		$sender->to = $email;
		$sender->subject = "$name te ha invitado a la app";
		$sender->sendFromTemplate($content, 'invite');

		// save invitation into the database
		if ($resend) {
			Database::query("UPDATE _email_invitations SET send_date = NOW() WHERE id_from = '{$request->person->id}' AND email_to = '$email'");
		} else {
			Database::query("INSERT INTO _email_invitations(id_from, email_to) VALUES('{$request->person->id}','$email')");
		}

		// complete the challenge
		Challenges::complete('invite-friend', $request->person->id);

		// add the experience
		Level::setExperience('INVITE_FRIEND', $request->person->id);

		// success inviting the user
		$response->setTemplate('message.ejs', [
			'header' => 'Su invitación ha sido enviada',
			'icon' => 'sentiment_very_satisfied',
			'text' => "Gracias por invitar a $email a ser parte de nuestra comunidad, si se une serás notificado y recibirás §0.5 de crédito."
		]);
	}
}
