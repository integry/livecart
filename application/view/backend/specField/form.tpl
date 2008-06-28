<ul class="tabs">
	<li class="active"><a href="#step_main" class="specField_change_state" >{t _SpecField_main}</a></li>
	<li><a href="#step_values" class="specField_change_state" >{t _SpecField_values}</a></li>
</ul>

<form action="{link controller=backend.specField action=save}" method="post" class="specField {denied role="category.update"}formReadonly{/denied}">
	<!-- STEP 1 -->
	<fieldset class="specField_step_lev1 specField_step_main">
	<legend>{t _SpecField_step_one}</legend>

		<input type="hidden" name="ID" class="hidden specField_form_id" />
		<input type="hidden" name="categoryID" class="hidden specField_form_categoryID" />

		<p>
			<label class="specField_form_type_label">{t _SpecField_type}</label>
			<fieldset class="error">
				<select name="type" class="specField_form_type" {denied role="category.update"}disabled="disabled"{/denied}>
					<optgroup label="{t _SpecField_text}">
						<option value="5">{t _SpecField_type_text_selector}</option>
						<option value="3">{t _SpecField_type_simple_text}</option>
					</optgroup>
					<optgroup label="{t _SpecField_numbers}">
						<option value="1">{t _SpecField_type_numbers_selector}</option>
						<option value="2">{t _SpecField_type_numbers}</option>
					</optgroup>
					<option value="6">{t _SpecField_type_date}</option>
				</select>
				<span class="errorText hidden"> </span>
			</fieldset>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isMultiValue" class="checkbox specField_form_multipleSelector" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="specField_form_multipleSelector_label">{t _SpecField_select_multiple}</label>
		</p>

		<p class="checkbox specField_form_advancedText">
			<input type="checkbox" value="1" name="advancedText" class="checkbox" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="specField_form_advancedText_label">{t _SpecField_formated_text}</label>
		</p>

		<div>
			<p class="required">
				<label class="specField_form_name_label">{t _SpecField_title}</label>
				<fieldset class="error">
					<input type="text" name="name" class="specField_form_name" {denied role="category.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p class="specField_handle">
				<label  class="specField_form_handle_label">{t _SpecField_handle}</label>
				<fieldset class="error">
					<input type="text" name="handle" class="specField_form_handle" {denied role="category.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p>
				<label  class="specField_form_valuePrefix_label sufixAndPrefix">{t _SpecField_valuePrefix}</label>
				<fieldset class="error sufixAndPrefix">
					<input type="text" name="valuePrefix" class="specField_form_valuePrefix" {denied role="category.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p>
				<label  class="specField_form_valueSuffix_label sufixAndPrefix">{t _SpecField_valueSuffix}</label>
				<fieldset class="error sufixAndPrefix">
					<input type="text" name="valueSuffix" class="specField_form_valueSuffix" {denied role="category.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p>
				<label class="specField_form_description_label">{t _SpecField_description}</label>

				<fieldset class="error">
					<textarea name="description" class="specField_form_description" rows="5" cols="40" {denied role="category.update"}readonly="readonly"{/denied}></textarea>
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

		</div>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isRequired" class="checkbox specField_form_isRequired" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="specField_form_isRequired_label">{t _SpecField_is_required}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isDisplayed" class="checkbox specField_form_isDisplayed" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="specField_form_isDisplayed_label">{t _SpecField_displayed_on_front_page}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isDisplayedInList" class="checkbox specField_form_isDisplayedInList" {denied role="category.update"}disabled="disabled"{/denied} />
			<label class="specField_form_isDisplayedInList_label">{t _SpecField_displayed_in_product_list}</label>
		</p>

		<div class="clear"></div>

		{language}

			<p>
				<label class="translation_name_label">{t _SpecField_title}</label>
				<input type="text" name="name_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
			</p>
			<p>
				<label class="translation_valuePrefix_label sufixAndPrefix">{t _SpecField_valuePrefix}</label>
				<input type="text" class="sufixAndPrefix" name="valuePrefix_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
			</p>
			<p>
				<label class="translation_valueSuffix_label sufixAndPrefix">{t _SpecField_valueSuffix}</label>
				<input type="text" class="sufixAndPrefix" name="valueSuffix_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
			</p>
			<p>
				<label class="translation_description_label">{t _SpecField_description}</label>
				<textarea name="description_{$lang.ID}" rows="5" cols="40" {denied role="category.update"}readonly="readonly"{/denied}></textarea>
			</p>

		{/language}

	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="specField_step_lev1 specField_step_values">
	<legend>{t _SpecField_step_two}</legend>
		<a href="#mergeValues" class="specField_mergeValuesLink menu">{t _specField_merge_values}</a>
		<a href="#mergeValues" class="specField_mergeValuesCancelLink cancel" style="display: none;">{t _specField_cancel_merge_values}</a>

		<p>
		<fieldset class="group specField_form_values_group">
			<div class="specField_values">
				<p>
					<ul class="{allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed}">
						<li class="dom_template specField_form_values_value singleInput specField_update" id="specField_form_values_" style="display: block;">
							<input type="checkbox" value="1" class="specField_mergeCheckbox checkbox" style="display: none;"  {denied role="category.update"}disabled="disabled"{/denied} />
							<input type="text" class="specField_update specField_valueName" {denied role="category.update"}readonly="readonly"{/denied} />
							<span class="errorText hidden"> </span>
							<br class="clear" />
						</li>
					</ul>
				</p>
				<p class="specField_values_controls">
					<a href="#add" class="specField_add_field">{t _SpecField_add_values}</a>
					<span class="specField_mergeValuesControls controls" style="display: none">
						<span class="progressIndicator" style="display: none;"></span>
						<input type="button" class="submit specField_mergeValuesSubmit" value="{tn _specField_merge_values}" />
						{t _or}
						<a href="#" class="cancel specField_mergeValuesCancel">{t _cancel}</a>
					</span>
				</p>

				{language}
					<ul>
						<li class="dom_template specField_form_values_value" id="specField_form_values_">
							<fieldset class="error">
								<label class="specField_update"> </label>
								<input class="specField_update" type="text" {denied role="category.update"}readonly="readonly"{/denied} />
							</fieldset>
						</li>
					</ul>
				{/language}


			</div>

			<div class="clear"></div>
		</fieldset>
		</p>

	</fieldset>


	<fieldset class="specField_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="specField_save button submit" value="{translate text=_save}" />
		{t _or}
		<a href="#cancel" class="specField_cancel cancel">{t _cancel}</a>
	</fieldset>

</form>