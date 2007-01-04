		Product specification...
		{foreach from=$specFieldList item=field}
		<p>
			<label>{$field.name.en}</label>
			{if $field.type == 1}
				{selectfield}

			{elseif $field.type == 2}
				{textfield name=$field.ID}

			{elseif $field.type == 3}
				{textfield}

			{elseif $field.type == 4}
				{textarea}

			{elseif $field.type == 5}
				{selectfield}

			{elseif $field.type == 6}
				{html_select_date}

			{/if}
		</p>
		{/foreach}