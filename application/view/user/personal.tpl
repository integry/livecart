{loadJs form=true}

{include file="user/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _personal_info}</h1>

	{include file="user/userMenu.tpl" current="personalMenu"}

	<div id="content" style="float: none;">

		<fieldset class="container">
		{form action="controller=user action=savePersonal" method="POST" handle=$form}

			<p class="required">
				{err for="firstName"}
					{{label {t _your_first_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p class="required">
				{err for="lastName"}
					{{label {t _your_last_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p>
				{err for="companyName"}
					{{label {t _company_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

			<p>
				<label></label>
				<input type="submit" class="submit" value="{tn _save}" />
				<label class="cancel">
				   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
				</label>
			</p>

		{/form}
		</fieldset>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}