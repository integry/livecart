{if $fields.FIRSTNAME}
	{input name="`$prefix`firstName"}
		{label}{t _your_first_name}:{/label}
		{textfield}
	{/input}
{/if}

{if $fields.LASTNAME}
	{input name="`$prefix`lastName"}
		{label}{t _your_last_name}:{/label}
		{textfield}
	{/input}
{/if}

{if $fields.COMPANYNAME}
	{input name="`$prefix`companyName"}
		{label}{t _company_name}:{/label}
		{textfield}
	{/input}
{/if}