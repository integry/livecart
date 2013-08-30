{language}
	{input name="name_`$lang.ID`"}
		{label}{t _product_name}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=name"}
	{/input}

	[[ textarea('shortDescription_`$lang.ID`', '_short_description', class: 'shortDescr tinyMCE') ]]

	[[ textarea('longDescription_`$lang.ID`', '_long_description', class: 'longDescr tinyMCE') ]]

	{input name="pageTitle_`$lang.ID`"}
		{label}{t _pageTitle}:{/label}
		{textfield name="pageTitle_`$lang.ID`" class="wide"}
	{/input}

	{if $multiLingualSpecFieldss}
	<fieldset>
		<legend>{t _specification_attributes}</legend>
		{include file="backend/eav/language.tpl" item=$product cat=$cat language=$lang.ID}
	</fieldset>
	{/if}
{/language}