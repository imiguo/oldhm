{include file="header.tpl"}

<h2>Welcome to Members Area!</h2>
You can deposit funds to your account as many times as you wish and use any payment system available. Our attitude towards spam is zero-tolerance. Any members caught spamming will permanently lose memberships.<br><br><br><br>


{if $settings.use_transaction_code ==1 && $userinfo.transaction_code == ''} <b>Note: currently you have not specified 
a Transaction code. The Transaction code strengthens your funds security in our 
system. The code is required to withdraw funds from your account{if $settings.internal_transfer_enabled} 
and for internal transfer to another user account{/if}. Just do not change 'Transaction 
code' in your account information if you do not want to use this feature. <a href=?a=edit_account>Click 
here</a> to specify a new transaction code .</b> <br>
<br>
{/if}

<h4>Financial Summary</h4>

<table width="580" cellspacing="0" cellpadding="0" border="0" bgcolor="#FAFAFA" align="center">
<tbody><tr>
<td width="380" valign="middle" height="25" class="reg">User:</td>
 <td width="200" valign="middle" class="dots">{$userinfo.username}</td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Registration Date:</td>
 <td valign="middle" class="dots">{$userinfo.create_account_date}</td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Last Access:</td>
 <td valign="middle" class="dots">{$last_access}&nbsp;</td>
</tr>

<tr>
 <td width="580" height="15" bgcolor="#FFFFFF" colspan="2">&nbsp;</td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Account Balance:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.total}</b><br>
 <small>
 	{section name=p loop=$ps}
   		{if $ps[p].balance > 0}{$currency_sign}{$ps[p].balance} of {$ps[p].name}<br>{/if}
	{/section}
 </small>
 </td>
 </tr>

<tr>
 <td valign="middle" height="25" class="reg">Earned Total:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.earning}</b></td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Pending Withdrawal:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.withdraw_pending|default:'0:00'}</b></td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Withdrew Total:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.withdrawal}</b></td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Active Deposit:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.active_deposit}</b></td>
</tr>

<tr>
 <td height="15" bgcolor="#FFFFFF" colspan="2">&nbsp;</td>
</tr>

<tr>
 <td width="580" valign="middle" height="25" class="reg">Last Deposit:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$last_deposit}</b> &nbsp; <small>{$last_deposit_date}</small></td>
</tr>


<tr>
 <td valign="middle" height="25" class="reg">Total Deposit:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$ab_formated.deposit}</b></td>
</tr>


<tr>
 <td valign="middle" height="25" class="reg">Last Withdrawal:</td>
 <td valign="middle" class="dots">{$currency_sign}<b>{$last_withdrawal}</b> &nbsp; <small>{$last_withdrawal_date}</small></td>
</tr>

<tr>
 <td valign="middle" height="25" class="reg">Referral Link:</td>
 <td valign="middle" class="dots">{$settings.site_url}/?ref={$userinfo.username}</td>
</tr>

</tbody></table><br><br><br><br>



{section name=p loop=$ps}
  {if $ps[p].pending_col > 0}{$ps[p].pending_col} {$ps[p].name} deposit{if $ps[p].pending_col > 1}s{/if} of {$currency_sign}{$ps[p].pending_amount} total pending<br>{/if}
{/section}

<br><br>
{if $wires} {$wires} Wire Transfer(s) pending.<br>
{/if}
<br>



{include file="footer.tpl"}
