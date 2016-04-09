<p>Gracias por invitar a sus amigos y familia a usar Apretaste.</p>

{space5}

{if  $invited|@count gt 0}
	<h2>Personas invitadas</h2>
	<p><small>Los emails que se muestran a continuaci&oacute;n han sido invitados. Cuando ellos usen Apretaste por primera vez, usted ganar&aacute; tickets para {link href="RIFA" caption="nuestra rifa"} y $0.25 en cr&eacute;dito de Apretaste.</small></p>
	
	<ul>
		{foreach from=$invited item=email}
			<li>{$email}</li>
		{/foreach}
	</ul>
	{space15}
{/if}


{if  $already|@count gt 0}
	<h2>Usuarios existentes</h2>
	<p><small>Los usuarios a continuaci&oacute;n ya pertenecen a Apretaste o ya han sido invitados anteriormente, por lo cual nos les invitaremos de nuevo.</small></p>
	<ul>
		{foreach from=$already item=email}
			<li>{$email}</li>
		{/foreach}
	</ul>
	{space15}
{/if}


{if  $invalid|@count gt 0}
	<h2>Emails inv&aacute;lidos</h2>
	<p><small>Los emails a continuaci&oacute;n han sido detectado como direcciones inv&aacute;lidas o inexistentes, por lo cual no les podemos invitar. Por favor revise la sintaxis e intente nuevamente.</small></p>
	<ul>
		{foreach from=$invalid item=email}
			<li>{$email}</li>
		{/foreach}
	</ul>
	{space15}
{/if}


<p>Gracias por compartir Apretaste y hacer este proyecto m&aacute;s grande cada d&iacute;a.</p>