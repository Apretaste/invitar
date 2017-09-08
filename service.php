<?php

class Invitar extends Service
{
	private $connection = null;
	private $profit_by_child = 1;
	private $profit_by_nieto = 0.1;

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

		$invited = $this->utils->getUsernameFromEmail($request->email);
		$who = $this->whoInvite($invited);
		
		// new: check inviter's COUPON
		$query = str_replace('@', '', trim($request->query));
		$inviterEmail = $this->utils->getEmailFromUsername($query);
			
		// si el username (COUPON) recibido es un user de AP...
		if ($inviterEmail !== false)
		{
			$inviter = $this->utils->getPerson($inviterEmail);
			
			// si alguien ya invito al user actual...
			if ($who !== false)
				return new Response();

			// 1. darle credito a ambos
			$this->q("UPDATE person SET credit = credit + {$this->profit_by_child} WHERE email = '{$inviter->email}';");

			// 2. darle credito al padre de quien invita
			$who = $this->whoInvite($inviter->username);
			if ($who !== false)
			{
				$this->q("UPDATE person SET credit = credit + {$this->profit_by_nieto} WHERE username = '$who';");
			}
			
			// 3. insert invitation
			$this->q("INSERT INTO _invitar_tree (email_inviter,email_invited,source) VALUES ('$inviterEmail','{$request->email}','coupon')");

			return new Response();
		}

		// get the array of emails from the body. For each email
		$query = str_replace(",", " ", $request->query);
		$query = preg_replace("/\s+/", " ", $query);
		$emailsToInvite = explode(" ", $query);

		// if no emails passed, return an error response
		if(empty($query))
		{
			// get childs of current user
			$childs = $this->q("SELECT *, 
			(SELECT username FROM person WHERE person.email = _invitar_tree.email_invited) AS username_invited
			 FROM _invitar_tree WHERE email_inviter = '{$request->email}';");
			 
			if ($childs === false) $childs = [];
			
			// get "nietos" of current user
			foreach ($childs as $child)
			{
				$child->profit = {$this->profit_by_child};
				$sql = "SELECT count(*) as t
				 FROM _invitar_tree WHERE email_inviter = '{$child->email_invited}';";
				 
				$r = $this->q($sql);
				
				$child->profit += $r[0]->t * {$this->profit_by_nieto};				
			}
						
			// create response
			$response = new Response();
			$response->createFromTemplate("home.tpl", [
				"profit_by_child" => $this->profit_by_child,
				"profit_by_nieto" => $this->profit_by_nieto,
				"invited" => $who !== false,
				"father" => $who,
				"childs" => count($childs) > 0 ? $childs: false
			]);
			
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
		$r = $this->q("SELECT * FROM _invitar_tree WHERE email_invited = '$email' LIMIT 0,1;");

		if (isset($r[0]))
		{
			$person = $utils->getPerson($r[0]->email_inviter);
			return $person->username;
		}

		return false;
	}
}
