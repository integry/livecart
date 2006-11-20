<form>
<fieldset>
<legend class="specField_title">Field</legend>

	<a href="#step_main" class="change_state" >Main</a>
	<a href="#step_values" class="change_state" >Values</a>
	<a href="#step_translations" class="change_state">Translations</a>

	<!-- STEP 1 -->
	<fieldset class="step_lev1 step_main">
		<legend>Step 1 (Main language _ English)</legend>

		<input type="hidden" name="id" class="hidden specField_form_id" />

		<p>
    		<label>t title <em class="required">*</em></label>
    		<input type="text" name="title" class="required specField_form_title" />
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
		<fieldset class="group specField_form_valueType">
    		<h2>t value type</h2>
    		<div>
                <p>
        			<input type="radio" name="valueType" value="text" class="radio" />
        		    <label class="radio">Text</label>
                </p>
            </div>
            <div>
                <p>
        			<input type="radio" name="valueType" value="numbers" class="radio" />
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
	<fieldset class="step_lev1 step_values">
		<legend>Step 2 (Values)</legend>


		<p>
		<fieldset class="group specField_form_values_group">
    		<h2>t values</h2>
    		<div>
                <p>
        			<ul class="activeList_add_sort activeList_add_delete">
        				<li class="dom_template specField_form_values_value" id="specField_form_values_">
        					<input type="text" class="sortable_drag_handle" />
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="add_field" s>Enter more values</a>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>

		<label style="display: none">Can select multiple entries</label>
		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField_form_multipleSelector" />
	</fieldset>

	<!-- STEP 3 -->
	<fieldset class="step_lev1 step_translations">
		<legend>Step 3 (Translations)</legend>

		<div class="specFields_form_values_translations_language_links">
			<div class="dom_template"><a href="#step_translations_language_">language</a></div>
		</div>

		<fieldset class="step_translations_language dom_template step_translations_language_">
			<legend></legend>

			<label>t title</label>
			<input type="text" name="title" />
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
</fieldset>
</form>
<script type="text/javascript">new LiveCart.SpecFieldManager({json array=$specFieldsList});</script>