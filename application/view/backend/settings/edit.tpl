{form action="backend.settings/save" class="form-vertical" method="post" handle=$form onsubmit="return settings.save(this);" role="settings.update" id="settings" type="multipart/form-data" target="upload"}

{foreach from=$layouts key=sectionId item=layout name="groups"}
{foreach from=$layout key=groupName item=fields name="groups"}

	{% if !$fields && !$smarty.foreach.groups.first %}
		{% set subsections = false %}
		</fieldset>
	{% endif %}

	[[ partial('backend/settings/sectionHelp.tpl', ['key': "$sectionKey"]) ]]

	<fieldset class="settings" ng-show="activeID == '[[sectionId]]'">

		{% if !empty(groupName) %}
			<legend>{t $groupName}</legend>
		{% endif %}

		{foreach from=$fields key="fieldName" item="foo"}
			<div class="setting" id="setting_[[fieldName]]" {% if 'bool' != $values.$fieldName.type %}style="margin-top: 7px; margin-bottom: 7px;"{% endif %}>
			<div class="row {% if 'bool' == $values.$fieldName.type %} checkbox{% endif %}">

			{% if 'bool' != $values.$fieldName.type %}
				<label for="[[fieldName]]" class="setting">{t `$values.$fieldName.title`}:</label>
			{% endif %}

			<div class="controls">
				{% if 'string' == $values.$fieldName.type %}
					{textfield class="text wide" name=$fieldName id=$fieldName}
				{% elseif 'image' == $values.$fieldName.type %}
					{filefield name=$fieldName id=$fieldName}
					<image class="settingImage" src="{$fieldName|config}" />
				{% elseif 'longtext' == $values.$fieldName.type %}
					{textarea class="tinyMCE" name=$fieldName id=$fieldName}
				{% elseif 'num' == $values.$fieldName.type || 'float' == $values.$fieldName.type %}
					{textfield class="text number" name=$fieldName id=$fieldName}
				{% elseif 'bool' == $values.$fieldName.type %}
					<label for="[[fieldName]]">
						{checkbox name=$fieldName id=$fieldName value="1"}
						{t `$values.$fieldName.title`}
					</label>
				{% elseif is_array($values.$fieldName.type) %}
					{% if 'multi' == $values.$fieldName.extra %}
						<div class="multi">
						{foreach from=$values.$fieldName.type item="value" key="key"}
							<div class="checkbox">
							{checkbox name="`$fieldName`[`$key`]" id="`$fieldName`[`$key`]" value=1}
							<label for="[[fieldName]][[[key]]]">[[value]]</label>
							</div>
						{/foreach}
							<div class="clear"></div>
						</div>
					{% else %}
						{selectfield options=$values.$fieldName.type name=$fieldName id=$fieldName}
					{% endif %}
				{% endif %}
				<div class="text-error hidden"></div>
			</div>
			</div>
			</div>
		{foreachelse}
			{% set subsections = true %}
		{/foreach}

	{% if $fields || $smarty.foreach.groups.last %}
		</fieldset>
	{% endif %}

{/foreach}

{% if !empty(subsections) %}
	</fieldset>
{% endif %}

{/foreach}

<div ng_show="isLangVisible()">
{language}
	{foreach from=$multiLingualValues key="fieldName" item="foo"}
		{input ng_show="'`$values[$fieldName].section`' == activeID" name="`$fieldName`_`$lang.ID`"}
			{label class="setting"}{t `$values.$fieldName.title`}:{/label}
			{% if $types.$fieldName == 'longtext' %}
				{textarea class="tinyMCE" id="`$fieldName`_`$lang.ID`"}
			{% else %}
				{textfield class="text wide" id="`$fieldName`_`$lang.ID`"}
			{% endif %}
		{/input}
	{/foreach}
{/language}
</div>

<input type="hidden" name="id" value="[[id]]" />

<fieldset class="controls">
	<span class="progressIndicator" style="display: none;"></span>
	<input type="submit" value="{t _save}" class="submit" />
</fieldset>
{/form}