{includeCss file="backend/DatabaseImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/DatabaseImport.js"}

{pageTitle}{t _import_database}{/pageTitle}

{include file="layout/backend/header.tpl"}

<p>{t _import_description}</p>

<p class="importWarning">{t _import_warning}</p>

<div id="import">
{form action="controller=backend.databaseImport action=import" method="POST" handle=$form onsubmit="new Backend.DatabaseImport(this); return false;"}

    <fieldset>
        <legend>{t _begin_import}</legend>

        <p class="required">
            {err for="cart"}
                {{label {t _shopping_cart} }}
                {selectfield options=$carts}
            {/err}
        </p>

        <p class="required">
            {err for="dbType"}
                {{label {t _database_type} }}
                {selectfield options=$dbTypes}
            {/err}
        </p>

        <p class="required">
            {err for="dbServer"}
                {{label {t _database_server} }}
                {textfield}
            {/err}
        </p>

        <p class="required">
            {err for="dbName"}
                {{label {t _database_name} }}
                {textfield}
            {/err}
        </p>

        <p class="required">
            {err for="dbUser"}
                {{label {t _database_user} }}
                {textfield}
            {/err}
        </p>

        <p>
            {err for="dbPass"}
                {{label {t _database_pass} }}
                {textfield type="password"}
            {/err}
        </p>

        <p>
            {err for="filePath"}
                {{label {t _file_path} }}
                {textfield}
            {/err}
        </p>

    </fieldset>
        
    <fieldset class="controls">        
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="submit" value="{tn _import}" />
        {t _or}
        <a class="cancel" href="#">{t _cancel}</a>
    </fieldset>

{/form}
</div>

<div id="importProgress" style="display: none;">

    <div id="completeMessage" class="yellowMessage stick" style="display: none;">
        <div>{t _import_completed}</div>
    </div>

    <fieldset>
        <legend>Importing</legend>
        <ul>
            {foreach from=$recordTypes item=type}
                <li id="progress_{$type}" style="display: none;">
                    <h2>{translate text=$type}</h2>
                    <div class="progressBarIndicator"></div>
                    <div class="progressBar" style="display: none;">
                        <span class="progressCount"></span>
                        <span class="progressSeparator"> / </span>
                        <span class="progressTotal"></span>
                    </div>
                </li>
            {/foreach}
        </ul>
    </fieldset>
</div>

{include file="layout/backend/footer.tpl"}