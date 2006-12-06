<a href="#step_main" class="specField_change_state specField_change_state_active" >{t _SpecField_main}</a>
<a href="#step_values" class="specField_change_state" >{t _SpecField_values}</a>
<a href="#step_translations" class="specField_change_state">{t _SpecField_translations}</a>

<div style="display: inline;" class="specField_controls">
    <input type="button" class="specField_save button" value="{translate text=_save}" />
    {t _or}
    <a href="#cancel" class="specField_cancel">{t _cancel}</a>
</div>

<form action="{link controller=backend.specField action=save}" method="post" class="specField">
	<!-- STEP 1 -->
	<fieldset class="specField_step_lev1 specField_step_main">
		<legend>{t _SpecField_step_one}</legend>

		<input type="hidden" name="ID" class="hidden specField_form_id" />
		<input type="hidden" name="categoryID" class="hidden specField_form_categoryID" />

		<p>
    		<label>{t _SpecField_title}<em class="required">*</em></label>
    		<input type="text" name="name" class="required specField_form_name" />
    		<span class="feedback"> </span>
    	</p>

		<p>
    		<label>{t _SpecField_handle}</label>
    		<input type="text" name="handle" class="specField_form_handle" />
    		<span class="feedback"> </span>
		</p>

		<p>
    		<label>{t _SpecField_description}</label>
    		<textarea name="description" class="specField_form_description" rows="5" cols="40"></textarea>
    		<span class="feedback_textarea"> </span>
		</p>

		<p>
		<fieldset class="group specField_form_dataType">
    		<h2>{t _SpecField_value_type}</h2>
    		<div>
                <p>
        			<input type="radio" name="dataType" value="1" class="radio" />
        		    <label class="radio">{t _SpecField_text}</label>
                </p>
            </div>
            <div>
                <p>
        			<input type="radio" name="dataType" value="2" class="radio" />
        			<label class="radio">{t _SpecField_numbers}</label>
                </p>
			</div>

    		<span class="feedback"> </span>
			<br class="clear" />
		</fieldset>
		</p>

		<p>
    		<label>{t _SpecField_type}</label>
    		<select name="type" class="specField_form_type"></select>
    		<span class="feedback"> </span>
		</p>
	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="specField_step_lev1 specField_step_values">
		<legend>{t _SpecField_step_two}</legend>


		<p>
		<fieldset class="group specField_form_values_group">
    		<h2 class="specField_values_title">{t _SpecField_values}</h2>
    		<div class="specField_values">
                <p>
        			<ul class="activeList_add_delete activeList_add_sort">
        				<li class="dom_template specField_form_values_value" id="specField_form_values_">
        					<input type="text"  />
                    		<span class="feedback"> </span>
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="specField_add_field">{t _SpecField_add_values}</a>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>

		<label>{t _SpecField_select_multiple}</label>
		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
	</fieldset>

	<!-- STEP 3 -->
	<fieldset class="specField_step_lev1 specField_step_translations">
		<legend>{t _SpecField_step_three}</legend>

		<div class="specField_form_values_translations_language_links">
			<div class="dom_template specField_language_link"><a href="#step_translations_language_" class="specField_translations_links">language</a></div>
		</div>

		<fieldset class="specField_step_translations_language dom_template specField_step_translations_language_">
			<legend></legend>

			<label>{t _SpecField_title}</label>
			<input type="text" name="name" />
			<br />

			<label>{t _SpecField_description}</label>
			<textarea name="description" rows="5" cols="40"></textarea>
			<br />

			<fieldset class="specField_form_values_translations">
				<legend>{t _SpecField_values}</legend>
					<ul>
						<li class="dom_template specField_form_values_value" id="specField_form_values_">
							<label></label>
							<input type="text" />
							<br />
						</li>
					</ul>
			</fieldset>
		</fieldset>
	</fieldset>
</form>