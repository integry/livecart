{*
<head><!--
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/library/scriptaculous/builder.js"></script>
	<script type="text/javascript" src="javascript/backend/specFieldManager.js"></script>-->
	<title>Fields</title>
</head>

<h2>Laptop</h2>


{literal}
<style type="text/css">
.step-translations-language
{
	display: none;
}
fieldset.step-main, fieldset.step-values, fieldset.step-translations
{
	display: none;
}

.specField-form-values-group div
{
    width: 20em  !important ;
}

.specField-form-values-group div input
{
    width: 10em  !important ;
}

.sortable-drag-handler, .sortable-drag-handler-for-fields {
	cursor: pointer;
	cursor: hand;
    background: yellow;
    color: black;
    float: left;
}

ul#specField-items-list, ul#specField-items-list ul
{
    list-style: none;
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

{activeList id="languageList" sortable=true deletable=true handlerClass=langListHandler}
	{foreach from=$languagesList item=item}
		{include file="backend/language/listItem.tpl"}
	{/foreach}
{/activeList}



{activeList id="languageList" sortable=true deletable=true handlerClass=langListHandler}
	{foreach from=$languagesList item=item}
<ul id="specField-items-list">
    <li id="specField-item-96">

    	{include file="backend/specField/item.tpl" class="specField-item"}

    	{literal}
    	<script type="text/javascript">
    	var specField = {
    		id: 96,
    		handle: 	'field1',

    		type: 		'_selector',
    		valueType: 	'text',
    		multipleSelector: true,

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
    </li>


    <li id="specField-item-95">
    <div class="sortable-drag-handler-for-fields">D</div>
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
    </li>




    <li id="specField-item-100">
    <div class="sortable-drag-handler-for-fields">D</div>

    	{include file="backend/specField/item.tpl" class="specField-item"}

    	{literal}
    	<script type="text/javascript">
    	var specField = {
    		id: 100,
    		handle: 	'field1',

    		type: 		'text',
    		valueType: 	'text',

    		translations: {
    			en: {title: 'Other features',		description: 'Other features'},
    			lt: {title: 'Kiti navorotai',		description: 'Kiti navorotai'},
    			de: {title: 'Blachen fileich',		description: 'Blachen fileich'}
    		}
    	}

    	new LiveCart.SpecFieldManager(specField);
    	</script>
    	{/literal}
    </li>

    <li id="specField-item-101">
    <div class="sortable-drag-handler-for-fields">D</div>

    	{include file="backend/specField/item.tpl" class="specField-item"}

    	{literal}
    	<script type="text/javascript">
    	var specField = {
    		id: 101,
    		handle: 	'field1',

    		type: 		'selector',
    		valueType: 	'numbers',

    		translations: {
    			en: {title: 'Waranty',		description: 'Years waranty'},
    			lt: {title: 'Garantija',	description: 'Garantija metais'},
    			de: {title: 'Gharanty',		description: 'Gharanty yahr'}
    		},

    		values: {
    			12: {en: 1},
    			13: {en: 2},
    			3: {en: 3},
    			4: {en: 4},
    			5: {en: 5},
    			6: {en: 6},
    			7: {en: 7},
    			8: {en: 8},
    			9: {en: 9},
    			10: {en: 10},
    			11: {en: 100}
    		}
    	}

    	new LiveCart.SpecFieldManager(specField);
    	</script>
    	{/literal}
    </li>



    <li id="specField-item-102">
    <div class="sortable-drag-handler-for-fields">D</div>

    	{include file="backend/specField/item.tpl" class="specField-item"}

    	{literal}
    	<script type="text/javascript">
    	var specField = {
    		id: 102,
    		handle: 	'field1',

    		type: 		'_selector',
    		valueType: 	'text',

    		translations: {
    			en: {title: 'Pressent',		description: 'You will get a pressent when you buy this product'},
    			lt: {title: 'Dovana',		description: 'Gausite dovana perkant si produkta'},
    			de: {title: 'Preshentwirdshihtceit',		description: 'Present mit bhot das kein!'}
    		},


    		values: {
    			45: {en: 'TV tunner', lt: 'TV tuneris', 	 de: 'TV thuner'},
    			46: {en: 'Ultraslim', lt: 'Super plonas', de: 'Shicht'},
    			47: {en: 'Life time waranty', lt: 'Amzina garantiha', de: 'Das gluklich garantee'}
    		}
    	}

    	new LiveCart.SpecFieldManager(specField);
    	</script>
    	{/literal}
    </li>
</ul>

{literal}
<script type="text/javascript">
function updateField()
{
    alert('sada');
//    alert(Sortable.serialize('specField-items-list'));
}

Sortable.create('specField-items-list',
{
   dropOnEmpty: true,
   containment: ['specField-items-list'],
   constraint: false,
   handler: "sortable-drag-handler-for-fields"
});
</script>
{/literal}
*}

{$configuration}