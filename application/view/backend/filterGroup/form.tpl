<form action="{link controller=backend.filterGroup action=save}" method="post" class="filter">
	<!-- STEP 1 -->
	<fieldset class="filter_step_lev1 filter_step_main">
    <legend>{t _Filter_step_one}</legend>

		<input type="hidden" name="ID" class="hidden filter_form_id" />

		<p>
    		<label>{t _Filter_name}<em class="required">*</em></label>
            <fieldset class="error">
        		<input type="text" name="name" class="required filter_form_name" />
        		<span class="errorText hidden"> </span>
            </fieldset>
    	</p>

		<p class="filter_specField">
    		<label>{t _Filter_associated_field}</label>
            <fieldset class="error">
     	        <select name="specFieldID" class="filter_form_specFieldID"></select>
                <div class="filter_form_specFieldText"></div>
        		<span class="errorText hidden"> </span>
            </fieldset>
		</p>
        
    	<!-- STEP 2 -->
    	<fieldset class="filter_step_lev1 filter_step_filters">
        <legend>{t _Filter_step_two}</legend>
    	    <p>
    		<fieldset class="group filter_form_filters_group">
        		<h2 class="filter_filters_title">{t _Filter_filters}</h2>
        		<div class="filter_filters">
                    <p>
            			<ul class="activeList_add_sort activeList_add_delete">
            				<li class="dom_template filter_form_filters_value filter_form_filters_value_main" id="filter_form_filters_">
                				<span>
                					<span class="filter_name">
                    					<label>{t _Filter_name}</label>
                                        <fieldset class="error">
                        				    <input type="text" />
                                    		<span class="errorText hidden"> </span>
                                        </fieldset>
                				    </span>

                					<span class="filter_range">
                    					<label>{t _Filter_range}</label>
                        					<input type="text" /> - <input type="text" />
                                    		<span class="errorText hidden"> </span>
                				    </span>
    
                                    <span class="filter_date_range">
                                        <label>{t _Filter_date_range}</label>
                                            <input type="text" /> <img src="image/silk/calendar.png" class="calendar_button" /> - <input type="text" /> <img src="image/silk/calendar.png" class="calendar_button" />
                                            <input type="hidden" class="hidden filter_date_start_real" />
                                            <input type="hidden" class="hidden filter_date_end_real" />
                                            <span class="errorText hidden"> </span>
                                    </span>
                				    <br />
                				</span>
            				</li>
            			</ul>
                    </p>
                    <p class="filter_crate_filters">
                        <a href="#add" class="filter_add_filter">{t _Filter_add_filter}</a> 
                    </p>
    			</div>
    
    			<br class="clear" />
    		</fieldset>
    		</p>
    	</fieldset>
        
    	<!-- STEP 3 -->
    	<fieldset class="filter_step_translations">
            <fieldset class="expandingSection dom_template filter_step_translations_language filter_step_translations_language_">
                <legend></legend>
                <fieldset class="expandingSectionContent">
                    <fieldset class="activeForm_translation_values filter_language_translation">
                        <p>
                			<label>{t _Filter_name}</label>
                			<input type="text" name="name" />
            			</p>
                    </fieldset>
                    <fieldset>
                        <legend>Filters translations</legend>
                        <div class="filter_form_language_translation">
                            <p>
            					<ul>
            						<li class="dom_template filter_form_filters_value" id="filter_form_filters_">
            							<label> </label>
            							<input type="text" />
            							<br />
            						</li>
            					</ul>
                            </p>
                        </div>
                    </fieldset>
                </fieldset>
            </fieldset>
    	</fieldset>
    </fieldset>


    <fieldset class="filter_controls">
        <span class="activeForm_progress"></span>
        <input type="submit" class="filter_save button" value="{translate text=_save}" />
        {t _or}
        <a href="#cancel" class="filter_cancel">{t _cancel}</a>
    </fieldset>
</form>