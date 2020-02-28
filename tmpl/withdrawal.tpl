{include file="header.tpl"}
{literal}


<script Language='JavaScript'>
function formsubmit() {
Today = new Date();
var NowHour = Today.getHours();
var NowMinute = Today.getMinutes();
var NowSecond = Today.getSeconds();
var mysec = (NowHour*3600)+(NowMinute*60)+NowSecond;
if((mysec-document.formsubmitf.mypretime.value)>120)
//600只是一个时间值，就是5分钟内禁止重复提交，值随你高兴设
{
document.formsubmitf.mypretime.value=mysec;
}
else
{
alert('Please waiting');
return false;
}
document.forms.formsubmitf.submit();
}
</script>



{/literal}

<h3>Ask for withdrawal:</h3><br>

{if $fatal}

{if $fatal == 'one_per_month'}
You can withdraw once per month only.
{/if}

{if $fatal == 'withdrawal_percent_reached'}
Withdrawl percent has been reached. We have stopped all withdrawals.
{/if}

{else}

{if $say eq 'on_hold'}
Sorry, this amount on hold now.<br><br>
{/if}
{if $say eq 'zero'}
Sorry, you can't request a withdraw smaller than $0.00 only<br><br>
{/if}
{if $say eq 'less_min'}
Sorry, you can request not less than ${$settings.min_withdrawal_amount}<br><br>
{/if}
{if $say eq 'greater_max'}
Sorry, you can request not greater than ${$settings.max_withdrawal_amount}<br><br>
{/if}
{if $say eq 'daily_limit'}
Sorry, you have exceeded a daily limit<br><br>
{/if}
{if $say eq 'processed'}
{if $batch eq ''}
Withdrawal request saved.<br><br>
{else} Withdrawal processed, batch_id: {$batch}<br>
<br>
{/if}
{/if}
{if $say eq 'not_enought'}
Sorry, you have requested the amount larger than the one on your balance.<br><br>
{/if}
{if $say eq 'invalid_transaction_code'}
You have entered the invalid transaction code.<br><br>
{/if}
{if $say eq 'no_deposits'}
You have not done any deposits yet. Withdrawal function will be available after a deposit.
<br><br>
{/if}
{if $say eq 'no_active_deposits'}
You must have active deposit to withdraw.
{/if}
{if $preview}

<form name=formsubmitf id ="the" method="post">
<input type=hidden name='mypretime' value='0'> 
<input type=hidden name=a value=withdraw>
<input type=hidden name=action value=withdraw>
<input type=hidden name=amount value={$amount}>
<input type=hidden name=ec value={$ec}>
<input type=hidden name=comment value="{$comment|escape:html}">

<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td colspan=2>You are withdrawing <b>${$amount}</b> of {$currency}. 
{if $settings.withdrawal_fee > 0 || $settings.withdrawal_fee_min > 0} Our fee is 
{if $settings.withdrawal_fee > 0}<b>{$settings.withdrawal_fee}%</b>{/if}
{if $settings.withdrawal_fee > 0 && $settings.withdrawal_fee_min > 0} or {/if}
{if $settings.withdrawal_fee_min > 0} <b>${$settings.withdrawal_fee_min}</b>{if $settings.withdrawal_fee > 0} as a minimum{/if}{/if}
.
{else}
We have no fee for this operation. 
{/if}
</td>
</tr>
<tr>
 <td colspan=2>Actually you will acquire the total of <b>${$to_withdraw}</b> on your {$currency} account {if $account}{$account}{/if}.</td>
</tr>
{if $comment}
<tr>
 <td colspan=2>Your comments: {$comment|escape:html}</td>
</tr>
{/if}
{if $settings.use_transaction_code && $userinfo.transaction_code}
<tr>
 <td colspna=2>&nbsp;</td>
</tr>
<tr>
 <td width=1% nowrap>Transaction Code:</td>
 <td width=99%><input type="password" name="transaction_code" class=inpts size=15></td>
</tr>
{/if}
<tr>
 <td><br><input type=button value="Confirm" class=sbmt name="button1"  onclick='formsubmit()' ></td>
</tr></table>
</form>


{else}


<form method=post>
<input type=hidden name=a value=withdraw>
<input type=hidden name=action value=preview>

<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account Balance:</td>
 <td>$<b>{$ab_formated.total}</b></td>
</tr>
<tr><td>&nbsp;</td>
 <td> <small>
{section name=p loop=$ps}
   {if $ps[p].balance > 0}${$ps[p].balance} of {$ps[p].name}{if $hold[p].amount > 0} / ${$hold[p].amount} on hold{/if}<br>{/if}
{/section}
 </td>
</tr>
<tr>
 <td>Pending Withdrawals: </td>
 <td>$<b>{if $ab_formated.withdraw_pending != 0}{$ab_formated.withdraw_pending}{else}0.00{/if}</b></td>
</tr>

{section name=ps loop=$pay_accounts}
<tr>
 <td>Your {$pay_accounts[ps].name} Account:</td>
 <td>{if $pay_accounts[ps].account != ''}{$pay_accounts[ps].account}{else}<a href=?a=edit_account><i>not set</i></a>{/if}</td>
</tr>
{/section}
</table>

{if $other_processings}
For other eCurrencies please provide your <br> Payee Account details in the comments field below.
{/if}

{if $ps}
<table cellspacing=0 cellpadding=2 border=0 width=200>
<tr>
 <td colspan=2>&nbsp;</td>
</tr>
<tr>
 <td>Select eCurrency:</td>
 <td><select name=ec class=inpts>
{section name=p loop=$ps}
  {if $ps[p].balance > 0}
  <option value={$ps[p].id}>{$ps[p].name}</option>
  {/if}
{/section}
     </select>
 </td>
</tr>
<tr>
 <td>Withdrawal (US$):</td>
 <td><input type=text name=amount value="10.00" class=inpts size=15></td>
</tr>
<tr>
 <td colspan=2><textarea name=comment class=inpts cols=45 rows=4>Your comment</textarea>
</tr>
<tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Request" class=sbmt ></td>
</tr>
</table>
{else}
<br><br>
You have no funds to withdraw.
{/if}
</form>

{/if}

{/if}
{include file="footer.tpl"}
