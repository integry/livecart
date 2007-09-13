<h1>Store Configuration</h1>

<p>
    This step allows you to configure the most important aspects of your store. <Br />More configuration options will be available after the installation is completed.
</p>

{form action="controller=install action=setConfig" method="POST" handle=$form}

    <p>
        {err for="name"}
            {{label Store name:}}
            {textfield class="text"}
        {/err}
    </p>

    <p>
        {err for="language"}
            {{label Base language:}}
            {selectfield options=$languages}
        {/err}
    </p>

    <p>
        {err for="curr"}
            {{label Base currency:}}
            {selectfield options=$currencies}
        {/err}
    </p>

    <input type="submit" class="submit" value="Complete installation" />

{/form} 