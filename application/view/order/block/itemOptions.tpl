{% if options[item.ID] || moreOptions[item.ID] %}
	<div class="productOptions">
		{foreach from=options[item.ID] item=option}
			[[ partial('product/optionItem.tpl', ['selectedChoice': item.options[option.ID]]) ]]
			{% if 3 == option.type %}
				<a href="[[ url("order/downloadOptionFile/" ~ item.ID, "option=`option.ID`") ]]">{item.options[option.ID].fileName}</a>
			{% endif %}
		{% endfor %}

		{foreach from=moreOptions[item.ID] item=option}
			{% if item.options[option.ID] %}
				<div class="nonEditableOption">
					[[option.name()]]:
					{% if 0 == option.type %}
						{t _option_yes}
					{% elseif 1 == option.type %}
						{item.options[option.ID].Choice.name()}
					{% elseif 3 == option.type %}
						<a href="[[ url("order/downloadOptionFile/" ~ item.ID, "option=`option.ID`") ]]">{item.options[option.ID].fileName}</a>
						{% if item.options[option.ID].small_url %}
							<div class="optionImage">
								<a href="{static url=item.options[option.ID].large_url}" rel="lightbox"><img src="{static url=item.options[option.ID].small_url}" /></a>
							</div>
						{% endif %}
					{% else %}
						{item.options[option.ID].optionText|@htmlspecialchars}
					{% endif %}
					{% if item.options[option.ID].Choice.priceDiff != 0 %}
						<span class="optionPrice">
							({item.options[option.ID].Choice.formattedPrice.currency})
						</span>
					{% endif %}
				</div>
			{% endif %}
		{% endfor %}

		{% if moreOptions[item.ID] %}
		<div class="productOptionsMenu">
			<a href="[[ url("order/options/" ~ item.ID) ]]" ajax="[[ url("order/optionForm/" ~ item.ID) ]]">{t _edit_options}</a>
		</div>
		{% endif %}
	</div>
{% endif %}