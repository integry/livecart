{language}
	{input name="name_`$lang.ID`"}
		{label}{t _product_name}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=name"}
	{/input}

	{input name="shortDescription_`$lang.ID`"}
		{label}{t _short_description}:{/label}
		{textarea class="shortDescr tinyMCE"}
	{/input}

	{input name="longDescription_`$lang.ID`"}
		{label}{t _long_description}:{/label}
		{textarea class="longDescr tinyMCE"}
	{/input}

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