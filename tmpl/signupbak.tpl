{include file="header.tpl"}
<h3>Registration at {$settings.site_name}:</h3>
<br>

{if $deny_registration}
 We are closed for new registrations now.
{elseif $settings.use_referal_program && $settings.force_upline && !$referer && !$settings.get_rand_ref}
 You  do not have a upline. Our system require a upline for each user.

{else}
 {literal}
<script language=javascript>
 function checkform() {
  if (document.regform.fullname.value == '') {
    alert("Please enter your full name!");
    document.regform.fullname.focus();
    return false;
  }
 {/literal}
 {if $settings.use_user_location}
 {literal}
  if (document.regform.address.value == '') {
    alert("Please enter your address!");
    document.regform.address.focus();
    return false;
  }
  if (document.regform.city.value == '') {
    alert("Please enter your city!");
    document.regform.city.focus();
    return false;
  }
  if (document.regform.state.value == '') {
    alert("Please enter your state!");
    document.regform.state.focus();
    return false;
  }
  if (document.regform.zip.value == '') {
    alert("Please enter your ZIP!");
    document.regform.zip.focus();
    return false;
  }
  if (document.regform.country.options[document.regform.country.selectedIndex].text == '--SELECT--') {
    alert("Please choose your country!");
    document.regform.country.focus();
    return false;
  }
 {/literal}
 {/if}
 {literal}
  if (document.regform.username.value == '') {
    alert("Please enter your username!");
    document.regform.username.focus();
    return false;
  }
  if (document.regform.password.value == '') {
    alert("Please enter your password!");
    document.regform.password.focus();
    return false;
  }
  if (document.regform.password.value != document.regform.password2.value) {
    alert("Please check your password!");
    document.regform.password2.focus();
    return false;
  }
 {/literal}
 {if $settings.use_transaction_code}
 {literal}
  if (document.regform.transaction_code.value == '') {
    alert("Please enter your transaction code!");
    document.regform.transaction_code.focus();
    return false;
  }
  if (document.regform.transaction_code.value != document.regform.transaction_code2.value) {
    alert("Please check your transaction code!");
    document.regform.transaction_code2.focus();
    return false;
  }
 {/literal}
 {/if}
 {literal}
  if (document.regform.email.value == '') {
    alert("Please enter your e-mail address!");
    document.regform.email.focus();
    return false;
  }
  if (document.regform.email.value != document.regform.email1.value) {
    alert("Please retupe your e-mail!");
    document.regform.email.focus();
    return false;
  }
  if (document.regform.agree.checked == false) {
    alert("You have to agree with the Terms and Conditions!");
    return false;
  }
  return true;
 }

 function IsNumeric(sText) {
  var ValidChars = "0123456789";
  var IsNumber=true;
  var Char;
  if (sText == '') return false;
  for (i = 0; i < sText.length && IsNumber == true; i++) { 
    Char = sText.charAt(i); 
    if (ValidChars.indexOf(Char) == -1) {
      IsNumber = false;
    }
  }
  return IsNumber;
 }
 </script>
{/literal}
 
 {if $errors}
