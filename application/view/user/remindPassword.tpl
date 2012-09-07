{loadJs form=true}
{pageTitle}{t _remind_pass}{/pageTitle}

<div class="userRemindPassword">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	<h1>{t _remind_pass}</h1>

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

</div>

{include file="layout/frontend/footer.tpl"}

</div>