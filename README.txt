INSTALLATION INSTRUCTIONS

1) Copy the janrain_capture directory into /packages/
2) Import product-janrain_capture.xml from the Manage Products screen
3) Navigate to User Profile Fields -> Add New User Profile Field and add
a field with a type of Single-Line Text Box, a Name and Description of your
choosing, change:
Field Editable by User to "No",
Private Field to "Yes",
Field Searchable on Members List to "No",
and Show on Members List to "No"
Leave all other fields in their default states and click "Save". Note the value
present in the "Name" column (e.g. "field5").
4) Navigate to the Settings -> Options -> Janrain Capture Options and set
Enable Janrain Capture to Yes, Enter your Client ID, Client Secret, Capture URL,
and SSO URL. Set the Capture UUID Field to the Name value from step 3,
optionally add the field names for First Name and Last name and click
Save.
5) Place the following link snippet in one of your template files in a location
of your choosing:

<a class='iframe janrain_signin_link' href='{vb:raw bbuserinfo.janrain_capture_login}'>Register / Sign In</a>

6) vBulletin replaces page content with the output from the "STANDARD_ERROR"
template to prompt users to log in when attempting to access a page intended
for authenticated users. Since the browser URL is in fact the final destination
the user should be directed to, we can simply open up the STANDARD_ERROR
template, remove the login form entirely from the template, and add the
following code between the {vb:raw footer} and </body> lines:

<vb:if condition="$show['permission_error'] OR $show['inlinemod_form']">
<script type="text/javascript">
  jQuery(function($){
    $.fancybox({
      type: 'iframe',
      href: '{vb:raw bbuserinfo.janrain_capture_login}',
      padding: 0,
      scrolling: 'no',
      autoScale: true,
      width: 666,
      autoDimensions: false
    });
  });
</script>
</vb:if>

7) Edit the 'modifyprofile' template to disallow changing profile data locally
and include a link to edit profile data within Capture. Replace the content in
the Required Fields section that includes the option to change email and
password, beginning with this line:
<h3 class="blocksubhead">{vb:rawphrase registration_required_information}</h3>

And ends immediately before this line:
<h3 class="blocksubhead">{vb:rawphrase optional_information_will}</h3>

Replace with this content:
<vb:if condition="$vbulletin->session->vars['capture_access_token']">
<h3 class="blocksubhead">{vb:rawphrase registration_required_information}</h3>
<div class="section">

    <div class="blockrow singlebutton">
        <label>Edit Profile:</label>
        <div class="rightcol">
            <a style="display:inline-block" href="{vb:raw bbuserinfo.janrain_capture_profile}" class="iframe janrain_profile_link button">Edit Profile</a>
        </div>
        <p class="description">
            Please click this button to edit your profile data. Any changes you've made on this page will not be saved.
        </p>
    </div>

    {vb:raw customfields.required}
</div>
</vb:if>