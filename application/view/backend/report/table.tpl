<table class="report">
	<thead>
		<tr>
		{foreach from=$reportData|@reset key=column item=value}
			<th>{translate text="_`$column`"}</th>
		{/foreach}
		</tr>
	</thead>
	<tbody>
		{foreach from=$reportData key=id item=row name="report"}
			<tr>
			{foreach from=$row item=value key=key}
				<td class="[[key]]">
					{% if $key == $format %}
						[[ partial(template) ]]
					{% else %}
						[[value]]
					{% endif %}
				</td>
			{/foreach}
			</tr>
		{/foreach}
	</tbody>
</table>