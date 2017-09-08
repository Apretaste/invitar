<h1>Invitar</h1>
{if $invited}
	<h2>Quien te invit&oacute;?</h2>
	{link href="PERFIL @{$father}" caption="@{$father}" popup="false" wait="false"} te ha invitado a usar Apretaste!.
	{space5}
	{if $childs !== false}
		<h2>A qui&eacute;nes has invitado?</h2>
		<ul>
		{foreach from=$childs item=item}
		<li>{link href="PERFIL @{$item->username_invited}" caption="@{$item->username_invited}" popup="false" wait="false"}, &sect;{$item->profit|money_format}</li>
		{/foreach}
		</ul>
	{/if}
{else}
	<p>Puedes ganar cr&eacute;dito de Apretaste invitando a otros a que lo usen. Hasta ahora nadie te ha invitado 
	y no has invitado a nadie. Comienza por decirnos qui&eacute;n te invit&oacute; 
	y as&iacute; ambos ganar&aacute;n &sect;{$profit_by_child|money_format}. Despu&eacute;s comienza 
	a invitar y gana el mismo cr&eacute;dito por ello. Cuando tus invitados inviten a otros, 
	obtendr&aacute;s &sect;{$profit_by_nieto|money_format} por cada invitaci&oacute;n.</p>
	
	<center>{button href="INVITAR @quienteinvita" caption="Invitar" desc="Escribe el @username de quien te ha invitado a usar Apretaste" popup="true" wait="false"}</center>

{/if}