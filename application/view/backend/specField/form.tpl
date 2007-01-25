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
            
            <div>
        		<p>
            		<label>{t _SpecField_title}<em class="required">*</em></label>
            		<input type="text" name="name" class="required specField_form_name" />
            		<span class="feedback"> </span>
            	</p>
        
        		<p class="specField_handle">
            		<label>{t _SpecField_handle}</label>
            		<input type="text" name="handle" class="specField_form_handle" />
            		<span class="feedback"> </span>
        		</p>
        
        		<p>
            		<label>{t _SpecField_description}</label>
            		<textarea name="description" class="specField_form_description" rows="5" cols="40"></textarea>
            		<span class="feedback_textarea"> </span>
        		</p>
            </div>
            
        	<!-- STEP 3 -->
        	<div class="specField_step_translations">
        		<fieldset class="dom_template specField_step_translations_language specField_step_translations_language_">
        			<legend><span class="expandIcon">[+]</span><span class="specField_legend_text"></span></legend>
        
                    <div class="specField_language_translation">
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
        				<li class="dom_template specField_form_values_value" id="specField_form_values_">
        					<input type="text" />
                    		<span class="feedback"> </span>
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
            
                        <div class="specField_form_values_translations specField_language_translation">
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

		<p class="checkbox">
    		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
    		<label>{t _SpecField_select_multiple}</label>
		</p>
	</fieldset>


    <fieldset class="specField_controls">
        <input type="submit" class="specField_save button" value="{translate text=_save}" />
        {t _or}
        <a href="#cancel" class="specField_cancel cancel">{t _cancel}</a>
    </fieldset>

</form>