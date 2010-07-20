{if $class.ID}
	{assign var="action" value="controller=backend.taxClass action=update id=`$class.ID`"}
{else}
	{assign var="action" value="controller=backend.taxClass action=create"}
{/if}

{form handle=$classForm action=$action id="classForm_`$class.ID`" method="post" onsubmit="Backend.TaxClass.prototype.getInstance(this).save(); return false;"}

	{hidden name="ID"}


	<p>
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name"}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>

	{language}
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name_`$lang.ID`" class="observed"}
			<span class="errorText hidden"> </span>
		</fieldset>
	{/language}

	<fieldset class="class_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="
		class_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="class_cancel cancel">{t _cancel}</a>
	</fieldset>

{/form}