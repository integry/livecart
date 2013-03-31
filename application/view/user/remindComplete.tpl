{pageTitle}{t _password_sent}{/pageTitle}

{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	<p>
	   {maketext text=_has_been_sent_to params=$email}
	</p>

	<p>
	   {t _pass_security_warning}
	</p>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}