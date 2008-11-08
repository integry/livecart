{loadJs form=true}

<div class="userChangeEmail">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="emailMenu"}
<div id="userContent" style="float: none;">

	<h1>{t _change_email}</h1>

		<fieldset class="container">
		{form action="controller=user action=doChangeEmail" method="POST" handle=$form}

			<p>
				<label>{t _current_email}:</label>
				<label class="currentEmail">{$user.email}</label>
			</p>

			<p class="required">
				{err for="email"}
					{{label {t _new_email}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p>
				<label></label>
				<input type="submit" class="submit" value="{tn _complete_email_change}" />
				<label class="cancel">
				   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
				</label>
			</p>

		{/form}
		</fieldset>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>