{form action="controller=backend.theme action=saveColors" method="POST" enctype="multipart/form-data" handle=$form id="colors_`$theme`" target="iframe_`$theme`"}
	{foreach from=$config item=section}
		<fieldset>
			<legend>{$section.name}</legend>

			{foreach from=$section.properties item=property}
				<fieldset class="container entry" rel="{$property.selector}/{$property.type}">
					<label>{$property.name}</label>
					{if 'upload' == $property.type}
						{filefield name=$property.var class="file"}
						- {t _or} -
						{textfield class="text"}
						<div class="imageOptions">
							<div class="repeat">{t _repeat}: {selectfield class="repeat" options=$bgRepeat}</div>
							<div class="position">{t _position}: {selectfield class="position" options=$bgPosition}</div>
						</div>
					{elseif 'color' == $property.type}
						{textfield id=$property.id class="text color"}
						<script type="text/javascript">
							$('{$property.id}').color = new jscolor.color($('{$property.id}'), {literal}{adjust: false, required: false, hash: true, caps: false}{/literal});
						</script>
					{elseif 'size' == $property.type}
						<div class="sizeEntry">
							{textfield id=$property.id class="text number"}
							{selectfield options=$measurements}
						</div>
					{elseif 'border' == $property.type}
						{textfield class="text number"}
						px
						{selectfield options=$borderStyles}
						{textfield id=$property.id class="text color"}
						<script type="text/javascript">
							$('{$property.id}').color = new jscolor.color($('{$property.id}'), {literal}{adjust: false, required: false, hash: true, caps: false}{/literal});
						</script>
					{elseif 'text-decoration' == $property.type}
						{selectfield options=$textStyles}
					{elseif 'font' == $property.type}
						<div class="fontEntry">
							{textfield name=$property.var id=$property.id class="text"}
						</div>
					{/if}
				</fieldset>
			{/foreach}
		</fieldset>
	{/foreach}

	<fieldset class="controls">
		<input type="hidden" name="id" value="{$theme}" />
		<input type="hidden" name="css" value="" />
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" value="{tn _save}" class="submit" />
		{t _or}
		<a class="cancel" href="{link controller=backend.theme}">{t _cancel}</a>
	</fieldset>
{/form}

<iframe src="{link controller=backend.theme action=cssIframe query="theme=`$theme`"}" id="iframe_{$theme}" name="iframe_{$theme}" style="display: none;"></iframe>