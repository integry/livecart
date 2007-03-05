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
    		<label>{t _SpecField_type}</label>
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
    		<label>{t _SpecField_is_required}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
    		<label>{t _SpecField_select_multiple}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="isDisplayed" class="checkbox specField_form_isDisplayed" />
    		<label>{t _SpecField_displayed_on_front_page}</label>
		</p>
              
		<p class="checkbox">
    		<input type="checkbox" value="1" name="isDisplayedInList" class="checkbox specField_form_isDisplayedInList" />
    		<label>{t _SpecField_displayed_in_product_list}</label>
		</p>
              
		<p class="checkbox specField_form_advancedText">
    		<input type="checkbox" value="1" name="advancedText" class="checkbox" />
    		<label>{t _SpecField_formated_text}</label>
		</p>
        
        <div>
    		<p>
        		<label>{t _SpecField_title}<em class="required">*</em></label>
                <fieldset class="error">
            		<input type="text" name="name" class="required specField_form_name" />
            		<span class="errorText hidden"> </span>
                </fieldset>
        	</p>
    
    		<p class="specField_handle">
        		<label>{t _SpecField_handle}</label>
                <fieldset class="error">
            		<input type="text" name="handle" class="specField_form_handle" />
            		<span class="errorText hidden"> </span>
                </fieldset>
    		</p>

    		<p>
        		<label>{t _SpecField_description}</label>
                
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
        				<li class="dom_template specField_form_values_value" id="specField_form_values_" style="display: block;">
                            <input type="text" />
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
            							<label> </label>
            							<input type="text" />
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
        <input type="submit" class="specField_save button" value="{translate text=_save}" />
        {t _or}
        <a href="#cancel" class="specField_cancel cancel">{t _cancel}</a>
    </fieldset>

</form>