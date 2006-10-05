{foreach from=$layers item=item}	
	{if $showDiv}<div id='imageLayer_{$item.imgID}' class='imageLayer'>{/if}					
		<table border='0'>
			<tr valign=top>
				<td width='110' height='110'>
					<img name="image{$item.imgID}" id="image{$item.imgID}" src="{$item.imgSource}?asdfsadfsda" border=1>
				</td>
				<td>
					<b>Description:</b>
					<span id='spanTitle{$item.imgID}'>{$item.imgDesc}</span><br>					
					<span id='span{$item.imgID}'>							
						<a href='javascript: productImages.editPicture({$id}, {$item.imgID})'>Change picture</a>
						<br>										
						<a href='javascript: productImages.editTitle({$item.imgID})'>Edit title</a>
						<br>					
						<a href='javascript: productImages.deleteImage({$item.imgID});'>Remove</a>
					</span>					
				</td>
			</tr>	
		</table>					
	{if $showDiv}</div>{/if}
{/foreach}