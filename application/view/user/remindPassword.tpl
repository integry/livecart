{loadJs form=true}
{pageTitle}{t _remind_pass}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doRemindPassword" method="post" style="float: left; width: 100%;" handle=$form}
		{input name="email"}
			{label}{t _your_email}:{/label}
			{textfield}
		{/input}

		<p>
			<label></label>
			<input type="submit" class="submit" value="{tn _continue}" />
		   	<label class="cancel">
				{t _or}
				<a class="cancel" href="{link route=$return controller=user}">{t _cancel}</a>
			</label>
		</p>

		<input type="hidden" name="return" value="{$return}" />

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}