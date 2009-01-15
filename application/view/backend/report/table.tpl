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
			<tr class="{zebra loop="report"}">
			{foreach from=$row item=value key=key}
				<td class="{$key}">
					{if $key == $format}
						{include file=$template}
					{else}
						{$value}
					{/if}
				</td>
			{/foreach}
			</tr>
		{/foreach}
	</tbody>
</table>