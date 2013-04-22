{loadJs form=true}
{pageTitle}{t _remind_pass}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doRemindPassword" method="post"  class="form-horizontal" handle=$form}
		{input name="email"}
			{label}{t _your_email}:{/label}
			{textfield}
		{/input}

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return cancel=user}

		<input type="hidden" name="return" value="{$return}" />

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}