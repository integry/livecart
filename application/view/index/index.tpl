{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	
	<div style="font-size: 90%; width: 600px; margin-left: auto; margin-right: auto; border: 1px solid yellow; padding: 5px; background-color: #FFFCDA; margin-top: 25px;">
		Welcome to the LiveCart demo store! LiveCart is a new shopping cart software and is currently in a beta testing phase. The software cannot be purchased just yet while we're still working on it. However, in the meanwhile you're welcome to test it out and if you think it might well suit your next project - the launch is only a couple of weeks away! <a href="http://blog.livecart.com">Read more about LiveCart</a>.
	</div>
	
	{include file="category/subcategoriesColumns.tpl"}
</div>		

{include file="layout/frontend/footer.tpl"}