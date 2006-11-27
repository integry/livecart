<a href="#step_main" class="specField_change_state" >Main</a>
<a href="#step_values" class="specField_change_state" >Values</a>
<a href="#step_translations" class="specField_change_state">Translations</a>

<div style="display: inline;" class="specField_controls">
==>
<input type="button" class="specField_save" value="Save" />
or <a href="#cancel" class="specField_cancel">Cancel</a>
</div>

<form action="/backend.specField/save" method="post">
	<!-- STEP 1 -->
	<fieldset class="specField_step_lev1 specField_step_main">
		<legend>Step 1 (Main language _ English)</legend>

		<input type="hidden" name="ID" class="hidden specField_form_id" />
		<input type="hidden" name="categoryID" class="hidden specField_form_categoryID" />

		<p>
    		<label>t title <em class="required">*</em></label>
    		<input type="text" name="name" class="required specField_form_name" />
		</p>

		<p>
    		<label>t handle</label>
    		<input type="text" name="handle" class="specField_form_handle" />
		</p>

		<p>
    		<label>t description</label>
    		<textarea name="description" class="specField_form_description" rows="5" cols="40"></textarea>
		</p>

		<p>
		<fieldset class="group specField_form_dataType">
    		<h2>t value type</h2>
    		<div>
                <p>
        			<input type="radio" name="dataType" value="1" class="radio" />
        		    <label class="radio">Text</label>
                </p>
            </div>
            <div>
                <p>
        			<input type="radio" name="dataType" value="2" class="radio" />
        			<label class="radio">Numbers</label>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>

		<p>
    		<label>t type</label>
    		<select name="type" class="specField_form_type"></select>
		</p>
	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="specField_step_lev1 specField_step_values">
		<legend>Step 2 (Values)</legend>


		<p>
		<fieldset class="group specField_form_values_group">
    		<h2>t values</h2>
    		<div>
                <p>
        			<ul class="activeList_add_sort activeList_add_delete">
        				<li class="dom_template specField_form_values_value" id="specField_form_values_">
        					<input type="text"  />
        					{* <input type="checkbox" value="1" class="specField_form_values_default_checkbox" /> *}
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="specField_add_field" s>Enter more values</a>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>

		<label>Can select multiple entries</label>
		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
	</fieldset>

	<!-- STEP 3 -->
	<fieldset class="specField_step_lev1 specField_step_translations">
		<legend>Step 3 (Translations)</legend>

		<div class="specField_form_values_translations_language_links">
			<div class="dom_template specField_language_link"><a href="#step_translations_language_">language</a></div>
		</div>

		<fieldset class="specField_step_translations_language dom_template specField_step_translations_language_">
			<legend></legend>

			<label>t title</label>
			<input type="text" name="name" />
			<br />

			<label>t description</label>
			<textarea name="description" rows="5" cols="40"></textarea>
			<br />

			<fieldset class="specField_form_values_translations">
				<legend>Values</legend>
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

<script type="text/javascript">new LiveCart.SpecFieldManager({json array=$specFieldsList});</script>