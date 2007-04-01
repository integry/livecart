<div id="header" style="background-color: #EEEEEE;"> 

	{block LANGUAGE}
		
	<span style="font-size: 30px;">LiveCart Demo Store</span>

	<fieldset class="container">
		<form action="" class="quickSearch">
		    <input type="text" class="text searchQuery" name="search" value="search" />
		    in
		    <select name="category">
		        <option value="">Our Online Store!</option>
		        <option value="6">test</option>
		    </select>
		    <input type="submit" class="submit" value="Go!">
		</form>
	
		<div style="float: right;">
			<a href="{link controller=order returnPath=true}">{t Shopping Cart}</a> | 
			<a href="{link controller=checkout returnPath=true}">{t Checkout}</a>
		</div>
	
	</fieldset>
		
</div>

{block BREADCRUMB}