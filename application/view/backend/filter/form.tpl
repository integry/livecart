<ul class="tabs">
    <li class="active"><a href="#step_main" class="filter_change_state" >{t _Filter_main}</a></li>
    <li><a href="#step_filters" class="filter_change_state" >{t _Filter_filters}</a></li>
    <li><a href="#step_translations" class="filter_change_state">{t _Filter_translations}</a></li>
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
        				<li class="dom_template filter_form_filters_value" id="filter_form_filters_">
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
            				    <br />
            				</span>
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="filter_add_filter">{t _Filter_add_filter}</a>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>
	</fieldset>

	<!-- STEP 3 -->
	<fieldset class="filter_step_lev1 filter_step_translations">
    <legend>{t _Filter_step_three}</legend>

		<ul class="filter_form_filters_translations_language_links tabs">
			<li class="dom_template filter_language_link"><a href="#step_translations_language_" class="filter_translations_links">language</a></li>
		</ul>

		<fieldset class="filter_step_translations_language dom_template filter_step_translations_language_">
			<legend></legend>

			<label>{t _Filter_name}</label>
			<input type="text" name="name" />
			<br />

			<fieldset class="filter_form_filters_translations">
				<legend>{t _Filter_filters}</legend>
					<ul>
						<li class="dom_template filter_form_filters_value" id="filter_form_filters_">
							<label></label>
							<input type="text" />
							<br />
						</li>
					</ul>
			</fieldset>
		</fieldset>
	</fieldset>
</form>

<fieldset class="filter_controls">
    <input type="button" class="filter_save button" value="{translate text=_save}" />
    {t _or}
    <a href="#cancel" class="filter_cancel">{t _cancel}</a>
</fieldset>