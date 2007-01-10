<ul class="tabs">
    <li class="active"><a href="#step_main" class="filter_change_state" >{t _Filter_main}</a></li>
    <li><a href="#step_filters" class="filter_change_state" >{t _Filter_filters}</a></li>
</ul>

<form action="{link controller=backend.filter action=save}" method="post" class="filter">
	<!-- STEP 1 -->
	<fieldset class="filter_step_lev1 filter_step_main">
    <legend>{t _Filter_step_one}</legend>

		<input type="hidden" name="ID" class="hidden filter_form_id" />

		<p>
    		<label>{t _Filter_name}<em class="required">*</em></label>
    		<input type="text" name="name" class="required filter_form_name" />
    		<span class="feedback"> </span>
    	</p>

		<p>
    		<label>{t _Filter_associated_field}</label>
 	        <select name="specFieldID" class="filter_form_specFieldID"></select>
    		<span class="feedback"> </span>
		</p>
        
        
    	<!-- STEP 3 -->
    	<div class="filter_step_translations">
            <fieldset class="dom_template filter_step_translations_language filter_step_translations_language_">
                <legend><span class="expandIcon">[+] </span><span class="filter_legend_text"></span></legend>
                <div class="filter_language_translation">
                    <p>
            			<label>{t _Filter_name}</label>
            			<input type="text" name="name" />
        			</p>
                </div>
            </fieldset>
    	</div>
	</fieldset>

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
            					<p>
                					<label>{t _Filter_name}</label>
                				    <input type="text" />
                            		<span class="feedback"> </span>
            				    </p>
            					<p class="filter_range">
                					<label>{t _Filter_range}</label>
                					<input type="text" /> - <input type="text" />
                            		<span class="feedback"> </span>
            				    </p>
                                <p class="filter_selector">
                                    <label>{t _Filter_value}</label>
                                    <select type="text" ></select>
                                    <span class="feedback"> </span>
                                </p>
                                <p class="filter_date_range">
                                    <label>{t _Filter_date_range}</label>
                                    <input type="text" /> <img src="image/silk/calendar.png" class="calendar_button" /> - <input type="text" /> <img src="image/silk/calendar.png" class="calendar_button" />
                                    <input type="hidden" class="hidden filter_date_start_real" />
                                    <input type="hidden" class="hidden filter_date_end_real" />
                                    <span class="feedback"> </span>
                                </p>
            				    <br />
            				</span>
        				</li>
        			</ul>
                </p>
                <p class="filter_crate_filters">
                    <a href="#add" class="filter_add_filter">{t _Filter_add_filter}</a> <a href="#generate" class="filter_generate_filters">{t _Filter_generate_filters}</a>
                </p>
                
                
                <!-- STEP 3 -->
            	<div class="filter_step_filters_translations">
            		<fieldset class="dom_template filter_step_translations_language filter_step_translations_language_">
            			<legend><span class="expandIcon">[+] </span><span class="filter_legend_text"></span></legend>
            
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
            	</div>
                
                
			</div>

			<br class="clear" />
		</fieldset>
		</p>
	</fieldset>
</form>

<fieldset class="filter_controls">
    <input type="button" class="filter_save button" value="{translate text=_save}" />
    {t _or}
    <a href="#cancel" class="filter_cancel">{t _cancel}</a>
</fieldset>