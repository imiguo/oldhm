{include file="header.tpl"}

{if $false_data != 1}
    <h3>Please confirm your deposit:</h3>
    <br>
    <form action="//payeer.com/api/merchant/m.php" method="GET">

        <!-- Your Payeer account <b>{$userinfo.payeer_account}</b><br> -->
        Amount ($US): <b>{$amount_format}</b><br>

        {if $use_compound}
            {if $compound_min_percents == $compound_max_percents && !$compound_percents}
                <input type=hidden name=compound value="{$compound_min_percents}">
            {else}
                <table cellspacing=0 cellpadding=2 border=0>
                    <tr>
                        <td nowrap width=1%>Compounding percent:</td>
                        {if $compound_percents}
                        <td><select name='compound' class=inpts>
                                {section name=p loop=$compound_percents}
                                    <option value="{$compound_percents[p].percent}">{$compound_percents[p].percent}
                                    %</option>{/section}
                            </select></td>
                        {else}
                        <td width=99%><input type=text name='compound' value="{$compound_min_percents}" class=inpts size=5></td>
                    </tr>
                    <tr>
                        <td nowrap colspan=2>(You can set any percent between <b>{$compound_min_percents}%</b> and
                            <b>{$compound_max_percents}%</b>)
                        </td>
                        {/if}
                    </tr>
                </table>
            {/if}
        {else}
            <input type=hidden name=compound value="0">
        {/if}

        <input type="hidden" name="m_shop" value="{$settings.def_payee_account_payeer}">
        <input type="hidden" name="m_orderid" value="{$m_orderid}">
        <input type="hidden" name="m_amount" value="{$amount}">
        <input type="hidden" name="m_curr" value="USD">
        <input type="hidden" name="m_desc" value="{$m_desc}">
        <input type="hidden" name="m_sign" value="{$m_sign}">

        <br>
        <input type=submit name=i_submit value="Process" class=sbmt> &nbsp;
        <input type=button class=sbmt value="Cancel" onclick="document.location='?a=account'">
    </form>
{/if}
{include file="footer.tpl"}

