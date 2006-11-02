<head>
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>
	<title>Fields</title>
</head>



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

<div id="specField-item-96">
	{include file="backend/specField/item.tpl" class="specField-item"}
	
	{literal}
	<script type="text/javascript">
	LiveCart.SpecFieldManager.prototype.languages = {
										en: 'English',
										lt: 'Lithuanian',
										de: 'German',
										ru: 'Russian',
										ch: 'Chineese'
									}
			
	LiveCart.SpecFieldManager.prototype.types = {
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
									}
	
	LiveCart.SpecFieldManager.prototype.messages = {	deleteField: 'delete field'	}
	LiveCart.SpecFieldManager.prototype.selectorValueTypes = ['_selector', 'selector'];
	LiveCart.SpecFieldManager.prototype.doNotTranslateTheseValueTypes = ['numbers'];


	
	var specField = {
		id: 96,
		handle: 'some_handle',
		valueType: 'numbers',
		type: 'numbers',	
		
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
	}
			
	new LiveCart.SpecFieldManager(specField);
	</script>
	{/literal}
</div>




<div id="specField-item-78" class="specField-item">
	{include file="backend/specField/item.tpl"}
	
	{literal}
	<script type="text/javascript">
	var specField = {
		id: 74,
		handle: 'some_handle',
		valueType: 'numbers',
		type: 'numbers',	
		
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
	}
	
	new LiveCart.SpecFieldManager(specField);
	</script>
	{/literal}
</div>

<div id="specField-item-56" class="specField-item">
	{include file="backend/specField/item.tpl"}
	
	{literal}
	<script type="text/javascript">
	var specField = {
		id: 56,
		handle: 'some_handle',
		
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
	}
	
	new LiveCart.SpecFieldManager(specField);
	</script>
	{/literal}
	
</div>
