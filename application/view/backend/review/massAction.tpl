<span {denied role="product.mass"}style="display: none;"{/denied} id="reviewMass_[[id]]" class="activeGridMass">

	{form action="controller=backend.review action=processMass query=id=`$id`" method="POST" handle=$massForm onsubmit="return false;"}

	<input type="hidden" name="filters" value="" />
	<input type="hidden" name="selectedIDs" value="" />
	<input type="hidden" name="isInverse" value="" />

	{t _with_selected}:
	<select name="act" class="select" onchange="Backend.Product.massActionChanged(this);">
		<option value="enable_isEnabled">{t _approve}</option>
		<option value="disable_isEnabled">{t _disapprove}</option>
		<option value="delete">{t _delete}</option>
	</select>

	<input type="submit" value="{tn _process}" class="submit" />
	<span class="massIndicator progressIndicator" style="display: none;"></span>

	{/form}

</span>