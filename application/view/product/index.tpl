{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
    <h1>{$product.name_lang}</h1>
    
    <div id="productDescription">
        {$product.longDescription_lang}    
    </div>

    <div id="productSpecification">
        <table>
            {foreach from=$product.attributes item="attr"}
                <tr>
                    <td>{$attr.SpecField.name_lang}</td>
                    <td>
                        {if $attr.values}
                            <ul>
                                {foreach from=$attr.values item="value"}
                                    <li> {$value.value_lang}</li>
                                {/foreach}
                            </ul>
                        {elseif $attr.value_lang}
                            {$attr.value_lang}        
                        {elseif $attr.value}
                            {$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
                        {/if}
                    </td>
                </tr>                            
            {/foreach}
        </table>
    </div>
    
</div>

{include file="layout/frontend/footer.tpl"}