<?php
/**
 * This file used to generate signup form for the subscription page
 */
function buildForm($type, $profile2) {
$build_form = '
<form method="POST" name="billing" class="paypal" >
     <h4>Billing information:</h4>
     <fieldset class="paypal_billing">
          <p class="subscription"><label for="USERTYPE">Account Type</label>
               <select class="subscription" name="SUBSCRIPTION" id="SUBSCRIPTION" >
';
if ($type == "an Independent Barber") {
     $build_form .= '
                    <option selected="selected" value="IND_MONT">$25 monthly</option> 
                    <option value="IND_SMYR">$135 every 6 months</option>
                    <option value="IND_YEAR">$255 yearly</option>
';
} else {
     $build_form .= '
          
                    <option selected="selected" value="SHOP_MONT">$50 monthly</option> 
                    <option value="SHOP_SMYR">$270 every 6 months</option>
                    <option value="SHOP_YEAR">$510 yearly</option>
';
}
$build_form .= '
               </select>
               </p>
          <p class="name"><label for="BILLTOFIRSTNAME">First Name</label>
               <input type="text" name="BILLTOFIRSTNAME" id="BILLTOFIRSTNAME" />
               <label for="BILLTOLASTNAME">Last Name</label>
               <input type="text" name="BILLTOLASTNAME" id="BILLTOLASTNAME" /></p>
          <p class="street"><label for="BILLTOSTREET">Street Address</label>
               <input type="text" name="BILLTOSTREET" id="BILLTOSTREET" /></p>
          <p class="citystatezip"><label for="BILLTOCITY">City</label>
               <input class="city" type="text" name="BILLTOCITY" id="BILLTOCITY" />
               <label for="BILLTOSTATE">State</label>
               <select class="state" name="BILLTOSTATE" id="BILLTOSTATE"> 
                    <option selected="selected" value="AL">Alabama</option> 
                    <option value="AK">Alaska</option> 
                    <option value="AZ">Arizona</option> 
                    <option value="AR">Arkansas</option> 
                    <option value="CA">California</option> 
                    <option value="CO">Colorado</option> 
                    <option value="CT">Connecticut</option> 
                    <option value="DE">Delaware</option> 
                    <option value="DC">District Of Columbia</option> 
                    <option value="FL">Florida</option> 
                    <option value="GA">Georgia</option> 
                    <option value="HI">Hawaii</option> 
                    <option value="ID">Idaho</option> 
                    <option value="IL">Illinois</option> 
                    <option value="IN">Indiana</option> 
                    <option value="IA">Iowa</option> 
                    <option value="KS">Kansas</option> 
                    <option value="KY">Kentucky</option> 
                    <option value="LA">Louisiana</option> 
                    <option value="ME">Maine</option> 
                    <option value="MD">Maryland</option> 
                    <option value="MA">Massachusetts</option> 
                    <option value="MI">Michigan</option> 
                    <option value="MN">Minnesota</option> 
                    <option value="MS">Mississippi</option> 
                    <option value="MO">Missouri</option> 
                    <option value="MT">Montana</option> 
                    <option value="NE">Nebraska</option> 
                    <option value="NV">Nevada</option> 
                    <option value="NH">New Hampshire</option> 
                    <option value="NJ">New Jersey</option> 
                    <option value="NM">New Mexico</option> 
                    <option value="NY">New York</option> 
                    <option value="NC">North Carolina</option> 
                    <option value="ND">North Dakota</option> 
                    <option value="OH">Ohio</option> 
                    <option value="OK">Oklahoma</option> 
                    <option value="OR">Oregon</option> 
                    <option value="PA">Pennsylvania</option> 
                    <option value="RI">Rhode Island</option> 
                    <option value="SC">South Carolina</option> 
                    <option value="SD">South Dakota</option> 
                    <option value="TN">Tennessee</option> 
                    <option value="TX">Texas</option> 
                    <option value="UT">Utah</option> 
                    <option value="VT">Vermont</option> 
                    <option value="VA">Virginia</option> 
                    <option value="WA">Washington</option> 
                    <option value="WV">West Virginia</option> 
                    <option value="WI">Wisconsin</option> 
                    <option value="WY">Wyoming</option>
               </select>
               <label for="BILLTOZIP">Zip</label>
               <input class="zip" type="text" name="BILLTOZIP" id="BILLTOZIP" /></p>
          <p><label for="BILLTOCOUNTRY">Country</label>
               <input type="text" name="BILLTOCOUNTRY" id="BILLTOCOUNTRY" value="US" /></p>
          <input type="submit" id="submit" name="BILL_SUBMIT" />
     </fieldset>
</form>';
return $build_form;
}