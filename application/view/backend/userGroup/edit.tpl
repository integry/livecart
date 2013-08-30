<div id="userGroup_[[userGroup.ID]]"></div>
{form handle=$userGroupForm action="controller=backend.userGroup action=save" id="userGroupForm_`$userGroup.ID`" method="post" onsubmit="Backend.User.Group.prototype.getInstance(this).save(); return false;" role="userGroup.update"}
	{hidden name="ID"}

	[[ textfld('name', '_name') ]]

	[[ textfld('description', '_description') ]]

	{include file="backend/eav/fields.tpl" item=$userGroup}

	<fieldset class="userGroup_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="userGroup_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="userGroup_cancel cancel">{t _cancel}</a>
	</fieldset>
{/form}
<script type="text/javascript">
	Backend.User.Group.prototype.getInstance("userGroupForm_[[userGroup.ID]]");
</script>
