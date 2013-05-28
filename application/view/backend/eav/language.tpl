{foreach from=$multiLingualSpecFieldss item="field"}
	<div class="control-group">
		<label class="control-label" for="product_{$cat}_{$item.ID}_{$field.fieldName}_{$lang.ID}">{$field.name_lang}:</label>
		{include file="backend/eav/specFieldFactory.tpl" field=$field language=$lang.ID}
	</div>
{/foreach}