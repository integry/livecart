<div style="float: left;">
<div class="menuContainer" id="orderNoteMenu_{$order.ID}">

    <ul class="menu orderNoteMenu" style="margin: 0; {denied role='order.update'}display: none;{/denied}">    	
    	<li><a href="#addResponse" class="addResponse" >{t _add_response}</a></li>
    </ul>
    
    <div class="clear"></div>
    
    <div class="slideForm addResponse" style="display: none;">
        <fieldset>
        
            <legend>{t _add_response}</legend>
        
            {form action="controller=backend.orderNote action=add id=`$order.ID`" method="POST" handle=$form onsubmit="Backend.OrderNote.submitForm(event);" role="order.update"}
            
                <p>
                    {textarea name="comment"}
                </p>        
        
                <fieldset class="controls">
                    <span class="progressIndicator" style="display: none;"></span>
                    <input type="submit" class="submit" value="{tn Add Response}" />
                    {t _or} <a class="cancel responseCancel" href="#">{t _cancel}</a>
                </fieldset>
        
            {/form}
        
        </fieldset>
    </div>

</div>

<div class="clear"></div>

<fieldset class="container orderNoteContainer">
    <ul class="notes">
    {foreach from=$notes item=note}
        {include file="backend/orderNote/view.tpl"}
    {/foreach}
    </ul>
</fieldset>

<script type="text/javascript">
    Backend.OrderNote.init($('orderNoteMenu_{$order.ID}'));
</script>

<div class="clear"></div>

</div>