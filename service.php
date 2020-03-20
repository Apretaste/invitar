<?php

use Apretaste\Email;
use Apretaste\Person;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Apretaste\Level;
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
			return $response->setTemplate('message.ejs', [
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
				return $response->setTemplate('message.ejs', [
					'header' => 'Lo sentimos',
					'icon' => 'sentiment_very_dissatisfied',
					'text' => "Ya enviaste una invitación a $email hace menos de 3 días, por favor espera antes de reenviar la invitación."
				]);
			}
		}

		// get support email
		$supportEmail = Config::pick('general')['support_email'];

		// get host name or username if it does not exist
		$name = !empty($request->person->first_name) ? $request->person->first_name : '@' . $request->person->username;

		// create the invitation text
		$body = "
			<p>Algo debes tener, porque <b>@{$request->person->username}</b> te invitó a ser parte nuestra vibrante comunidad</p>
			<p>Somos la única app que ofrece docena de servicios útils en Cuba a través de Datos, WiFi y correo Nauta, y la que más ahorra tus megas. Además, cada semana hacemos rifas, concursos y encuestas, en las cuales te ganas recargas, teléfonos y hasta tablets.</p>
			<p>Descarga la app desde el siguiente enlace, entra usando este correo, y ambos $name y tú ganarán $0.50 de crédito para comprar dentro de la app.</p>
			<p>http://bit.ly/32gPZns</p>
			<p>Si presentas alguna dificultad, escríbenos a $supportEmail y siempre estaremos atentos para ayudarte.</p>
			<p>¡Bienvenido a nuestra familia!</p>";

		// send the email
		$sender = new Email();
		$sender->to = $email;
		$sender->subject = "$name te ha invitado a la app";
		$sender->body = $body;
		$sender->service = 'invitar';
		$sender->send();

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
