{include file="header.tpl"}

<h2>Register Account</h2>
Fill the form available below to open an account with Midollar. Please make sure you provide us with your real information. If you have questions about this process please contact support.  Program participation is available to any individual, who is over 18 years of age.<br>
<br><br>

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
    <li>Please enter your full name!
   {/if}
   {if $errors[e] eq 'address'}
    <li>Please enter your address!
   {/if}
   {if $errors[e] eq 'city'}
    <li>Please enter your city!
   {/if}
   {if $errors[e] eq 'state'}
    <li>Please enter your state!
   {/if}
   {if $errors[e] eq 'zip'}
    <li>Please enter your zip!
   {/if}
   {if $errors[e] eq 'country'}
    <li>Please choose your country!
   {/if}
   {if $errors[e] eq 'username'}
    <li>Please enter your username!
   {/if}
   {if $errors[e] eq 'username_exists'}
    <li>Sorry, such user already exists! Please try another username. 
   {/if}
   {if $errors[e] eq 'email_exists'}
    <li>Sorry, such email already exists! Please try another email. 
   {/if} 
   {if $errors[e] eq 'password'} 
    <li>Please enter a password!
   {/if}
   {if $errors[e] eq 'password_confirm'}
    <li>Please check your password!
   {/if}
   {if $errors[e] eq 'password_too_small'}
    <li>The password you provided is too small, please enter at least {$settings.min_user_password_length} characters!
   {/if} 
   {if $errors[e] eq 'transaction_code'} 
    <li>Please enter the Transaction Code!
   {/if} 
   {if $errors[e] eq 'transaction_code_confirm'} 
    <li>Please check your Transaction Code!
   {/if}
   {if $errors[e] eq 'transaction_code_too_small'}
    <li>The Transaction Code you provided is too small, please enter at least {$settings.min_user_password_length} characters!
   {/if}
   {if $errors[e] eq 'transaction_code_vs_password'} 
    <li>The Transaction Code should differ from the Password!
   {/if}
   {if $errors[e] eq 'egold'} 
    <li>Please enter your e-gold account number!
   {/if}
   {if $errors[e] eq 'email'} 
    <li>Please enter your e-mail!
   {/if}
   {if $errors[e] eq 'agree'}
    <li>You have to agree with the Terms and Conditions!
   {/if}
   {if $errors[e] eq 'turing_image'}
    <li>Enter the verification code as it is shown in the corresponding box.
   {/if} 
   {if $errors[e] eq 'no_upline'}
    <li>The system requires an upline to register. {if $settings.get_rand_ref}You have to be agreed to random one or found a referral link in the net.{/if}
   {/if} 
   {if $errors[e] eq 'ip_exists_in_database'}
    <li>Your IP already exists in our database. Sorry, but registration impossible.
   {/if}

   <br> 
  {/section}
  </ul>
 {/if} 


<form name="regform" onsubmit="return checkform()" method="post">
<input type="hidden" value="signup" name="a">
<input type="hidden" value="signup" name="action">






<b>General Details</b>

        <!--  / TEXT BOX \ -->
        <div class="textBox">

<table width="100%" border="0">
  <tbody><tr>
    
 <td width="200" valign="middle" height="30" align="right">Full Name<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="45" class="inpts" value="{$frm.fullname|escape:"quotes"}" name="fullname"></td>
  </tr>
</tbody></table>


<table width="100%" border="0">
  <tbody><tr>

 <td valign="middle" height="30" align="right">E-mail Address<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="20" class="inpts" value="{$frm.email|escape:"quotes"}" name="email"></td>

 <td valign="middle" height="30" align="right">Retype E-mail<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="20" class="inpts" value="{$frm.email1|escape:"quotes"}" name="email1"></td>
  </tr>
  <tr>

 <td valign="middle" height="30" align="right">Secret question:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="20" class="inpts" value="{$frm.sq|escape:"quotes"}" name="sq"></td>

 <td valign="middle" height="30" align="right">Secret answer:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="20" class="inpts" value="{$frm.sa|escape:"quotes"}" name="sa"></td>
  </tr>
</tbody></table>



        </div>
        <!--  \ TEXT BOX / -->


<br><br>
<b>Login Info</b>

        <!--  / TEXT BOX \ -->
        <div class="textBox">

<table width="100%" border="0">
  <tbody><tr>
    <td> </td><td width="200" valign="middle" height="30" align="right">Username<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="text" style="height:20px;" size="45" class="inpts" value="{$frm.username|escape:"quotes"}" name="username"></td>
  </tr>
</tbody></table>
<table width="100%" border="0">
  <tbody><tr>

 <td valign="middle" height="30" align="right">Password<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="password" style="height:20px;" size="20" class="inpts" value="{$frm.password|escape:"quotes"}" name="password"></td>

 <td valign="middle" height="30" align="right">Retype Password<font color="#FF0000">*</font>:&nbsp;&nbsp;</td>
 <td valign="middle"><input type="password" style="height:20px;" size="20" class="inpts" value="{$frm.password2|escape:"quotes"}" name="password2"></td>
  </tr>


</tbody></table>


        </div>
        <!--  \ TEXT BOX / -->


<br><br>
<b>E-Currency Accounts</b>

<!--  / TEXT BOX \ -->
<div class="textBox">

<table width="100%" border="0">
<tbody>
{if $settings.def_payee_account_perfectmoney}
<tr>
<td>Your Perfect Money Account:</td>
<td><input type=text class=inpts size=30 name=perfectmoney value="{$frm.perfectmoney|escape:"quotes"}"></td>
</tr>
{/if}
</tbody>
</table>
<font color="#FF0000">*</font>
<font size="1" color="#0000FF">For Midollar,Please putt USD account. (Starts with U)</font>
</div>
<!--  \ TEXT BOX / -->


<br><br>
<b>Members Agreement</b>
        <!--  / TEXT BOX \ -->
        <div class="textBox">
          <table width="100%" border="0">
  
    <tbody><tr><td></td></tr><tr>
 <td width="200">Your Upline:</td>
  <td>{$referer.name} ({$referer.username})</td>
</tr>

  
</tbody></table>



<table width="100%" border="0">

<tbody><tr>
 <td colspan="2"><input type=checkbox name=agree value=1 {if $frm.agree}checked{/if} > I agree with <a href="?a=rules">Terms and conditions</a></td>
</tr>
<tr>
 <td align="right" class="menutxt"></td>
 <td></td>
</tr>
<tr>
 <td>&nbsp;</td>
  <input type="hidden" name="signup_token" value="{$token}">
 <td><input type="submit" style="float: left" class="sbmt" value="Register"></td>
</tr></tbody></table>


        </div></form>
{/if}
{include file="footer.tpl"}
