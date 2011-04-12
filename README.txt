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

<a class='iframe janrain_signin_link' href='https://{vb:raw vboptions.janrain_capture_captureaddr}/oauth/signin?response_type=code&redirect_uri={vb:raw vboptions.bburl}/&client_id={vb:raw vboptions.janrain_capture_clientid}&xd_receiver={vb:raw vboptions.bburl}/packages/janrain_capture/xdcomm.html'>Register / Sign In</a>