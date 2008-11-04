{foreach $variations.variations as $variationType}
	<p>
		<label>{$variationType.name_lang}</label>
		{selectfield name="variation_`$variationType.ID`" options=$variationType.selectOptions}
	</p>
{/foreach}
