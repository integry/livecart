{literal}
<style>  
    div.imageLayer {
    	cursor:move;
    }
	div.imageLayerOver {
	  	background-color: #fdc;
	}
</style>
{/literal}
{$imageScript}
{$form}	
<div id="sortDiv" style="visibility: hidden">
	<a href="javascript: productImages.updateSorting({$id}, Sortable.serialize('imagesLayer'));">Update images sorting</a>
</div>
<div id="imagesLayer">
	{include file="backend/product/image.tpl"}	
</div>		
{literal}
<script language="javascript">	
	
	//productImages.sort();
	Sortable.create('imagesLayer', {tag:'div', only:'imageLayer', hoverclass:'imageLayerOver',
									onUpdate:
									function(sortable) {										
										$('sortDiv').style.visibility = "visible";
									}
									});		
</script>
{/literal}
<iframe name="iframeUpload" src="{link controller="backend.product" action="saveImage"}" width="450" height="200" style="display:none"></iframe>
