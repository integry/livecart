{foreach $variations.variations as $variationType}
	<p>
		<label>{$variationType.name_lang}</label>
		<select name="variation_{$variationType.ID}">
			<option value="">{t _choose}</option>
			{foreach from=$variationType.selectOptions key=id item=name}
				<option value="{$id}">{$name}</option>
			{/foreach}
		</select>
	</p>
{/foreach}

<script type="text/javascript">
	new Product.Variations($('variations'), {json array=$variations});
</script>