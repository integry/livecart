<h1>License Agreement</h1>

<div id="license">{$license|nl2br}</div>

<div>
	{form action="install/acceptLicense" method="POST" handle=$form style="padding: 0; background: 0; border: 0;" class="form-horizontal"}

	<fieldset class="error">
		<p id="agreeContainer" onclick="if (Event.element(event) != $('accept')) { $('accept').click(); }">

			{checkbox name=accept id=accept class="checbox" style="float: left; margin-right: 5px;"}
			<label class="checkbox">I accept the license agreement</label>
			<span class="text-danger hidden"></span>
			<br class="clear" />
		</p>
	</fieldset>
	<div class="clear"></div>
	<input type="submit" value="Continue installation" />
	{/form}
</div>


<!--[if IE]>
	<style>
	label.checkbox
	{
		line-height: 1.8em;
	}
	</style>
<![endif]-->

<script type="text/javascript">
	$('accept').focus();
</script>


