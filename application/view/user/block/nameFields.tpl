{if $fields.FIRSTNAME}
	[[ textfld('`$prefix`firstName', '_your_first_name') ]]
{/if}

{if $fields.LASTNAME}
	[[ textfld('`$prefix`lastName', '_your_last_name') ]]
{/if}

{if $fields.COMPANYNAME}
	[[ textfld('`$prefix`companyName', '_company_name') ]]
{/if}