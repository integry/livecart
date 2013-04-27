{if $fields.ADDRESS1}
	{input name="`$prefix`address1"}
		{label}{t _address}:{/label}
		{textfield}
	{/input}
{/if}

{if $fields.ADDRESS2}
	{input name="`$prefix`address2"}
		{textfield}
	{/input}
{/if}

{if $fields.CITY}
	{input name="`$prefix`city"}
		{label}{t _city}:{/label}
		{textfield}
	{/input}
{/if}

{if $fields.COUNTRY}
	{input name="`$prefix`country"}
		{label}{t _country}:{/label}
		{selectfield options=$countries id="{uniqid assign=id_country}"}
		<span class="progressIndicator" style="display: none;"></span>
	{/input}
{else}
	{hidden name="`$prefix`country" id="{uniqid assign=id_country}"}
{/if}

{if $fields.STATE}
	{include file="user/addressFormState.tpl" prefix=$prefix}
{/if}

{if $fields.POSTALCODE}
	{input name="`$prefix`postalCode"}
		{label}{t _postal_code}:{/label}
		{textfield}
	{/input}
{/if}