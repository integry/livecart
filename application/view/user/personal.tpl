{loadJs form=true}

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="personalMenu"}

{include file="block/content-start.tpl"}

	<h1>{t _personal_info}</h1>

	{form action="controller=user action=savePersonal" method="POST" handle=$form}

		{input name="firstName"}
			{label}{t _your_first_name}:{/label}
			{textfield}
		{/input}

		{input name="lastName"}
			{label}{t _your_last_name}:{/label}
			{textfield}
		{/input}

		{input name="companyName"}
			{label}{t _company_name}:{/label}
			{textfield}
		{/input}

		{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

		<p>
			<label></label>
			<input type="submit" class="submit" value="{tn _save}" />
			<label class="cancel">
			   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
			</label>
		</p>

	{/form}

{include file="block/content-stop.tpl"}

{include file="layout/frontend/footer.tpl"}