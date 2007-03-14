<ul class="tabs">
    <li class="active"><a href="#step_main" class="specField_change_state" >{t _SpecField_main}</a></li>
    <li><a href="#step_values" class="specField_change_state" >{t _SpecField_values}</a></li>
</ul>

<form action="{link controller=backend.specField action=save}" method="post" class="specField">
	<!-- STEP 1 -->
	<fieldset class="specField_step_lev1 specField_step_main">
    <legend>{t _SpecField_step_one}</legend>

		<input type="hidden" name="ID" class="hidden specField_form_id" />
		<input type="hidden" name="categoryID" class="hidden specField_form_categoryID" />

		<p>
    		<label class="specField_form_type_label">{t _SpecField_type}</label>
            <fieldset class="error">
        		<select name="type" class="specField_form_type">
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
    		<input type="checkbox" value="1" name="isRequired" class="checkbox specField_form_isRequired" />
    		<label class="specField_form_isRequired_label">{t _SpecField_is_required}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
    		<label class="specField_form_multipleSelector_label">{t _SpecField_select_multiple}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="isDisplayed" class="checkbox specField_form_isDisplayed" />
    		<label class="specField_form_isDisplayed_label">{t _SpecField_displayed_on_front_page}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="isDisplayedInList" class="checkbox specField_form_isDisplayedInList" />
    		<label class="specField_form_isDisplayedInList_label">{t _SpecField_displayed_in_product_list}</label>
		</p>
              
		<p class="checkbox specField_form_advancedText">
    		<input type="checkbox" value="1" name="advancedText" class="checkbox" />
    		<label class="specField_form_advancedText_label">{t _SpecField_formated_text}</label>
		</p>
        
        <div>
    		<p class="required">
        		<label class="specField_form_name_label">{t _SpecField_title}</label>
                <fieldset class="error">
            		<input type="text" name="name" class="specField_form_name" />
            		<span class="errorText hidden"> </span>
                </fieldset>
        	</p>
    
    		<p class="specField_handle">
        		<label  class="specField_form_handle_label">{t _SpecField_handle}</label>
                <fieldset class="error">
            		<input type="text" name="handle" class="specField_form_handle" />
            		<span class="errorText hidden"> </span>
                </fieldset>
    		</p>
      
    		<p>
        		<label  class="specField_form_valuePrefix_label">{t _SpecField_valuePrefix}</label>
                <fieldset class="error">
            		<input type="text" name="valuePrefix" class="specField_form_valuePrefix" />
            		<span class="errorText hidden"> </span>
                </fieldset>
    		</p>
            
    		<p>
        		<label  class="specField_form_valueSuffix_label">{t _SpecField_valueSuffix}</label>
                <fieldset class="error">
            		<input type="text" name="valueSuffix" class="specField_form_valueSuffix" />
            		<span class="errorText hidden"> </span>
                </fieldset>
    		</p>

    		<p>
        		<label class="specField_form_description_label">{t _SpecField_description}</label>
                
                <fieldset class="error">
            		<textarea name="description" class="specField_form_description" rows="5" cols="40"></textarea>
            		<span class="errorText hidden"> </span>
                </fieldset>
    		</p>
        </div>
        
    	<!-- STEP 3 -->
    	<div class="specField_step_translations">
    		<fieldset class="dom_template specField_step_translations_language specField_step_translations_language_">
    			<legend><span class="expandIcon">[+]</span><span class="specField_legend_text"></span></legend>
    
                <div class="activeForm_translation_values specField_language_translation">
                    <p>
            			<label>{t _SpecField_title}</label>
            			<input type="text" name="name" />
        			</p>
                    <p>
            			<label>{t _SpecField_valuePrefix}</label>
            			<input type="text" name="valuePrefix" />
        			</p>
                    <p>
            			<label>{t _SpecField_valueSuffix}</label>
            			<input type="text" name="valueSuffix" />
        			</p>
                    <p>
            			<label>{t _SpecField_description}</label>
            			<textarea name="description" rows="5" cols="40"></textarea>
        			</p>
                </div>
    		</fieldset>
    	</div>
            
	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="specField_step_lev1 specField_step_values">
    <legend>{t _SpecField_step_two}</legend>


		<p>
		<fieldset class="group specField_form_values_group">
    		<h2 class="specField_values_title">{t _SpecField_values}</h2>
    		<div class="specField_values">
                <p>
        			<ul class="activeList_add_sort activeList_add_delete">
        				<li class="dom_template specField_form_values_value specField_update" id="specField_form_values_" style="display: block;">
                            <input type="text" class="specField_update" />
                    		<span class="errorText hidden"> </span>
                            <br class="clear" />
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="specField_add_field">{t _SpecField_add_values}</a>
                </p>   
                     	
                        
                <!-- STEP 3 -->
            	<div class="specField_step_values_translations">
            		<fieldset class="dom_template specField_step_translations_language specField_step_translations_language_">
            			<legend><span class="expandIcon">[+]</span><span class="specField_legend_text"></span></legend>
            
                        <div class="activeForm_translation_values specField_form_values_translations specField_language_translation">
                            <p>
            					<ul>
            						<li class="dom_template specField_form_values_value" id="specField_form_values_">
            							<label class="specField_update"> </label>
            							<input class="specField_update" type="text" />
            							<br />
            						</li>
            					</ul>
                            </p>
                        </div>
            		</fieldset>
            	</div>
                
                
			</div>

			<br class="clear" />
		</fieldset>
		</p>

	</fieldset>


    <fieldset class="specField_controls">
    	<span class="activeForm_progress"></span>
        <input type="submit" class="specField_save button submit" value="{translate text=_save}" />
        {t _or}
        <a href="#cancel" class="specField_cancel cancel">{t _cancel}</a>
    </fieldset>

</form>