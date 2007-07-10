<form action="{link controller=backend.filterGroup action=update}" method="post" class="filter" {denied role="category.update"}class="formReadonly"{/denied}>
	<!-- STEP 1 -->
	<fieldset class="filter_step_lev1 filter_step_main">
    <legend>{t _Filter_step_one}</legend>

		<input type="hidden" name="ID" class="hidden filter_form_id" />

		<p class="filter_specField">
    		<label class="filter_form_specFieldID_label">{t _Filter_associated_field}</label>
            <fieldset class="error">
     	        <select name="specFieldID" class="filter_form_specFieldID" {denied role="category.update"}disabled="disabled"{/denied}></select>
                <div class="filter_form_specFieldText"></div>
        		<span class="errorText hidden"> </span>
            </fieldset>
		</p>
		
		<p class="required">
    		<label class="filter_form_name_label">{t _Filter_name}</label>
            <fieldset class="error">
        		<input type="text" name="name" class="required filter_form_name" {denied role="category.update"}readonly="readonly"{/denied} />
        		<span class="errorText hidden"> </span>
            </fieldset>
    	</p>
        
    	<!-- STEP 2 -->
    	<fieldset class="filter_step_lev1 filter_step_filters error">
        <legend>{t _filters}</legend>
    	    <p>
    		<fieldset class="group filter_form_filters_group">
        		<h2 class="filter_filters_title">{t _Filter_filters}</h2>
        		<div class="filter_filters">
                    <p>
            			<ul class="{allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed}">
            				<li class="dom_template filter_form_filters_value filter_form_filters_value_main filter_update" id="filter_form_filters_">
                				<span>
                					<span class="filter_name">
                    					<label class="filter_update">{t _Filter_name}</label>
                                        <fieldset class="error">
                        				    <input type="text" class="filter_update" {denied role="category.update"}readonly="readonly"{/denied} />
                                    		<span class="errorText hidden"> </span>
                                        </fieldset>
                				    </span>

                					<span class="filter_range">
                    					<label class="filter_update">{t _Filter_range}</label>
                                        <fieldset class="error">
                        					<input type="text" class="filter_update" {denied role="category.update"}readonly="readonly"{/denied} /> - <input type="text" class="filter_update" {denied role="category.update"}readonly="readonly"{/denied} />
                                    		<span class="errorText hidden"> </span>
                                        </fieldset>
                				    </span>
    
                                    <span class="filter_date_range">
                                        <label class="filter_update">{t _Filter_date_range}</label>
                                        <fieldset class="error">
                                            <input type="text" class="filter_update" {denied role="category.update"}readonly="readonly"{/denied} /> <img src="image/silk/calendar.png" class="calendar_button filter_update" {denied role="category.update"}style="display: none"{/denied} /> - <input type="text" class="filter_update"  {denied role="category.update"}readonly="readonly"{/denied} /> <img src="image/silk/calendar.png" class="calendar_button"  {denied role="category.update"}style="display: none"{/denied} />
                                            <input type="hidden" class="hidden filter_date_start_real filter_update"  {denied role="category.update"}readonly="readonly"{/denied} />
                                            <input type="hidden" class="hidden filter_date_end_real filter_update"  {denied role="category.update"}readonly="readonly"{/denied} />
                                            <span class="errorText hidden"> </span>
                                        </fieldset>
                                    </span>
                				    <br />
                				</span>
            				</li>
            			</ul>
                    </p>
                    <p class="filter_crate_filters">
                        <a href="#add" class="filter_add_filter" {denied role="category.update"}style="display: none"{/denied}>{t _Filter_add_filter}</a> 
                    </p>
    			</div>
    
    			<br class="clear" />
    		</fieldset>
    		</p>
    	</fieldset>
        
    	<!-- STEP 3 -->
    	<fieldset class="filter_step_translations container">
        {language}
            <fieldset class="activeForm_translation_values filter_language_translation">
                <fieldset class="error">
        			<label>{t _Filter_name}</label>
        			<input type="text" name="name_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
        		</fieldset>
            </fieldset>
            
    		<h5 class="filter_filters_title">{t _Filter_filters}:</h5>
            <fieldset class="filters_translations_fieldset">
				<ul class="filters_translations_{$lang.ID}">
					<li class="dom_template filter_form_filters_value" id="filter_form_filters_">
						<label class="filter_update"> </label>
						<input type="text" class="filter_update"  {denied role="category.update"}readonly="readonly"{/denied} />
						<br />
					</li>
				</ul>
            </fieldset>
        {/language}
        </fieldset>
    </fieldset>


    <fieldset class="filter_controls controls">
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="filter_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="filter_cancel cancel">{t _cancel}</a>
    </fieldset>
</form>