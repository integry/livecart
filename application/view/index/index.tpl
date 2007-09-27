<div class="index">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	
	<div style="font-size: 90%; width: 600px; margin-left: auto; margin-right: auto; border: 1px solid yellow; padding: 8px; background-color: #FFFCDA; margin-top: 25px;">
        <p style="margin-top: 0px;">
        Welcome to the LiveCart demo store! 
        </p>
        
        <p>
        <a href="http://livecart.com">LiveCart</a> is a <strong>new shopping cart software</strong> and is currently in a beta testing phase. The software cannot be purchased just yet, while we're still working on it. However, in the meanwhile you're welcome to test it out and if you think it might well suit your next project - the launch is only a couple of weeks away! 
        </p>
               
        <a href="http://blog.livecart.com">Read more about LiveCart</a>
                
	</div>

	{include file="category/subcategoriesColumns.tpl"}

	{if $news}
        <h2>{t _latest_news}</h2>
        <ul class="news">
        {foreach from=$news item=newsItem name="news"}
    		{if !$smarty.foreach.news.last || !$isNewsArchive}
                <li class="newsEntry">
                    {include file="news/newsEntry.tpl" entry=$newsItem}
                </li>
            {else}
                <div class="newsArchive">
                    <a href="{link controller=news}">{t _news_archive}</a>
                </div>
    		{/if}
    	{/foreach}
    	</ul>
	{/if}
    
</div>		

{include file="layout/frontend/footer.tpl"}

</div>