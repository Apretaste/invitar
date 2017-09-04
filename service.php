<?php

class Invitar extends Service
{
	private $connection = null;

	/**
	 * Singleton connection to db
	 *
	 * @author kuma
	 * @return Connection
	 */
	private function connection()
	{
		if (is_null($this->connection))
		{
			$this->connection = new Connection();
		}

		return $this->connection;
	}

	/**
	 * Query assistant
	 *
	 * @author kuma
	 * @example
	 *      $this->q("SELECT * FROM TABLE"); // (more readable / SQL is autodescriptive)
	 * @param string $sql
	 * @return array
	 */
	private function q($sql)
	{
		return $this->connection()->deepQuery($sql);
	}

	/**
	 * Function excecuted once the service Letra is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{

		// this service may return more than one Response
		// create an empty array to fill with all the Response
		$responses = array();

		// new: check inviter's COUPON
		$query = str_replace('@', '', trim($request->query));
		$inviter = $this->utils->getEmailFromUsername($query);

		// si el username (COUPON) recibido es un user de AP...
		if ($inviter !== false)
		{
			$inviter = $this->utils->getPerson($inviter);
			$invited = $this->utils->getUsernameFromEmail($request->email);
			$who = $this->whoInvite($invited);

			// si nadie ha invitado al user actual...
			if ($who !== false)
				return new Response();


			// 1. darle credito a ambos
			$this->q("UPDATE person SET credit = credit + 1 WHERE email = '{$inviter->email}';");

			// 2. darle credito al padre de quien invita
			$who = $this->whoInvite($inviter->username);
			if ($who !== false)
			{
				$this->q("UPDATE person SET credit = credit + 0.10 WHERE username = '$who';");
			}

			return new Response();
		}

		// get the array of emails from the body. For each email
		$query = str_replace(",", " ", $request->query);
		$query = preg_replace("/\s+/", " ", $query);
		$emailsToInvite = explode(" ", $query);

		// if no emails passed, return an error response
		if(empty($query))
		{
			// create response
			$response = new Response();
			$response->createFromText("Parece que quieres invitar a tus amigos y familia, pero olvidaste escribir sus emails.");
			return $response;
		}

		// inicialize response arrays
		$emailsInvited = array();
		$invalidEmails = array();
		$alreadyInvited = array();

		// separate all email types and make the invitations
		foreach ($emailsToInvite as $emailToInvite)
		{
			// check if the person's email is formatted properly
			if ( ! filter_var($emailToInvite, FILTER_VALIDATE_EMAIL))
			{
				$invalidEmails[] = $emailToInvite;
				continue;
			}

			// check you invited the person already, or if he/she is using Apretaste
			if(
				$this->utils->checkPendingInvitation($request->email, $emailToInvite) ||
				$this->utils->personExist($emailToInvite)
			)
			{
				$alreadyInvited[] = $emailToInvite;
				continue;
			}

			// check if the email is valid
			$status = $this->utils->deliveryStatus($emailToInvite);
			if($status != 'ok')
			{
				$invalidEmails[] = $emailToInvite;
				continue;
			}

			// invite the person
			$emailsInvited[] = $emailToInvite;

			// add the person to the database
			$sql = "INSERT INTO invitations (email_inviter,email_invited,source) VALUES ('{$request->email}','$emailToInvite','internal')";
			$this->q($sql);

			// create the invitation for the user
			$response = new Response();
			$response->setResponseEmail($emailToInvite);
			$response->setResponseSubject("{$request->email} le ha invitado a usar Apretaste");
			$responseContent = array("host"=>$request->email, "guest"=>$emailToInvite);
			$response->createFromTemplate("invitation.tpl", $responseContent);
			$response->internal = true; // get the global template located at app/controllers/templates
			$responses[] = $response;
		}

		// create returning array
		$responseContent = array(
			"invited" => $emailsInvited,
			"invalid" => $invalidEmails,
			"already" => $alreadyInvited
		);

		// create the confirmation for the invitor
		$response = new Response();
		$response->setResponseSubject("Gracias por invitar a sus amigos y familia a Apretaste");
		$response->createFromTemplate("confirmation.tpl", $responseContent);
		$responses[] = $response;

		// return the array of Response
		return $responses;
	}

	/**
	 * Who is the inviter of username
	 *
	 * @param $username
	 * @return bool
	 */
	private function whoInvite($username)
	{

		$utils = new Utils();
		$email = $utils->getEmailFromUsername($username);
		$r = $this->q("SELECT * FROM invitations WHERE email_invited = '$email' LIMIT 0,1;");

		if (isset($r[0]))
		{
			$person = $utils->getPerson($r[0]->email_inviter);
			return $person->username;
		}

		return false;
	}
}
