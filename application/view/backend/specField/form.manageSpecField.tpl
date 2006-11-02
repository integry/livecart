<form>
<fieldset>
<legend>{t add new category field}</legend>

	<a href="#step-main" class="change-state" >Main</a>
	<a href="#step-values" class="change-state" >Values</a>
	<a href="#step-translations" class="change-state">Translations</a>
	
	<!-- STEP 1 -->
	<fieldset class="step-lev1 step-main">
		<legend>Step 1 (Main language - English)</legend>
		
		<input type="hidden" name="id" class="hidden specField-form-id" />
		
		<label>{t title}</label>
		<input type="text" name="title" class="specField-form-title" />
		<br />
		
		<label>{t handle}</label>
		<input type="text" name="handle" class="specField-form-handle" />
		<br />
		
		<label>{t description}</label>
		<textarea name="description" class="specField-form-description"></textarea>
		<br />
	
		<label>{t value type}</label>
		<div class="input-group specField-form-valueType">
			<input type="radio" name="valueType" value="text" /> Text
			<input type="radio" name="valueType" value="numbers" /> Numbers
		</div>
		<br />
	
		<label>{t type}</label>
		<select name="type" class="specField-form-type"></select>
		<br />
	</fieldset>
	
	<!-- STEP 2 -->
	<fieldset class="step-lev1 step-values">
		<legend>Step 2 (Values)</legend>
	
		<label>{t values}</label>
		<div class="input-group specField-form-values-group">
			<ul>
				<li class="dom-template specField-form-values-value" id="specField-form-values-">
					<input type="text" />
					<a href="#delete" class="delete-value">{t delete}</a>
					<br />
				</li>
			</ul>
			<a href="#add" class="add-field">Enter more values</a>
			<br />
		</div>
	</fieldset>
	
	<!-- STEP 3 -->
	<fieldset class="step-lev1 step-translations">
		<legend>Step 3 (Translations)</legend>
		
		<fieldset class="step-translations-language dom-template">
			<legend></legend>
			
			<label>{t title}</label>
			<input type="text" name="title" />
			<br />
			
			<label>{t description}</label>
			<textarea name="description"></textarea>
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
					</div>
			</fieldset>
		</fieldset>
	</fieldset>
</fieldset>
</form>