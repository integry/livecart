<div class="dom-head" style="display: none;">
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>
</div>

<script type="text/javascript">
	var responce = "
		<div class="dom-head" style="display: none;">
			<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
			<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>
		</div>
	";
	
	
</script>

<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>

{literal}
<style type="text/css">
fieldset.step-main, fieldset.step-values, fieldset.step-translations
{
	display: none;
}

div.change-state
{
	text-decoration: underline;
	color: blue;
	cursor: pointer;
	cursor: hand;
}

.dom-template
{
	display: none ! important;
}

.hidden
{
	display: none;
}

#form-specField-values-group ul
{
	padding: 0px;
	margin: 0px;
}


</style>
{/literal}

<form id="form-specField">
<fieldset>
<legend>{t add new category field}</legend>

	<a href="#step-main" class="change-state" >Step 1</a>
	<fieldset class="step-lev1 step-main">
		<legend>Step 1 (Main language - English)</legend>
		
		<input type="hidden" name="id" class="hidden" id="form-specField-id" />
		
		<label for="form-specField-title">{t title}</label>
		<input type="text" name="title" id="form-specField-title" />
		<br />
		
		<label for="form-specField-handle">{t handle}</label>
		<input type="text" name="handle" id="form-specField-handle" />
		<br />
		
		<label for="form-specField-description">{t description}</label>
		<textarea name="description" id="form-specField-description"></textarea>
		<br />
	
		<label for="form-specField-valueType">{t value type}</label>
		<div class="input-group" id="form-specField-valueType">
			<input type="radio" name="valueType" value="text" /> Text
			<input type="radio" name="valueType" value="numbers" /> Numbers
		</div>
		<br />
	
		<label for="form-specField-type">{t type}</label>
		<select name="type" id="form-specField-type"></select>
		<br />
	</fieldset>
	
	<a href="#step-values" class="change-state">Step 2</a>
	<fieldset class="step-lev1 step-values">
		<legend>Step 2 (Values)</legend>
	
		<label for="form-specField-values">{t values}</label>
		<div class="input-group" id="form-specField-values-group">
			<ul>
				<li class="form-specField-values-value dom-template" id="form-specField-values-">
					<input type="text" />
					<a href="#delete" class="delete-value">{t delete}</a>
					<br />
				</li>
			</ul>
			<a href="#add" class="add-field">Enter more values</a>
			<br />
		</div>
	</fieldset>
	
	<a href="#step-translations" class="change-state" >Step 3</a>
	<fieldset class="step-lev1 step-translations">
		<legend>Step 3 (Translations)</legend>
		
		<fieldset class="step-translations-language dom-template">
			<legend></legend>
			
			<label for="form-specField-title">{t title}</label>
			<input type="text" name="title" />
			<br />
			
			<label for="form-specField-description">{t description}</label>
			<textarea name="description"></textarea>
			<br />
			
			<fieldset class="form-specField-values-translations">
				<legend>Values</legend>
					<div class="form-specField-values-value dom-template" id="form-specField-values-">
						<label></label>
						<input type="text" />
						<br />
					</div>
			</fieldset>
		</fieldset>
	</fieldset>
</fieldset>
</form>





{literal}
<script type="text/javascript">
var specField = {
	id: 12,
	handle: 'some_handle',
	valueType: 'numbers',
	type: 'numbers',
	
	languages: {
		en: 'English',
		lt: 'Lithuanian',
		de: 'German',
		ru: 'Russian',
		ch: 'Chineese'
	},
	
	translations: {
		en: {title: 'Cow',description: 'Pretty cow'},
		lt: {title: 'Karve',	description: 'Grazi karve'},
		de: {title: 'Rind',description: 'Herrlich rind'}
	},

	values: {
		1: {en: 'forteen', lt: 'keturiolika'},
		2: {en: 'fifteen', lt: 'penkiolika'},
		3: {en: 'seventeen', lt: 'septiniolika', ru: 'semnadcat'},
		4: {en: 'nineteen',	lt: 'deviniolika'}
	},

	types: {
		numbers: [
			new Option('Selector', 'selector'),
			new Option('Numbers', 'numbers')
		],
		text: [
			new Option('Text', 'text'),
			new Option('Word processer', 'wordProcesser'),
			new Option('{t _selector}', '_selector'),
			new Option('Date', 'date')
		]
	},
	
	selectorValueTypes: ['_selector', 'selector'],
	doNotTranslateTheseValueTypes: ['numbers'],
	
	messages: {
		deleteField: 'delete field'
	}
}
	

new LiveCart.SpecFieldManager(specField);
</script>
{/literal}