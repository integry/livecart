<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{t Select related product}</title>
	<base href="{baseUrl}" />

	<!-- Css includes -->
    <link href="stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
    <link href="stylesheet/library/dhtmlxtree/dhtmlXTree.css" media="screen" rel="Stylesheet" type="text/css" />
    <link href="stylesheet/backend/Category.css" media="screen" rel="Stylesheet" type="text/css" />
    <link href="stylesheet/backend/Product.css" media="screen" rel="Stylesheet" type="text/css" />
    
	{literal}
	<!--[if IE]>
		<link href="stylesheet/backend/BackendIE.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	<!--[if IE 6]>
		<link href="stylesheet/backend/BackendIE6.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	<!--[if IE 7]>
		<link href="stylesheet/backend/BackendIE7.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	{/literal}

	<!-- JavaScript includes -->
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/library/scriptaculous/scriptaculous.js"></script>
    <script type="text/javascript" src="javascript/library/dhtmlxtree/dhtmlXCommon.js" ></script>
    <script type="text/javascript" src="javascript/library/dhtmlxtree/dhtmlXTree.js" ></script>
	<script type="text/javascript" src="javascript/backend/Backend.js"></script>
    <script type="text/javascript" src="javascript/backend/Category.js" ></script>
</head>
<body>
    </head>
    <body>
        <div id="catgegoryContainer">
        	<div id="categoryBrowser" class="treeBrowser">
        	</div>
        </div>
        <div id="activeCategoryPath"></div>
        
        {literal}
        <script type="text/javascript">
            Backend.Category['links'] = {};
            Backend.Category['links']['categoryRecursiveAutoloading'] = '{link controller=backend.category action=xmlRecursivePath}';
        	    
        	Backend.Category.init();    
        	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading); 
            Backend.Category.addCategories({/literal}{json array=$categoryList}{literal});
            
        	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
        	Backend.Category.initPage();
            
            Backend.Category.loadBookmarkedCategory();
        </script>
        {/literal}
        
        
        <div style="width: 98%;">
        <table class="productHead" id="products_{$categoryID}_header">
        	<tr class="headRow">
        		<th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
        		<th class="first cell_sku">
        			<span class="fieldName">Product.sku</span>
        			<input type="text" class="text" id="filter_Product.sku" name="filter_Product.sku" value="{tn SKU}" />
        		</th>
        		<th class="cell_name">
                    <span class="fieldName">Product.name</span>
            		<input type="text" class="text" id="filter_Product.name" name="filter_Product.name" value="{tn Name}" />                    
                </th>	
        		<th class="cell_manuf">
                    <span class="fieldName">Manufacturer.name</span>
            		<input type="text" class="text" id="filter_Manufacturer.name" name="filter_Manufacturer.name" value="{tn Manufacturer}" />  
                </th>	
        		<th class="cell_price">
                    <span class="fieldName">ProductPrice.price</span>Price <small>({$currency})</small>
                </th>
        		<th class="cell_stock">
                    <span class="fieldName">Product.stockCount</span>
            		<input type="text" class="text" id="filter_Product.stockCount" name="filter_Product.stockCount" value="{tn In stock}" />   
                </th>	
        		<th class="cell_enabled">
                    <span class="fieldName">Product.isEnabled</span>{tn Enabled}
                </th>	
        	</tr>
        </table>
        </div>
        
        <div style="width: 98%;">
        <table class="activeGrid productList" id="products_{$categoryID}">
        	<tbody>
        		{include file="backend/product/productList.tpl"}
        	</tbody>
        </table>
        </div>
    </body>
</html>