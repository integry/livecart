{foreach from=$multiLingualSpecFieldss item="field"}
	<p>
		<label for="product_{$cat}_{$item.ID}_{$field.fieldName}_{$lang.ID}">{$field.name_lang}:</label>
		{include file="backend/eav/specFieldFactory.tpl" field=$field language=$lang.ID}
	</p>
{/foreach}