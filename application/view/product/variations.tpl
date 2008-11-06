{foreach $variations.variations as $variationType}
	<div>
		<label>{$variationType.name_lang}</label>
		<select name="variation_{$variationType.ID}">
			<option value="">{t _choose}</option>
			{foreach from=$variationType.selectOptions key=id item=name}
				<option value="{$id}">{$name}</option>
			{/foreach}
		</select>
		{error for="variation_`$variationType.ID`"}<div class="errorText">{$msg}</div>{/error}
	</div>
{/foreach}

<script type="text/javascript">
	new Product.Variations($('variations'), {json array=$variations});
</script>