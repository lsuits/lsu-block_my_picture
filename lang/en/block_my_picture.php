<?php

// Author: Adam Zapletal

$string['my_picture:addinstance'] = 'Add a new my picture block';
$string['my_picture:myaddinstance'] = 'Add a new my picture block';

// Strings for block content
$string['pluginname'] = 'My Profile Picture';
$string['reprocess'] = 'Reprocess';

// Strings for cron
$string['start'] = 'Starting profile picture updates...';
$string['fetched'] = 'Fetching {$a} users...';
$string['completed'] = 'Completed {$a}...';
$string['finish'] = 'Finished {$a} profile picture update(s)';
$string['num_success'] = 'Successful updates';
$string['num_nopic'] = 'Missing pictures';
$string['num_badid'] = 'Invalid idnumbers';
$string['num_error'] = 'Errors';
$string['elapsed'] = 'Elapsed time: {$a} seconds';
$string['misconfigured_message'] = 'The My Profile Picture block failed to contact the photos webservice. Please check the settings and verify the webservice is operating normally at the address {$a}';
$string['misconfigured_subject'] = "MyProfilePicture ERROR";
$string["cron_webservice_err"]   = "\n!! Webservice communication error.\nIt is possible that the URLs are misconfigured in the Admin settings area.";

// Strings for reprocess.php
$string['reprocess_title'] = 'Reprocess My Picture';
$string['badid_user'] = 'Please contact elearning@lsu.edu to have your idnumber corrected in Moodle';
$string['nopic_user'] = 'Please visit the Tiger Card Office to update your Moodle picture';
$string['error_user'] = 'An error has occurred';
$string['success_user'] = 'Your profile picture was successfully updated';

// Strings for fetch_missing.php and reprocess_all.php
$string['reprocess_all_title'] = 'Reprocess All Profile Pictures';
$string['fetch_missing_title'] = 'Fetch Missing Profile Pictures';
$string['fetching_start'] = 'Fetching missing pictures...';
$string['all_start'] = 'Reprocessing all pictures...';
$string['error_admin'] = 'Could not create picture on this server';
$string['bad_id_admin'] = 'Invalid idnumber';
$string['nopic_admin'] = 'Not found';
$string['success'] = 'Success';
$string['no_missing_pictures'] = 'There were no missing profile pictures in the system';

// Strings for settings.php
$string['fetch'] = 'Fectch missing on cron';
$string['fetch_desc'] = 'At every cron interval, _My Profile Picture_ will fetch missing photos.';
$string['cron_users'] = 'Cron Users';
$string['cron_users_desc'] = 'Number of users to process per cron run';
$string['webservice_url'] = 'myPicture WebService URL';
$string['ready_url'] = 'myPicture Ready URL';
$string['update_url'] = 'myPicture Update URL';
$string['url'] = 'URL';
$string['reprocess_all'] = 'Reprocess all profile pictures';
$string['fetch_missing'] = 'Fetch all missing profile pictures';

// Reprocess Help
$string['pluginname_help'] = '
Reprocess Your Profile Picture

Pressing Reprocess under the My Profile Picture block requests your latest photo from the Tiger Card office.

You can update your Tiger Card photo by going to <a href="https://photos.tigercard.lsu.edu">https://photos.tigercard.lsu.edu</a> and submitting your own photo that adhere\'s to the Tiger Card Photo Requirements listed below

1. Open a browser and go to the photos area of the Tiger Card Website: <a href="https://photos.tigercard.lsu.edu">https://photos.tigercard.lsu.edu</a>

2. Here you will enter your PAWS ID and Password to login.

3. Then you will be able to upload a photo to their server. This will need to be a "head shot" similar to the current photo.

4. You will get an initial email when the photo is received.

5. Tiger Card will do what is necessary to get the photo approved and add the photo to their server by running a server update. You will get a second email letting you know whether or not the photo is approved; however, you still need to allow additional time for the server update to run. If the photo is rejected, you will also be notified.

6. The job that updates the Tiger Card server with new images runs twice a day (1 am and 2 pm, Monday-Friday), and the update takes about 30 minutes to run. So, by 1:30 am or 2:30 pm the update should be finished running each day. So even though you may get a message saying that your image is approved, it may not be immediately available to reprocess in Moodle. You will need to wait for the next update time to pass.

7. Once the photo has been updated, you log into Moodle.

8. On your My Courses page, Turn Editing On (upper right) and look for the Add Blocks block to appear. Use this block to add the My Profile Picture block to the page.

9. Click on the Reprocess link in this block and this will replace the former picture with your new picture.

10. To see your image, clear your browser\'s cache and refresh the Moodle page.
';
