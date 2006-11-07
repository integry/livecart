<form>
<fieldset>
<legend class="specField-title">Field</legend>

	<a href="#step-main" class="change-state" >Main</a>
	<a href="#step-values" class="change-state" >Values</a>
	<a href="#step-translations" class="change-state">Translations</a>

	<!-- STEP 1 -->
	<fieldset class="step-lev1 step-main">
		<legend>Step 1 (Main language - English)</legend>

		<input type="hidden" name="id" class="hidden specField-form-id" />

		<p>
    		<label>{t title} <em class="required">*</em></label>
    		<input type="text" name="title" class="required specField-form-title" />
		</p>

		<p>
    		<label>{t handle}</label>
    		<input type="text" name="handle" class="specField-form-handle" />
		</p>

		<p>
    		<label>{t description}</label>
    		<textarea name="description" class="specField-form-description" rows="5" cols="40"></textarea>
		</p>

		<p>
		<fieldset class="group specField-form-valueType">
    		<h2>{t value type}</h2>
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
    		<label>{t type}</label>
    		<select name="type" class="specField-form-type"></select>
		</p>
	</fieldset>

	<!-- STEP 2 -->
	<fieldset class="step-lev1 step-values">
		<legend>Step 2 (Values)</legend>


		<p>
		<fieldset class="group specField-form-values-group">
    		<h2>{t values}</h2>
    		<div>
                <p>
        			<ul>
        				<li class="dom-template specField-form-values-value" id="specField-form-values-">
        				    <span class="sortable-drag-handler">D</span>
        					<input type="text" class="sortable-drag-handle" />
        					<a href="#delete" class="delete-value" class="other">{t delete}</a>
        				</li>
        			</ul>
                </p>
                <p>
                    <a href="#add" class="add-field" s>Enter more values</a>
                </p>
			</div>

			<br class="clear" />
		</fieldset>
		</p>

		<label style="display: none">Can select multiple entries</label>
		<input type="checkbox" value="1" name="multipleSelector" class="checkbox specField-form-multipleSelector" />
	</fieldset>

	<!-- STEP 3 -->
	<fieldset class="step-lev1 step-translations">
		<legend>Step 3 (Translations)</legend>

		<div class="specFields-form-values-translations-language-links">
			<div class="dom-template"><a href="#step-translations-language-">language</a></div>
		</div>

		<fieldset class="step-translations-language dom-template step-translations-language-">
			<legend></legend>

			<label>{t title}</label>
			<input type="text" name="title" />
			<br />

			<label>{t description}</label>
			<textarea name="description" rows="5" cols="40"></textarea>
			<br />

			<fieldset class="specField-form-values-translations">
				<legend>Values</legend>
					<ul>
						<li class="dom-template specField-form-values-value" id="specField-form-values-">
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