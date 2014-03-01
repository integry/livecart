{foreach $variations.variations as $variationType}
	<p>
		<label>[[variationType.name()]]</label>
		<select name="variation_[[variationType.ID]]">
			<option value="">{t _choose}</option>
			{foreach from=$variationType.selectOptions key=id item=name}
				<option value="[[id]]">[[name]]</option>
			{/foreach}
		</select>
		<div class="text-danger hidden"></div>
		{error for="variation_`$variationType.ID`"}<div class="text-danger">[[msg]]</div>{/error}
	</p>
{/foreach}

<span id="variationOptionTemplate" style="display: none;">%name (%price)</span>

<script type="text/javascript">
	new Product.Variations($('{$container|@or:'variations'}'), {json array=$variations}, {ldelim}currency: '[[currency]]'{rdelim});
</script>
