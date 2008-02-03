<ul class="tabs">
	<li class="active"><a href="#step_main" class="productOption_change_state" >{t _ProductOption_main}</a></li>
	<li><a href="#step_values" class="productOption_change_state" >{t _ProductOption_values}</a></li>
</ul>

<form action="{link controller=backend.productOption action=save}" method="post" class="productOption {denied role="category.update"}formReadonly{/denied}">
	<!-- STEP 1 -->
	<fieldset class="productOption_step_lev1 productOption_step_main">
	<legend>{t _ProductOption_step_one}</legend>

		<input type="hidden" name="ID" class="hidden productOption_form_id" />
		<input type="hidden" name="parentID" class="hidden productOption_form_parentID" />

		<p class="checkbox">
			<input type="checkbox" value="1" name="isRequired" class="checkbox productOption_form_isRequired" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="productOption_form_isRequired_label">{t _ProductOption_is_required}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isDisplayed" class="checkbox productOption_form_isDisplayed" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="productOption_form_isDisplayed_label">{t _ProductOption_displayed_on_front_page}</label>
		</p>

		<p>
			<label class="productOption_form_type_label">{t _ProductOption_type}</label>
			<fieldset class="error">
				<select name="type" class="productOption_form_type" {denied role="category.update"}disabled="disabled"{/denied}>
					<option value="0">{t _ProductOption_type_bool}</option>
					<option value="1">{t _ProductOption_type_select}</option>
					<option value="2">{t _ProductOption_type_text}</option>
				</select>
				<span class="errorText hidden"> </span>
			</fieldset>
		</p>

		<div>
			<p class="required">
				<label class="productOption_form_name_label">{t _ProductOption_title}</label>
				<fieldset class="error">
					<input type="text" name="name" class="productOption_form_name" {denied role="category.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div class="clear"></div>

		{language}
			<p>
				<label class="translation_name_label">{t _ProductOption_title}</label>
				<input type="text" name="name_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
			</p>
		{/language}

	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="productOption_step_lev1 productOption_step_values">
	<legend>{t _ProductOption_step_two}</legend>
		<a href="#mergeValues" class="productOption_mergeValuesLink menu">{t _productOption_merge_values}</a>
		<a href="#mergeValues" class="productOption_mergeValuesCancelLink cancel" style="display: none;">{t _productOption_cancel_merge_values}</a>

		<p>
		<fieldset class="group productOption_form_values_group">
			<div class="productOption_values">
				<p>
					<ul class="{allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed}">
						<li class="dom_template productOption_form_values_value singleInput productOption_update" id="productOption_form_values_" style="display: block;">
							<input type="checkbox" value="1" class="productOption_mergeCheckbox checkbox" style="display: none;"  {denied role="category.update"}disabled="disabled"{/denied} />
							<p>
								<label>{t _option_name}</label>
								<input type="text" class="productOption_update productOption_valueName" {denied role="category.update"}readonly="readonly"{/denied} />
							<p>
								<label>{t _option_price_diff}</label>
								<input type="text" class="number productOption_valuePrice" {denied role="category.update"}readonly="readonly"{/denied} />
								<label>{$defaultCurrencyCode}</label>
							</p>
							<span class="errorText hidden"> </span>
							<br class="clear" />
						</li>
					</ul>
				</p>
				<p class="productOption_values_controls">
					<a href="#add" class="productOption_add_field">{t _ProductOption_add_values}</a>
					<span class="productOption_mergeValuesControls controls" style="display: none">
						<span class="progressIndicator" style="display: none;"></span>
						<input type="button" class="submit productOption_mergeValuesSubmit" value="{tn _productOption_merge_values}" />
						{t _or}
						<a href="#" class="cancel productOption_mergeValuesCancel">{t _cancel}</a>
					</span>
				</p>

				{language}
					<ul>
						<li class="dom_template productOption_form_values_value" id="productOption_form_values_">
							<fieldset class="error">
								<label class="productOption_update"> </label>
								<input class="productOption_update" type="text" {denied role="category.update"}readonly="readonly"{/denied} />
							</fieldset>
						</li>
					</ul>
				{/language}


			</div>

			<div class="clear"></div>
		</fieldset>
		</p>

	</fieldset>


	<fieldset class="productOption_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="productOption_save button submit" value="{translate text=_save}" />
		{t _or}
		<a href="#cancel" class="productOption_cancel cancel">{t _cancel}</a>
	</fieldset>

</form>