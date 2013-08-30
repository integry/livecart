{% if $options[$item.ID] || $moreOptions[$item.ID] %}
	<div class="productOptions">
		{foreach from=$options[$item.ID] item=option}
			{include file="product/optionItem.tpl" selectedChoice=$item.options[$option.ID]}
			{% if 3 == $option.type %}
				<a href="{link controller=order action=downloadOptionFile id=$item.ID query="option=`$option.ID`"}">{$item.options[$option.ID].fileName}</a>
			{% endif %}
		{/foreach}

		{foreach from=$moreOptions[$item.ID] item=option}
			{% if $item.options[$option.ID] %}
				<div class="nonEditableOption">
					[[option.name_lang]]:
					{% if 0 == $option.type %}
						{t _option_yes}
					{% elseif 1 == $option.type %}
						{$item.options[$option.ID].Choice.name_lang}
					{% elseif 3 == $option.type %}
						<a href="{link controller=order action=downloadOptionFile id=$item.ID query="option=`$option.ID`"}">{$item.options[$option.ID].fileName}</a>
						{% if $item.options[$option.ID].small_url %}
							<div class="optionImage">
								<a href="{static url=$item.options[$option.ID].large_url}" rel="lightbox"><img src="{static url=$item.options[$option.ID].small_url}" /></a>
							</div>
						{% endif %}
					{% else %}
						{$item.options[$option.ID].optionText|@htmlspecialchars}
					{% endif %}
					{% if $item.options[$option.ID].Choice.priceDiff != 0 %}
						<span class="optionPrice">
							({$item.options[$option.ID].Choice.formattedPrice.$currency})
						</span>
					{% endif %}
				</div>
			{% endif %}
		{/foreach}

		{% if $moreOptions[$item.ID] %}
		<div class="productOptionsMenu">
			<a href="{link controller=order action=options id=$item.ID}" ajax="{link controller=order action=optionForm id=$item.ID}">{t _edit_options}</a>
		</div>
		{% endif %}
	</div>
{% endif %}