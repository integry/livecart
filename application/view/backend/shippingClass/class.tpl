{% if $class.ID %}
	{assign var="action" value="controller=backend.shippingClass action=update id=`$class.ID`"}
{% else %}
	{assign var="action" value="controller=backend.shippingClass action=create"}
{% endif %}

{form handle=$classForm action=$action id="classForm_`$class.ID`" method="post" onsubmit="Backend.ShippingClass.prototype.getInstance(this).save(); return false;"}

	{hidden name="ID"}

	[[ textfld('name', '_name') ]]

	{language}
		[[ textfld('name_`$lang.ID`', '_name') ]]
	{/language}

	<fieldset class="class_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="
		class_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="class_cancel cancel">{t _cancel}</a>
	</fieldset>

{/form}