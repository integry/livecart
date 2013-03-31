{loadJs form=true}
{pageTitle}{t _change_email}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="emailMenu"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doChangeEmail" method="POST" handle=$form}

		{input}
			{label}{t _current_email}:{/label}
			<label class="currentEmail">{$user.email}</label>
		{/input}

		{input name="email"}
			{label}{t _new_email}:{/label}
			{textfield}
		{/input}

		<p>
			<label></label>
			<input type="submit" class="submit" value="{tn _complete_email_change}" />
			<label class="cancel">
			   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
			</label>
		</p>

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}