<ul class="tabs">
	<li class="active"><a href="#step_main" class="productOption_change_state" >{t _ProductOption_main}</a></li>
	<li><a href="#step_values" class="productOption_change_state" >{t _ProductOption_values}</a></li>
</ul>

<form" action="{link controller="backend.productOption" action=save}" method="post" class="productOption">
	<!-- STEP 1 -->
	<fieldset class="productOption_step_lev1 productOption_step_main">
	<legend>{t _ProductOption_step_one}</legend>

		<input type="hidden" name="ID" class="hidden productOption_form_id" />
		<input type="hidden" name="parentID" class="hidden productOption_form_parentID" />

		<p class="checkbox">
			<input type="checkbox" value="1" name="isRequired" class="checkbox productOption_form_isRequired" />
			<input type="hidden" value="1" name="checkbox_isRequired" />
			<label class="productOption_form_isRequired_label">{tip _ProductOption_is_required}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isDisplayed" class="checkbox productOption_form_isDisplayed" />
			<input type="hidden" value="1" name="checkbox_isDisplayed" />
			<label class="productOption_form_isDisplayed_label">{tip _ProductOption_displayed_in_product_page}</label>
		</p>

		<p class="checkbox" style="display: none;">
			<input type="checkbox" value="1" name="isDisplayedInList" class="checkbox productOption_form_isDisplayedInList" />
			<input type="hidden" value="1" name="checkbox_isDisplayedInList" />
			<label class="productOption_form_isDisplayedInList_label">{t _ProductOption_displayed_in_list}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isDisplayedInCart" class="checkbox productOption_form_isDisplayedInCart" />
			<input type="hidden" value="1" name="checkbox_isDisplayedInCart" />
			<label class="productOption_form_isDisplayedInCart_label">{tip _ProductOption_displayed_in_cart}</label>
		</p>

		<p class="checkbox">
			<input type="checkbox" value="1" name="isPriceIncluded" class="checkbox productOption_form_isPriceIncluded" />
			<input type="hidden" value="1" name="checkbox_isPriceIncluded" />
			<label class="productOption_form_isPriceIncluded_label">{tip _ProductOption_price_included}</label>
		</p>

		<p class="required">
			<label class="productOption_form_type_label">{tip _ProductOption_type}</label>
			<fieldset class="error">
				<select name="type" class="productOption_form_type">
					<option value="0">{t _ProductOption_type_bool}</option>
					<option value="1">{t _ProductOption_type_select}</option>
					<option value="2">{t _ProductOption_type_text}</option>
					<option value="3">{t _ProductOption_type_file}</option>
				</select>
				<span class="errorText hidden"> </span>
			</fieldset>
		</p>

		<div>
			<p class="required">
				<label class="productOption_form_name_label">{t _ProductOption_title}</label>
				<fieldset class="error">
					<input type="text" name="name" class="productOption_form_name"  />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div class="optionSelectMessage">
			<p>
				<label class="productOption_form_displayType_label">{tip _ProductOption_display_as}</label>
				<fieldset class="error">
					<select name="displayType" class="productOption_form_displayType">
						<option value="0">{t _ProductOption_displayType_selectBox}</option>
						<option value="1">{t _ProductOption_displayType_radioButtons}</option>
						<option value="2">{t _ProductOption_displayType_color}</option>
					</select>
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p>
				<label class="productOption_form_selectMessage_label">{tip _ProductOption_selectMessage}</label>
				<fieldset class="error">
					<input type="text" name="selectMessage" class="productOption_form_selectMessage"  />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div class="optionFile">
			<p>
				<label class="productOption_form_fileExtensions_label">
						{tip _ProductOption_fileExtensions _ProductOption_fileExtensions_help}
				</label>
				<fieldset class="error">
					<input type="text" name="fileExtensions" class="productOption_form_fileExtensions"  />
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>

			<p>
				<label class="productOption_form_maxFileSize_label">
					{capture assign=maxTip}{maketext text=_ProductOption_maxFileSize_help params=$maxUploadSize}{/capture}
					{tip _ProductOption_maxFileSize $maxTip}
				</label>
				<fieldset class="error">
					<input type="text" name="maxFileSize" class="number productOption_form_maxFileSize"  /> {t _ProductOption_megabytes}
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div class="optionPriceContainer">
			<p>
				<label class="productOption_form_priceDiff_label">{tip _option_price_diff}</label>
				<fieldset class="error">
					<input type="text" name="priceDiff" class="number productOption_form_priceDiff"  />
					{$defaultCurrencyCode}
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div>
			<p>
				<label class="productOption_form_description_label">{tip _ProductOption_description}</label>
				<fieldset class="error">
					<textarea name="description" class="productOption_form_description" ></textarea>
					<span class="errorText hidden"> </span>
				</fieldset>
			</p>
		</div>

		<div class="clear"></div>

		{language}
			<p>
				<label class="translation_name_label">{t _ProductOption_title}</label>
				<input type="text" name="name_{$lang.ID}"  />
			</p>

			<p class="optionSelectMessage">
				<label class="translation_name_label">{toolTip label=_ProductOption_selectMessage hint=_tip_ProductOption_selectMessage}</label>
				<input type="text" name="selectMessage_{$lang.ID}"  />

			</p>

			<p>
				<label class="translation_description_label">{tip _ProductOption_description}</label>
				<textarea name="description_{$lang.ID}" ></textarea>
			</p>
		{/language}

	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="productOption_step_lev1 productOption_step_values">
	<legend>{t _ProductOption_step_two}</legend>

		<p>
		<fieldset class="group productOption_form_values_group">
			<div class="productOption_values">
				<p>
					<ul class="activeList_add_sort activeList_add_delete">
						<li class="dom_template productOption_form_values_value singleInput productOption_update" id="productOption_form_values_" style="display: block;">
							<p>
								<label>{tip _option_name}</label>
								<fieldset class="container">
									<input type="text" class="productOption_update productOption_valueName"  />
									<span class="errorText hidden"> </span>
								</fieldset>
							</p>
							<p>
								<label>{tip _option_price_diff}</label>
								<input type="text" class="number productOption_valuePrice"  />
								{$defaultCurrencyCode}
							</p>

							<p class="selectColor">
								<label>{t _select_color}</label>
								<input type="text" class="number productOption_color"  />
							</p>

							<br class="clear" />
						</li>
					</ul>
				</p>
				<p class="productOption_values_controls">
					<a href="#add" class="productOption_add_field">{t _ProductOption_add_values}</a>
				</p>

				{language}
					<ul>
						<li class="dom_template productOption_form_values_value" id="productOption_form_values_">
							<fieldset class="error">
								<label class="productOption_update"> </label>
								<input class="productOption_update" type="text"  />
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