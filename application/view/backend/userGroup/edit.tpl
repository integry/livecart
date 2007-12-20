<div id="userGroup_{$userGroup.ID}"></div>
{form handle=$userGroupForm action="controller=backend.userGroup action=save" id="userGroupForm_`$userGroup.ID`" method="post" onsubmit="Backend.User.Group.prototype.getInstance(this).save(); return false;" role="userGroup.update"}
	{hidden name="ID"}

	<p class="required">
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name"}
			<span class="errorText" style="display: none" ></span>
		</fieldset>
	</p>

	<fieldset class="error">
		<p>
			<label>{t _description}</label>
			{textarea name="description"}
		</p>
	</fieldset>

	<fieldset class="userGroup_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="userGroup_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="userGroup_cancel cancel">{t _cancel}</a>
	</fieldset>
{/form}
<script type="text/javascript">
	Backend.User.Group.prototype.getInstance("userGroupForm_{$userGroup.ID}");
</script>
