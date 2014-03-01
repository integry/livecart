{function name="optionPrice" choice=null}
	{% if choice && choice.priceDiff != 0 %}
		<span class="optionPrice">
		{% if choice.Option.isPriceIncluded && choice.formattedTotalPrice.currency %}
			- <span class="optionFullPrice">{choice.formattedTotalPrice.currency}</span>
		{% else %}
			({% if choice && choice.priceDiff > 0 %}+{% endif %}{choice.formattedPrice.currency})
		{% endif %}
		</span>
	{% endif %}
{/function}

<div{% if option.isRequired %} class="required"{% endif %} class="productOption" id="{uniqid assign="optionContainer"}">
	{% if option.fieldName %}{assign var=fieldName value=option.fieldName}{% else %}{assign var=fieldName value="option_`option.ID`"}{% endif %}
	{assign var=fieldName value="`optionPrefix``fieldName`"}
	{% if 0 == option.type %}
		{input name=fieldName}
			{checkbox class="checkbox"}
			{label}[[option.name()]] {optionPrice choice=option.DefaultChoice}{/label}

			{% if option.description() %}
				<p class="description">
					[[option.description()]]
				</p>
			{% endif %}
		{/input}
	{% else %}
		{label}[[option.name()]]{/label}
			{input}
			{% if 1 == option.type %}
				{% if 0 == option.displayType %}
					<fieldset class="error">
					<select name="[[fieldName]]">
						<option value="">[[option.selectMessage()]]</option>
						{foreach from=option.choices item=choice}
							<option value="[[choice.ID]]"{% if selectedChoice.Choice.ID == choice.ID %} selected="selected"{% endif %}>
								[[choice.name()]]
								{optionPrice choice=choice}
							</option>
						{% endfor %}
					</select>
				{% else %}
					<div class="radioOptions {% if 2 == option.displayType %}colorOptions{% endif %}">
						{% if option.selectMessage() %}
							<p>
								<input name="[[fieldName]]" type="radio" class="radio" id="{uniqid}" value=""{% if !selectedChoice.Choice.ID %} checked="checked"{% endif %} />
								<label class="radio" for="{uniqid last=true}">[[option.selectMessage()]]</label>
							</p>
						{% endif %}

						{foreach from=option.choices item=choice}
							<p>
								<input name="[[fieldName]]" type="radio" class="radio" id="{uniqid}" value="[[choice.ID]]"{% if selectedChoice.Choice.ID == choice.ID %} checked="checked"{% endif %} />
								<label class="radio" for="{uniqid last=true}">
									<span class="optionName"  {% if 2 == option.displayType %}style="background-color: [[choice.config.color]];"{% endif %}>[[choice.name()]]</span>
									{optionPrice choice=choice}
								</label>
							</p>
						{% endfor %}
						<div class="clear"></div>
					</div>
				{% endif %}
			{% elseif 2 == option.type %}
				{textfield class="text"}
			{% elseif 3 == option.type %}
				{uniqid assign=uniq noecho=true}
				{filefield name="upload_`fieldName`" id=uniq}
				{hidden name=fieldName}
				{error for="upload_`fieldName`"}<div class="text-danger">[[msg]]</div>{/error}
				<div class="optionFileInfo" style="display: none;">
					<div class="optionFileName"></div>
					<div class="optionFileImage">
						<img src="" class="optionThumb" alt="" />
					</div>
				</div>
				<script type="text/javascript">
					var upload = ('[[uniq]]');
					new LiveCart.FileUpload(upload, '[[ url("order/uploadOptionFile/" ~ option.ID, "uniq=`uniq`&field=`fieldName`&productID=`product.ID`") ]]', Order.previewOptionImage);
				</script>
			{% endif %}

			{% if option.description() %}
				<p class="description">
					[[option.description()]]
				</p>
			{% endif %}

		{/input}
	{% endif %}
</div>
<div class="clear"></div>

<script type="text/javascript">
	Frontend.initColorOptions("[[optionContainer]]");
</script>
