{if $files}
	<h2>{t _preview_files}</h2>
{/if}

{foreach $files as $file}
	{if $file.productFileGroupID && ($file.productFileGroupID != $previousFileGroupID)}
		<h3>{$file.ProductFileGroup.name}</h3>
	{/if}

	{if $file.isEmbedded}
		{include file="product/files/embed.tpl"}
	{else}
		{include file="product/files/link.tpl"}
	{/if}
{/foreach}