<ul style="color:red">
  {section name=e loop=$errors} 
   {if $errors[e] eq 'full_name'}
  <li>Please enter your full name!</li>
  {/if}
   {if $errors[e] eq 'address'}
  <li>Please enter your address!</li>
  {/if}
   {if $errors[e] eq 'city'}
  <li>Please enter your city!</li>
  {/if}
   {if $errors[e] eq 'state'}
  <li>Please enter your state!</li>
  {/if}
   {if $errors[e] eq 'zip'}
  <li>Please enter your zip!</li>
  {/if}
  <li>
    {if $errors[e] eq 'country'}
              Please choose your country!
  </li>
  {/if}
  <li>
    {if $errors[e] eq 'username'}
                Please enter your username!
  </li>
  {/if}
   {if $errors[e] eq 'username_exists'}
  <li>
    Sorry, such user already exists! Please try another username.
  </li>
  {/if}
  <li>
    {if $errors[e] eq 'email_exists'}
                    Sorry, such email already exists! Please try another email.
  </li>
  {/if} 
   {if $errors[e] eq 'password'}
  <li>Please enter a password!</li>
  {/if}
   {if $errors[e] eq 'password_confirm'}
  <li>Please check your password!</li>
  {/if}
   {if $errors[e] eq 'password_too_small'}
  <li>
    The password you provided is too small, please enter at least {$settings.min_user_password_length} characters!
  </li>
  {/if} 
   {if $errors[e] eq 'transaction_code'}
  <li>Please enter the Transaction Code!</li>
  {/if} 
   {if $errors[e] eq 'transaction_code_confirm'}
  <li>Please check your Transaction Code!</li>
  {/if}
   {if $errors[e] eq 'transaction_code_too_small'}
  <li>
    The Transaction Code you provided is too small, please enter at least {$settings.min_user_password_length} characters!
  </li>
  {/if}
   {if $errors[e] eq 'transaction_code_vs_password'}
  <li>The Transaction Code should differ from the Password!</li>
  {/if}
   {if $errors[e] eq 'egold'}
  <li>Please enter your e-gold account number!</li>
  {/if}
   {if $errors[e] eq 'email'}
  <li>Please enter your e-mail!</li>
  {/if}
   {if $errors[e] eq 'agree'}
  <li>You have to agree with the Terms and Conditions!</li>
  {/if}
   {if $errors[e] eq 'turing_image'}
  <li>
    Enter the verification code as it is shown in the corresponding box.
  </li>
  {/if} 
   {if $errors[e] eq 'no_upline'}
  <li>
    The system requires an upline to register. {if $settings.get_rand_ref}You have to be agreed to random one or found a referral link in the net.{/if}
  </li>
  {/if} 
   {if $errors[e] eq 'ip_exists_in_database'}
  <li>
    Your IP already exists in our database. Sorry, but registration impossible.
  </li>
  {/if}
  <br>{/section}</ul>
{/if}
<form method=post onsubmit="return checkform()" name="regform">
  <input type=hidden name=a value="signup">
  <input type=hidden name=action value="signup">
  <table cellspacing=0 cellpadding=2 border=0>
    <tr>
      <td>Your Full Name:</td>
      <td>
        <input type=text name=fullname value="{$frm.fullname|escape:"quotes"}" class=inpts size=30></td>
    </tr>
    {if $settings.use_user_location}
    <tr>
      <td>Your Address:</td>
      <td>
        <input type=text name=address value="{$frm.address|escape:"quotes"}" class=inpts size=30></td>
    </tr>
    <tr>
      <td>Your City:</td>
      <td>
        <input type=text name=city value="{$frm.city|escape:"quotes"}" class=inpts size=30></td>
    </tr>
    <tr>
      <td>Your State:</td>
      <td>
        <input type=text name=state value="{$frm.state|escape:"quotes"}" class=inpts size=30></td>
    </tr>
    <tr>
      <td>Your Zip:</td>
      <td>
        <input type=text name=zip value="{$frm.zip|escape:"quotes"}" class=inpts size=30></td>
    </tr>
    <tr>
      <td>Your Country:</td>
      <td>
        <select name='country' class=inpts>
          <option value=''>--SELECT--</option>
          {section name=c loop=$countries}
          <option {if $countries[c].name eq $frm.country}selected{/if}>{$countries[c].name|escape:"quotes"}</option>
          {/section}
        </td>
      </tr>
      {/if}
      <tr>
        <td>Your Username:</td>
        <td>
          <input type=text name=username value="{$frm.username|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Define Password:</td>
        <td>
          <input type=password name=password value="{$frm.password|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Retype Password:</td>
        <td>
          <input type=password name=password2 value="{$frm.password2|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      {if $settings.use_transaction_code}
      <tr>
        <td>Difine Transaction Code:</td>
        <td>
          <input type=password name=transaction_code value="{$frm.transaction_code|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Retype Transaction Code:</td>
        <td>
          <input type=password name=transaction_code2 value="{$frm.transaction_code2|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      {/if}



{section name=ps loop=$pay_accounts}
      <tr>
        <td>Your {$pay_accounts[ps].name} Account:</td>
        <td>
          <input type=text class=inpts size=30 name=pay_account[{$pay_accounts[ps].id}] value="{$pay_accounts[ps].account|escape:html}"></td>
      </tr>
      {/section}
      <tr>
        <td>Payza Account</td>
        <td>
          <input type=text name=city value="{$frm.city|escape:"quotes"}" class=inpts size=30></td>
      </tr>

      <tr>
        <td>Your E-mail Address:</td>
        <td>
          <input type=text name=email value="{$frm.email|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Retype Your E-mail:</td>
        <td>
          <input type=text name=email1 value="{$frm.email1|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Secret question:</td>
        <td>
          <input type=text name=sq value="{$frm.sq|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      <tr>
        <td>Secret answer:</td>
        <td>
          <input type=text name=sa value="{$frm.sa|escape:"quotes"}" class=inpts size=30></td>
      </tr>
      {if $settings.use_referal_program}
{if $referer}
      <tr>
        <td>Your Upline:</td>
        <td>{$referer.name} ({$referer.username})</td>
      </tr>
      {else}
{if $settings.force_upline}
 {if $settings.get_rand_ref}
      <tr>
        <td colspan=2>
          You do not have an upline. Our system requires an upline for each user. You'll have to agree to get a random one or find a referral link on the net.
          <input type=checkbox name="rand_ref" value=1></td>
      </tr>
      {else}
      <tr>
        <td colspan=2>
          You do not have an upline. Our system requires an upline for each user.
        </td>
      </tr>
      {/if}
{/if}
{/if}
{/if}
      <tr>
        <td colspan=2>
          <input type=checkbox name=agree value=1 {if $frm.agree}checked{/if} >
          I agree with
          <a href=?a=rules>Terms and conditions</a>
        </td>
      </tr>
      {if $userinfo.validation_enabled == 1}
      <tr>
        <td class=menutxt align=right>
          <img src="?a=show_validation_image&{$userinfo.session_name}={$userinfo.session_id}&rand={$userinfo.rand}"></td>
        <td>
          <input type=text name=validation_number class=inpts size=15></td>
      </tr>
      {/if}
      <tr>
        <td>&nbsp;</td>
        <td>
          <input type=submit value="Register" class=sbmt></td>
      </tr>
    </table>
  </form>
                                              {/if}
{include file="footer.tpl"}