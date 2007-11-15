<h1>License Agreement</h1>

<div id="license">{$license|nl2br}</div>

<div>
	{form action="controller=install action=acceptLicense" method="POST" handle=$form style="padding: 0; background: 0; border: 0;"}
{literal}
	<fieldset class="error">
		<p id="agreeContainer" onclick="if (Event.element(event) != $('accept')) { $('accept').click(); }">
	{/literal}
			{checkbox name=accept class="checbox" style="float: left; margin-right: 5px;"}
			<label class="checkbox">I accept the license agreement</label>
			<span class="errorText hidden"></span>
			<br class="clear" />
		</p>
	</fieldset>
	<div class="clear"></div>
	<input type="submit" value="Continue installation" />
	{/form}
</div>

{literal}
<!--[if IE]>
	<style>
	label.checkbox
	{
		line-height: 1.8em;
	}
	</style>
<![endif]-->
{/literal}