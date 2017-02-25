<?php

class Invitar extends Service
{
	private $connection = null;

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

		// get the array of emails from the body. For each email
		$query = str_replace(",", " ", $request->query);
		$query = preg_replace("/\s+/", " ", $query);
		$emailsToInvite = explode(" ", $query);

		// if no emails passed, return an error response
		if(empty($query))
		{
			// update the valid email on the usage text
			$validEmailAddress = $this->utils->getValidEmailAddress();
			$usage = nl2br(str_replace('{APRETASTE_EMAIL}', $validEmailAddress, $this->serviceUsage));

			// create response
			$response = new Response();
			$response->createFromText("Parece que quieres invitar a tus amigos y familia, pero olvidaste escribir sus emails. Inv&iacute;talos de la siguiente manera:<br/><br/>$usage");
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
			if( ! $this->connection) $this->connection = new Connection();
			$sql = "INSERT INTO invitations (email_inviter,email_invited,source) VALUES ('{$request->email}','$emailToInvite','internal')";
			$this->connection->deepQuery($sql);

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
}
