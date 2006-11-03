<head>
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>
	<title>Fields</title>
</head>

<h2>Laptop</h2>


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

<script type="text/javascript">
{literal}
	LiveCart.SpecFieldManager.prototype.languages = 
	{
		en: 'English',
		lt: 'Lithuanian',
		de: 'German'
	}
			
	LiveCart.SpecFieldManager.prototype.types = 
	{
		numbers: 
		[
			new Option('Selector', 'selector'),
			new Option('Numbers', 'numbers')
		],
		text: 
		[
			new Option('Text', 'text'),
			new Option('Word processer', 'wordProcesser'),
			new Option('{t _selector}', '_selector'),
			new Option('Date', 'date')
		]
	}
	
	LiveCart.SpecFieldManager.prototype.messages = { deleteField: 'delete field'	}
	LiveCart.SpecFieldManager.prototype.selectorValueTypes = ['_selector', 'selector'];
	LiveCart.SpecFieldManager.prototype.doNotTranslateTheseValueTypes = ['numbers'];
	LiveCart.SpecFieldManager.prototype.countNewValues = 0;
{/literal}
</script>





<div id="specField-item-95">
	{include file="backend/specField/item.tpl" class="specField-item"}
	
	{literal}
	<script type="text/javascript">
	var specField = {
		id: 95,
		handle: 	'manufacter',
		
		valueType: 	'text',
		type: 		'text',
		
		translations: {
			en: {title: 'Manufacter',		description: 'Apple, Assus, Lenovo etc'},
			lt: {title: 'Gamyntojas',		description: 'Apple, Assus, Lenovo ir kiti'},
			de: {title: 'Machtengiher',		description: 'Apple, Assus, Lenovo und fuhr'}
		}
	}
			
	new LiveCart.SpecFieldManager(specField);
	</script>
	{/literal}
</div>

<div id="specField-item-96">
	{include file="backend/specField/item.tpl" class="specField-item"}
	
	{literal}
	<script type="text/javascript">
	var specField = {
		id: 96,
		handle: 	'field1',
		
		type: 		'_selector',
		valueType: 	'text',
		
		translations: {
			en: {title: 'WiFi',		description: 'Wireless internet'},
			lt: {title: 'WiFi',		description: 'Bevivielis internetas'},
			de: {title: 'WiFi',		description: 'Wirelichtinterneten'}
		},
	
		values: {
			1: {en: 'Yes', lt: 'Yra', de: 'Ya'},
			2: {en: 'No', lt: 'Nera', de: 'Nicht'}
		}
	}
			
	new LiveCart.SpecFieldManager(specField);
	</script>
	{/literal}
</div>



