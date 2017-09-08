<h1>Invitar</h1>
{if $invited}
	<h2>Quien te invit&oacute;?</h2>
	{link href="PERFIL @{$father}" caption="@{$father}" popup="false" wait="false"} te ha invitado a usar Apretaste!.
	{space5}
	{if $childs !== false}
		<h2>A qui&eacute;nes has invitado?</h2>
		<ul>
		{foreach from=$childs item=item}
		<li>{link href="PERFIL @{$item->username_invited}" caption="@{$item->username_invited}" popup="false" wait="false"}, {$item->profit}</li>
		{/foreach}
		</ul>
	{/if}
{else}
	<center>{button href="INVITAR @quienteinvita" caption="Invitar" desc="Escribe el @username de quien te ha invitado a usar Apretaste" popup="true" wait="false"}</center>

{/if}