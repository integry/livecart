<span id="newsletterSubscriberMass_0" class="activeGridMass">

	{form action="backend.newsletterSubscriber/processMass" method="POST" handle=$massForm onsubmit="return false;"}

	<input type="hidden" name="filters" value="" />
	<input type="hidden" name="selectedIDs" value="" />
	<input type="hidden" name="isInverse" value="" />

	{t _with_selected}:
	<select name="act" class="select">
		<option value="enable_isEnabled">{t _enable}</option>
		<option value="disable_isEnabled">{t _disable}</option>
		<option value="delete">{t _delete}</option>
	</select>

	<input type="submit" value="{tn _process}" class="submit" />
	<span class="massIndicator progressIndicator" style="display: none;"></span>

	{/form}

</span>