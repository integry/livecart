<h1>License Agreement</h1>

<div id="license">{$license|nl2br}</div>

<div>

    {form action="controller=install action=acceptLicense" method="POST" handle=$form}
{literal}
    <fieldset class="error">
        <p class="checkbox" id="agreeContainer" onclick="if (Event.element(event) != $('accept')) { $('accept').click(); }">
    {/literal}
            {checkbox name=accept class="checkbox"}
            <label class="checkbox">I accept the license agreement</label>
            <span class="errorText hidden"></span>
        </p>
    </fieldset>
    <div class="clear"></div>
    <input type="submit" value="Continue installation" />
    {/form}
</div>