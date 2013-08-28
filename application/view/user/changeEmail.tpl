{loadJs form=true}
{pageTitle}{t _change_email}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="emailMenu"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doChangeEmail" method="POST" handle=$form class="form-horizontal"}

		{input}
			{label}{t _current_email}:{/label}
			<div class="controls">[[user.email]]</div>
		{/input}

		{input name="email"}
			{label}{t _new_email}:{/label}
			{textfield}
		{/input}

		{include file="block/submit.tpl" caption="_complete_email_change" cancel="user"}

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}