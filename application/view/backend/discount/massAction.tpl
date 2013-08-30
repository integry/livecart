<span class="activeGridMass" {denied role="product.mass"}style="visibility: hidden;"{/denied} id="discountMass" >

	{form action="backend.discount/processMass" method="POST" handle=$massForm onsubmit="return false;"}

	<input type="hidden" name="filters" value="" />
	<input type="hidden" name="selectedIDs" value="" />
	<input type="hidden" name="isInverse" value="" />

	{t _with_selected}:
	<select name="act" class="select">
		<option value="delete">{t _delete}</option>
		<option value="enable_isEnabled">{t _enable}</option>
		<option value="disable_isEnabled">{t _disable}</option>
	</select>

	<span class="bulkValues" style="display: none;"></span>

	<input type="submit" value="{t _process}" class="submit" />
	<span class="progressIndicator" style="display: none;"></span>

	{/form}

</span>
