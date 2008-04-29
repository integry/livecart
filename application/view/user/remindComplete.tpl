{pageTitle}{t _password_sent}{/pageTitle}
<div class="userRemindComplete">

{include file="layout/frontend/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _password_sent}</h1>

	<p>
	   {maketext text=_has_been_sent_to params=$email}
	</p>

	<p>
	   {t _pass_security_warning}
	</p>

</div>

{include file="layout/frontend/footer.tpl"}

</div>