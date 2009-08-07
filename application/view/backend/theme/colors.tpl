{form action="controller=backend.theme action=saveColors" method="POST" enctype="multipart/form-data" handle=$form id="colors_`$theme.name`"}
	{foreach from=$config item=section}
		<fieldset>
			<legend>{$section.name}</legend>

			{foreach from=$section.properties item=property}
				<p>
					<label>{$property.name}</label>
					{if 'upload' == $property.type}
						{filefield name=$property.var}
					{elseif 'color' == $property.type}
						{textfield name=$property.var id=$property.id class="text color"}
						<script type="text/javascript">
							new jscolor.color($('{$property.id}'), {literal}{adjust: false, required: false, hash: true, caps: false}{/literal});
						</script>
					{elseif 'size' == $property.type}
						<div class="sizeEntry">
							{textfield name=$property.var id=$property.id class="text number"}
							{selectfield options=$measurements}
						</div>
					{elseif 'border' == $property.type}
						{textfield name=$property.var id=$property.id class="text number"}
						px
						{selectfield options=$borderStyles}
						{textfield name=$property.var id=$property.id class="text color"}
					{/if}
				</p>
			{/foreach}
		</fieldset>
	{/foreach}
{/form}

<script type="text/javascript">
colors_`$theme.name`"
</script>