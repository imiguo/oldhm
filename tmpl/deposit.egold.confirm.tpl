{include file="header.tpl"}

{if $false_data != 1}
<h3>Please confirm your deposit:</h3><br><br>

<form name=spend method=post action="https://www.e-gold.com/sci_asp/payments.asp">
Your e-gold account number <b>{$account}</b><br>
Amount ({$currency_sign}): <b>{$amount_format}</b><br>
{if $use_compound}
{if $compound_min_percents == $compound_max_percents && !$compound_percents}
<input type=hidden name=compound value="{$compound_min_percents}">
{else}
<table cellspacing=0 cellpadding=2 border=0>
<tr><td nowrap width=1%>Compounding percent: </td>
  {if $compound_percents}
<td><select name='compound' class=inpts>
{section name=p loop=$compound_percents}<option value="{$compound_percents[p].percent}">{$compound_percents[p].percent}%</option>{/section}
</select></td>
  {else}
<td width=99%><input type=text name='compound' value="{$compound_min_percents}" class=inpts size=5></td></tr>
<tr><td nowrap colspan=2>(You can set any percent between <b>{$compound_min_percents}%</b> and <b>{$compound_max_percents}%</b>)</td>
  {/if}
</tr>
<!--tr><td colspan=2><small>Example: {$compounding}% of your earning will be accumulate on deposit.</small></td></tr-->
</table>
{/if}
{else}
<input type=hidden name=compound value="0">
{/if}

<br>

<input type=hidden name=id value={$pid}>
{if 1==2}
<input type=hidden name=userid value="{$userinfo.id}">
<input type=hidden name=hyipid value={$h_id}>
<input type=hidden name=a value=checkpayment>
{/if}
<INPUT type=hidden name=PAYMENT_AMOUNT value="{$amount}">
<INPUT type=hidden name=PAYEE_ACCOUNT value="{$settings.def_payee_account}" >
<INPUT type=hidden name=FORCED_PAYER_ACCOUNT value="{$account}">
<INPUT type=hidden name=PAYEE_NAME value="{$settings.def_payee_name}">
<INPUT type=hidden name=PAYMENT_UNITS value="{$settings.egold_paymentunits}"> 
<INPUT type=hidden name=PAYMENT_METAL_ID value="1">
<INPUT type=hidden name=STATUS_URL value="{$settings.site_url}/egold_processing.php">
<INPUT type=hidden name=PAYMENT_URL value="{$settings.site_url}/index.php?a=return_egold&process=yes">
<INPUT type=hidden name=PAYMENT_URL_METHOD value=POST>
<INPUT type=hidden name=NOPAYMENT_URL  value="{$settings.site_url}/index.php?a=return_egold&process=no">
<INPUT type=hidden name=NOPAYMENT_URL_METHOD value=POST>
<INPUT type=hidden name=BAGGAGE_FIELDS value="id {if $use_compound}compound{/if}">
{if 1==2}<INPUT type=hidden name=BAGGAGE_FIELDS value="userid hyipid a {if $use_compound}compound{/if}">{/if}
<INPUT type=hidden value="{if $settings.egold_withdraw_string}$settings.egold_withdraw_string{else}Spend from {$userinfo.username}{/if}" name=SUGGESTED_MEMO>
<br><input type=submit value="Process" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="document.location='?a=deposit'">
</form>
{/if}
{include file="footer.tpl"}