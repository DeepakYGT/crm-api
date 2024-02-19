<?php require __DIR__."/../connect.php";

/******************************************************************************************/

/* Add Contact page */
if(isset($currFile) && $currFile == "add-contact.php"){

    $data = array(); 
    $data['tags']   = getTags($connect);
    $data['lists']  = getLists($connect);
    
}
/* Add Contacts page Ends here */
/*******************************************************************************************/
/* Create Contact Post Submit */

if (isset($_POST['frm_post']) && $_POST['frm_post'] == 'createContact') {
    // Get Current Date & Time
    $now = date('Y-m-d H:i:s');

    // Set Database Columns and the row data posted from front end
    $contactData = array(
        'contact_owner'     => $_POST['owner'],
        'first_name'        => $_POST['first_name'],
        'last_name'         => $_POST['last_name'],
        'mobile'            => $_POST['mobile'],
        'mobile2'           => $_POST['mobile2'],
        'mobile3'           => $_POST['mobile3'],
        'phone'             => $_POST['phone'],
        'phone2'            => $_POST['phone2'],
        'phone3'            => $_POST['phone3'],
        'personal_email'    => $_POST['personal_email'],
        'work_email'        => $_POST['work_email'],
        'alternate_email'   => $_POST['alternate_email'],
        'company_name'      => $_POST['company'],
        'address_1'         => $_POST['address1'],
        'address_2'         => $_POST['address2'],
        'city'              => $_POST['city'],
        'state'             => $_POST['state'],
        'country'           => $_POST['country'],
        'zip'               => $_POST['zipcode'],
        'website'           => $_POST['website'],
        'website2'          => $_POST['website2'],
        'website3'          => $_POST['website3'],
        'job_title'         => $_POST['job_title'],
        'twitter'           => $_POST['twitter'],
        'instagram'         => $_POST['instagram'],
        'linkedin'          => $_POST['linkedin'],
        'facebook'          => $_POST['facebook'],
        'telegram'          => $_POST['telegram'],
        'skype'             => $_POST['skype'],
        'comments'          => $_POST['description'],
        'status'            => 1,
        'created_at'        => $now,
        'modified_at'       => $now    
    );

    $sql_getEmail = "SELECT contact_id, personal_email FROM contact_master";
    $getEmail_key = "contact_id"; $getEmail_value = "personal_email";
    // Get data from database for contacts and accounts
    $emailArr   = getContactAccountData($connect, $sql_getEmail, $getEmail_key, $getEmail_value);

    $matched_contact_id = array_search($_POST['personal_email'], $emailArr);

    // Insert data into contact_master table
    $contactId = createContacts($contactData, $connect);

    if ($contactId) {
        // Handle tags
        if (isset($_POST['organizerMultipleTags'])) {
            $tags = $_POST['organizerMultipleTags'];
            insertTags($tags, $contactId, $connect);
        }

        // Handle lists
        if (isset($_POST['organizerMultipleLists'])) {
            $lists = $_POST['organizerMultipleLists'];
            insertLists($lists, $contactId, $connect);
        }

        $message  = "SUCCESS";
        header("Location: ../contacts.php?msg=".base64_encode($message));
    } else {
       $message  = "ERROR";
       header("Location: ../contacts.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }
}

/* Create Contact Ends Here */

/******************************************************************************************/

/* Update Contact Post Submit */

if (isset($_POST['frm_cdPost']) && $_POST['frm_cdPost'] == 'editContact') {
    // Get Current Date & Time
    $now = date('Y-m-d H:i:s');



    // Set Database Columns and the row data posted from front end
    $contactData = array(
        'contact_owner'     => $_POST['owner'],
        'first_name'        => $_POST['first_name'],
        'last_name'         => $_POST['last_name'],
        'mobile'            => $_POST['mobile'],
        'mobile2'           => $_POST['mobile2'],
        'mobile3'           => $_POST['mobile3'],
        'phone'             => $_POST['phone'],
        'phone2'            => $_POST['phone2'],
        'phone3'            => $_POST['phone3'],
        'personal_email'    => $_POST['personal_email'],
        'work_email'        => $_POST['work_email'],
        'alternate_email'   => $_POST['alternate_email'],
        'company_name'      => $_POST['company'],
        'address_1'         => $_POST['address1'],
        'address_2'         => $_POST['address2'],
        'city'              => $_POST['city'],
        'state'             => $_POST['state'],
        'country'           => $_POST['country'],
        'zip'               => $_POST['zipcode'],
        'website'           => $_POST['website'],
        'website2'          => $_POST['website2'],
        'website3'          => $_POST['website3'],
        'job_title'         => $_POST['job_title'],
        'industry'          => $_POST['industry'],
        'twitter'           => $_POST['twitter'],
        'instagram'         => $_POST['instagram'],
        'linkedin'          => $_POST['linkedin'],
        'facebook'          => $_POST['facebook'],
        'telegram'          => $_POST['telegram'],
        'skype'             => $_POST['skype'],
        'comments'          => $_POST['description'],
        'modified_at'       => $now    
    );

    // Check if a contact ID is provided for updating
    if (isset($_POST['contactId']) && is_numeric($_POST['contactId'])) {
        // Update existing contact
        $contactId = $_POST['contactId'];
        $updateResult = updateContact($contactData, $contactId, $connect);

        // Update tags associated with the contact
        if (isset($_POST['organizerMultipleTags']) && is_array($_POST['organizerMultipleTags'])) {
            updateContactTags($_POST['organizerMultipleTags'], $contactId, $connect);
        }

        // Update lists associated with the contact
        if (isset($_POST['organizerMultipleLists']) && is_array($_POST['organizerMultipleLists'])) {
            updateContactLists($_POST['organizerMultipleLists'], $contactId, $connect);
        }
        
        // INSERT ACTIVITY
        $activity_title = "Contact Edited";
        $response = addActivity($connect, $activity_title, $contactId,'0', $now, '0');

        $message  = "SUCCESS";
        header("Location: ../contacts.php?msg=".base64_encode($message));
    }
    else {
       $message  = "ERROR";
       header("Location: ../contacts.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }
}

/* Update Contact Ends Here */

/******************************************************************************************/

/*  Contacts Listing page */
if(isset($currFile) && $currFile == "contacts.php"){

    $data = array();

    //$data = getContacts($connect);

    $filterTags   = getTags($connect);

    $filterLists  = getLists($connect);

    
}
/* Contacts Listing page Ends here */

/******************************************************************************************/


/******************************************************************************************/

/*  Contact Detail page */
if(isset($currFile) && $currFile == "contact-details.php"){

    $data = array(); $data_tags = array(); $data_lists = array(); $data_activity = array();
    if(isset($_REQUEST['contact_id']) && $_REQUEST['contact_id']!= ""){
        $contactId = base64_decode($_REQUEST['contact_id']);
    }

    $data = getContactById($contact_id, $connect);

    // Fetch selected tags for the contact
    $selectedTags = getSelectedTags($contactId, $connect);
    $data_tags          = getTags($connect);

    // Fetch selected lists for the contact
    $selectedLists = getSelectedLists($contactId, $connect);
    $data_lists         = getLists($connect);

    $data_activity      = getActivitiesByContactId($connect, $contactId);
    $data_notes         = getNotesByContactId($connect, $contactId);
    $data_tasks         = getTasksByContactId($connect, $contactId);


}
/* Contacts detail page Ends here */

// Add note 
if(isset($_POST['action']) && $_POST['action'] == 'addNotes'){

    if(isset($_POST['contact_id']) && $_POST['contact_id'] != ""){
        $contactId = $_POST['contact_id'];
    }

    $noteText = mysqli_real_escape_string($connect, $_POST['noteText']);
    $created_by = 1;
    $now = date('Y-m-d H:i:s'); // Corrected date format

    $sql = "INSERT INTO contact_notes (contact_id, notes, created_at, created_by) 
                    VALUES ('$contactId', '$noteText', '$now', '$created_by')";


    if (mysqli_query($connect, $sql)) {

        // INSERT ACTIVITY
        $activity_title = "Note Added";
        $insertActivityQuery =  "INSERT INTO activity (activity_title, contact_id, created_at, created_by)
         VALUES ( '$activity_title',  '$contactId', '$now', '$created_by')";

        mysqli_query($connect, $insertActivityQuery);

        $data_notes         = getNotesByContactId($connect, $contactId);
        $notes_html = "";
        // Loop through each note in the result set
        while ($row_notes = mysqli_fetch_assoc($data_notes)) {
        $noteText           = $row_notes['notes'];
        $createdAt          = date('j M, Y h:i a', strtotime($row_notes['created_at']));
        $contact_note_id    = $row_notes['contact_note_id'];
        $notes_html .=' 
        <div class="border-2 border-dashed mb-4 pb-4 border-bottom border-translucent row justify-content-between align-items-md-center note-item">
            <div class="col-12 col-lg-auto flex-1">
                <p class="mb-1 text-body-highlight">'. $noteText .' </p>
                <div class="d-flex">
                    <div class="fs-9 text-body-tertiary text-opacity-85"><span
                            class="fa-solid fa-clock me-2"></span><span class="fw-semibold me-1"> '. $createdAt .'</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-auto">
                <div class="d-lg-block end-0" style="top: 23%;">
                    <div class="d-flex end-0">
                        <button class="btn btn-phoenix-secondary btn-icon me-1 fs-10 text-body px-0 me-1"
                                data-bs-toggle="modal" data-bs-target="#EditNotesModal"><span
                                class="fas fa-edit"></span></button>
                        <button class="btn btn-phoenix-secondary btn-icon fs-10 text-danger px-0"><span
                                class="fas fa-trash" data-contact-id="'.$contact_note_id.'"></span></button>
                    </div>
                </div>
            </div>
        </div>
        ';
        } // End of while loop 

        // Send the notes_html back to the Ajax request
        echo $notes_html;



    } else {
        echo "ERROR";
    }

}

// Delete note 
if(isset($_POST['action']) && $_POST['action'] == 'deleteNoteContact'){

    $contactNoteId = isset($_POST['contact_note_id']) ? $_POST['contact_note_id'] : null;

    if ($contactNoteId !== null) {
        // Sanitize input to prevent SQL injection
        $contactNoteId = mysqli_real_escape_string($connect, $contactNoteId);

        // Execute the DELETE query
        $deleteQuery = "DELETE FROM contact_notes WHERE contact_note_id = $contactNoteId";

        if (mysqli_query($connect, $deleteQuery)) {
            echo "SUCCESS";
        } else {
            echo "Error: Unable to delete note.";
        }

    }
}
// Delete note  Completed

// Edit Task
if(isset($_POST['action']) && $_POST['action'] == 'editTask'){

    if(isset($_POST['taskId']) && $_POST['taskId'] !=  "") {
        // Decode the base64 encoded task ID
        $task_id = base64_decode($_POST['taskId']);
        $contact_id = base64_decode($_POST['contact_id']);
        $now = date('Y-m-d H:i:s');
        
        // Check the current status
        $current_status_query = "SELECT status FROM tasks WHERE task_id = " . $task_id;
        $current_status_result = mysqli_query($connect, $current_status_query);
        $current_status_row = mysqli_fetch_assoc($current_status_result);
        $current_status = $current_status_row['status'];

        if ($current_status == 0) {
            // If current status is 0, update to 1
            $new_status = 1;
        } else {
            // If current status is not 0, update to 0
            $new_status = 0;
        }

        $sql = "UPDATE tasks SET status = " . $new_status . ", modified_at = '" . $now . "' WHERE task_id = " . $task_id;
    
        // Execute the SQL statement
        if(mysqli_query($connect, $sql)) {

            // INSERT ACTIVITY
            if($new_status == 1)
                $activity_title = "Task Opened";
            else
                $activity_title = "Task Closed";

            addActivity($connect, $activity_title, $contact_id,'0', $now, '0');
           
            // Update successful
            echo json_encode(array("success" => true));
        } else {
            // Update failed
            echo json_encode(array("success" => false, "error" => mysqli_error($mysqli)));
        }
    }

}
// Edit Task Completed
/******************************************************************************************/

/*  Contact Filter */
// Check if the request is an Ajax request
if (isset($_POST['action']) && $_POST['action'] == 'filterContacts') {

    // Get the filter values from the Ajax request
    $tagFilter = isset($_POST['tagFilter']) ? $_POST['tagFilter'] : '';
    $listFilter = isset($_POST['listFilter']) ? $_POST['listFilter'] : '';
    $statusFilter = isset($_POST['statusFilter']) ? $_POST['statusFilter'] : '';
    $page = isset($_POST['page']) ? $_POST['page'] : '';
    $itemsPerPage = isset($_POST['itemsPerPage']) ? $_POST['itemsPerPage'] : '';

    $start = ($page - 1) * $itemsPerPage;

    // Build the SQL query based on the selected filters
    $sql = "SELECT contact_id AS CONTACT_ID, CONCAT(first_name, ' ', last_name) AS NAME, personal_email AS EMAIL, mobile AS MOBILE_NUMBER, company_name AS COMPANY, job_title AS DESIGNATION, DATE_FORMAT(created_at ,'%d-%m-%Y') AS CREATED_AT FROM contact_master WHERE 1 AND is_deleted != 0";

    if ($tagFilter == 'none') {
        // Display contacts not associated with any tag ID in the contact_tag table
        $sql .= " AND contact_id IN (SELECT DISTINCT contact_id FROM contact_tag WHERE tag_id = 0 )";
    } else if ($tagFilter == 'any') {
        // No need to add additional condition for 'any' tag filter
    } else if (!empty($tagFilter)) {
        // Include specific tag filter condition
        $sql .= " AND contact_id IN (SELECT contact_id FROM contact_tag WHERE tag_id = '$tagFilter')";
    }

    // Include list filter condition
    if (!empty($listFilter)) {
        if ($listFilter == 'any') {
            // Display contacts associated with any list in contact_list table
            $sql .= " AND contact_id IN (SELECT DISTINCT contact_id FROM contact_list)";
        } elseif ($listFilter == 'none') {
            // Display contacts not associated with any list in contact_list table
            $sql .= " AND contact_id NOT IN (SELECT DISTINCT contact_id FROM contact_list)";
        } else {
            // Display contacts associated with the selected list id in contact_list table
            $sql .= " AND contact_id IN (SELECT contact_id FROM contact_list WHERE list_id = '$listFilter')";
        }
    }

    // Include status filter condition
    if ($statusFilter >= 0 && $statusFilter != "") {
        // Display contacts associated with the selected list id in contact_list table
        $sql .= " AND status ='$statusFilter'";
    }

    // Add LIMIT clause for pagination
    $sql .= " LIMIT $start, $itemsPerPage";

    // Execute the SQL query
    $result = mysqli_query($connect, $sql);

    // Prepare the filtered contacts HTML
    $filteredContactsTable = '';

    if ($result && mysqli_num_rows($result) > 0) {
        // Loop through the query result and construct HTML table rows
        while ($row = mysqli_fetch_assoc($result)) {

                if(isset($row["NAME"]) && $row["NAME"] !== ""){ $fullname = $row["NAME"]; } else { $fullname = "-na-" ; }
                if(isset($row["DESIGNATION"]) && $row["DESIGNATION"] !== ""){ $designation  = $row["DESIGNATION"]; } else { $designation = "-na-"; }
                if(isset($row["EMAIL"]) && $row["EMAIL"] !== ""){ $email = $row["EMAIL"]; } else { $email = "-na-"; }
                if(isset($row["MOBILE_NUMBER"]) && $row["MOBILE_NUMBER"] !== ""){ $mobile = $row["MOBILE_NUMBER"]; } else { $mobile = "-na-"; }
                if(isset($row["COMPANY"]) && $row["COMPANY"] !== ""){ $company = $row["COMPANY"]; } else { $company = "-na-"; }
                if(isset($row["CREATED_AT"]) && $row["CREATED_AT"] !== ""){ $created_at = $row["CREATED_AT"]; } else { $created_at = "-na-"; }
                $contact_id = $row["CONTACT_ID"];

            $filteredContactsTable .= '
                    <tr>
                        <td>
                            <div class="form-check mb-0 fs-8">
                              <input class="form-check-input" type="checkbox" />
                              <input type="hidden" value="'.base64_encode($contact_id).'" name="contact_idH" />
                            </div>
                        </td>
                        <td class="name align-middle white-space-nowrap ps-0 border-end border-start-0 border-translucent">
                            <div class="d-flex align-items-center">
                              <div><a class="fs-8 fw-bold" href="contact-details.php?contact_id='.base64_encode($contact_id).'">'.ucwords($fullname).'</a>
                                <div class="d-flex align-items-center">
                                  <p class="mb-0 text-body-highlight fw-semibold fs-9 me-2 ms-0">'.ucwords($designation).'</p>
                                </div>
                              </div>
                            </div>
                        </td>
                        <td class="email align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent"><a class="text-body-highlight" href="mailto:'. $email.'">'.$email.'</a>
                        </td>
                        <td class="phone align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent"><a class="text-body-highlight" href="tel:'.$mobile.'">'.$mobile.'</a>
                        </td>
                        <td class="company align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 border-end border-translucent fw-semibold text-body-highlight">'.ucwords($company).'
                        </td>
                        <td class="date align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 text-body-tertiary border-end border-translucent">' . $created_at.'
                        </td>
                        <td class="align-middle white-space-nowrap text-end pe-0 ps-4">
                            <div class="btn-reveal-trigger position-static">
                            <a class="send-an-email-modal btn btn-primary py-2 px-2" href="send-an-email.php"><span class="fa-solid fa-envelope me-2"></span>Email</a>                          
                              <button class="btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal fs-10" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                              <div class="dropdown-menu dropdown-menu-end py-2">
                              <a class="add-note-modal dropdown-item" href="add-note-popup.php?contact_id='.base64_encode($contact_id).'">Add a note</a>
                              <a class="add-task-modal dropdown-item" href="add-task-popup.php?contact_id='.base64_encode($contact_id).'">Add a task</a>
                              <a class="add-deal-modal dropdown-item" href="add-deal-popup.php">Add a deal</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-primary edit-contact" href="contact-details.php?contact_id='.base64_encode($contact_id).'">Edit contact</a>
                                <a class="dropdown-item text-danger delete-contact" data-cid="'.base64_encode($contact_id).'" href="javascript:;">Delete contact</a>
                              </div>
                            </div>
                        </td>
                    </tr>';
        }
    } else {
        $filteredContactsTable = '<tr><td colspan="7">No contacts found</td></tr>';
    }

    
    // Construct the base SQL query for counting total records
    $totalRecordsSql = "SELECT COUNT(*) AS total FROM contact_master WHERE 1 AND is_deleted != 0";

    // Add filter conditions based on the selected filters
    if ($tagFilter == 'none') {
        // Display contacts not associated with any tag ID in the contact_tag table
        $totalRecordsSql .= " AND contact_id IN (SELECT DISTINCT contact_id FROM contact_tag WHERE tag_id = 0 )";
    } else if ($tagFilter == 'any') {
        // No need to add additional condition for 'any' tag filter
    } else if (!empty($tagFilter)) {
        // Include specific tag filter condition
        $totalRecordsSql .= " AND contact_id IN (SELECT contact_id FROM contact_tag WHERE tag_id = '$tagFilter')";
    }

    // Include list filter condition
    if (!empty($listFilter)) {
        if ($listFilter == 'any') {
            // Display contacts associated with any list in contact_list table
            $totalRecordsSql .= " AND contact_id IN (SELECT DISTINCT contact_id FROM contact_list)";
        } elseif ($listFilter == 'none') {
            // Display contacts not associated with any list in contact_list table
            $totalRecordsSql .= " AND contact_id NOT IN (SELECT DISTINCT contact_id FROM contact_list)";
        } else {
            // Display contacts associated with the selected list id in contact_list table
            $totalRecordsSql .= " AND contact_id IN (SELECT contact_id FROM contact_list WHERE list_id = '$listFilter')";
        }
    }

    // Include status filter condition
    if ($statusFilter >= 0 && $statusFilter != "") {
        // Display contacts associated with the selected list id in contact_list table
        $totalRecordsSql .= " AND status ='$statusFilter'";
    }

    // Execute the total records SQL query
    $totalRecordsResult = mysqli_query($connect, $totalRecordsSql);

    // Retrieve the total count
    $totalCount = 0;
    if ($totalRecordsResult) {
        $row = mysqli_fetch_assoc($totalRecordsResult);
        $totalCount = $row['total'];
    }

    // Prepare response array with filtered contacts HTML and total count
    $response = array(
        'html' => $filteredContactsTable,
        'totalCount' => $totalCount
    );

    // Encode response array as JSON and echo
    echo json_encode($response);

}

/* Contacts Filter Ends here */

/*PAGINATION START*/

// Include your database connection and required functions

if (isset($_POST['action']) && $_POST['action'] == 'getPaginatedContacts') {
    $page = $_POST['page'];
    $itemsPerPage = $_POST['itemsPerPage'];

    $start = ($page - 1) * $itemsPerPage;

    // Call your function to get paginated contacts
    $result = getPaginatedContacts($connect, $start, $itemsPerPage);

     echo json_encode($result);
}

// Function to get paginated contacts data from contact_master table
function getPaginatedContacts($connect, $start, $itemsPerPage) {
    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch total count of records
    $totalCountSql = "SELECT COUNT(*) AS total FROM contact_master WHERE is_deleted != 0";
    $totalCountResult = mysqli_query($connect, $totalCountSql);
    $totalCountRow = mysqli_fetch_assoc($totalCountResult);
    $totalCount = $totalCountRow['total'];

    $sql = "SELECT 
                contact_id AS CONTACT_ID,
                CONCAT(first_name, ' ', last_name) AS NAME,
                personal_email AS EMAIL,
                mobile AS MOBILE_NUMBER,
                company_name AS COMPANY,
                job_title AS DESIGNATION,
                DATE_FORMAT(created_at ,'%d-%m-%Y') AS CREATED_AT
            FROM contact_master WHERE is_deleted != 0
            LIMIT $start, $itemsPerPage";

    $result = mysqli_query($connect, $sql);

    // Create HTML for the paginated table rows
    $html = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $contactID = $row['CONTACT_ID'];
        $fullName = ucwords($row['NAME']);if($fullName !=""){$fullName = ucwords($row['NAME']);}else{$fullName = "-na-";}
        $email = $row['EMAIL'];if($email !=""){$email = $row['EMAIL'];}else{$email = "-na-";}
        $mobileNumber = $row['MOBILE_NUMBER'];if($mobileNumber !=""){ $mobileNumber = $row['MOBILE_NUMBER']; }else{$mobileNumber = "-na-";}
        $company = ucwords($row['COMPANY']);if($company !=""){$company = ucwords($row['COMPANY']);}else{$company = "-na-";}
        $designation = ucwords($row['DESIGNATION']);if($designation !=""){$designation = ucwords($row['DESIGNATION']);}else{$designation = "-na-";}
        $createdAt = $row['CREATED_AT'];if($createdAt !=""){$createdAt = $row['CREATED_AT'];}else{$createdAt = "-na-";}

        // HTML for each table row
        $html .= "<tr class='hover-actions-trigger btn-reveal-trigger position-static'>";
        $html .= "<td class='fs-9 align-middle'>
                        <div class='form-check mb-0 fs-8'>
                            <input class='form-check-input' type='checkbox' data-bulk-select-row='{
                                \"customer\":{
                                    \"name\":\"$fullName\",
                                    \"designation\":\"$designation\",
                                    \"email\":\"$email\",
                                    \"phone\":\"$mobileNumber\",
                                    \"company\":\"$company\",
                                    \"date\":\"$createdAt\"
                                }
                            }' />
                            <input type='hidden' value='" . base64_encode($contactID) . "' name='contact_idH' />
                        </div>
                    </td>";
        $html .= "<td class='name align-middle white-space-nowrap ps-0 border-end border-start-0 border-translucent'>
                        <div class='d-flex align-items-center'>
                            <div><a class='fs-8 fw-bold' href='contact-details.php?contact_id=" . base64_encode($contactID) . "'>$fullName</a>
                                <div class='d-flex align-items-center'>
                                    <p class='mb-0 text-body-highlight fw-semibold fs-9 me-2 ms-0'>$designation</p>
                                </div>
                            </div>
                        </div>
                    </td>";
        $html .= "<td class='email align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent'>
                        <a class='text-body-highlight' href='mailto:$email'>$email</a>
                    </td>";
        $html .= "<td class='phone align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent'>
                        <a class='text-body-highlight' href='tel:$mobileNumber'>$mobileNumber</a>
                    </td>";
        $html .= "<td class='company align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 border-end border-translucent fw-semibold text-body-highlight'>
                        $company
                    </td>";
        $html .= "<td class='date align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 text-body-tertiary border-end border-translucent'>
                        $createdAt
                    </td>";
        $html .= "<td class='align-middle white-space-nowrap text-end pe-0 ps-4'>
                        <div class='btn-reveal-trigger position-static'>
                            <a class='send-an-email-modal btn btn-primary py-2 px-2' href='send-an-email.php'><span class='fa-solid fa-envelope me-2'></span>Email</a>
                            <button class='btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal fs-10' type='button' data-bs-toggle='dropdown' data-boundary='window' aria-haspopup='true' aria-expanded='false' data-bs-reference='parent'>
                                <span class='fas fa-ellipsis-h fs-10'></span>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end py-2'>
                                <a class='add-note-modal dropdown-item' href='add-note-popup.php?contact_id=" . base64_encode($contactID) . "'>Add a note</a>
                                <a class='add-task-modal dropdown-item' href='add-task-popup.php?contact_id=" . base64_encode($contactID) . "'>Add a task</a>
                                <a class='add-deal-modal dropdown-item' href='add-deal-popup.php'>Add a deal</a>
                                <div class='dropdown-divider'></div>
                                <a class='dropdown-item text-primary edit-contact'  href='contact-details.php?contact_id=" . base64_encode($contactID) . "'>Edit contact</a>
                                <a class='dropdown-item text-danger delete-contact' data-cid='" . base64_encode($contactID) . "' href='javascript:;'>Delete contact</a>
                            </div>
                        </div>
                    </td>";
        $html .= "</tr>";
    }

    //return $html;
    return array(
        'html' => $html,
        'totalCount' => $totalCount
    );
}


/*PAGINATION End*/


/*DELETE CONTACT */

if (isset($_POST['action']) && $_POST['action'] == 'deleteContact') {

    if(isset($_POST['contact_id'])) {
        $contact_id = base64_decode($_POST['contact_id']);
        
        // Delete the contact from the database
        $delQuery = "UPDATE contact_master SET is_deleted = '0' WHERE contact_id='$contact_id'";
        mysqli_query($connect, $delQuery);


        if(mysqli_affected_rows($connect) > 0) {
            // Contact deleted successfully
            echo json_encode(array("success" => true));
        } else {
            // Failed to delete contact
            echo json_encode(array("success" => false, "message" => "Failed to delete contact."));
        }
    } else {
        // Invalid request
        echo json_encode(array("success" => false, "message" => "Invalid request."));
    }
}

/*DELETE CONTACT ENDS HERE */

/* Load Contacts by list - implemented in list detail page*/
// Check if the request is an Ajax request
if (isset($_POST['action']) && $_POST['action'] == 'getContactsByListId') {

    // Get the filter values from the Ajax request
   
    $listFilter = isset($_POST['list_id']) ? $_POST['list_id'] : '';
    $page = isset($_POST['currentPage']) ? $_POST['currentPage'] : '';
    $itemsPerPage = isset($_POST['itemsPerPage']) ? $_POST['itemsPerPage'] : '';

    $start = ($page - 1) * $itemsPerPage;

    // Build the SQL query based on the selected filters
    $sql = "SELECT contact_id AS CONTACT_ID, CONCAT(first_name, ' ', last_name) AS NAME, personal_email AS EMAIL, mobile AS MOBILE_NUMBER, company_name AS COMPANY, job_title AS DESIGNATION, DATE_FORMAT(created_at ,'%d-%m-%Y') AS CREATED_AT FROM contact_master WHERE 1 AND is_deleted != 0";

    // Include list filter condition
    if (!empty($listFilter)) {
        // Display contacts associated with the selected list id in contact_list table
            $sql .= " AND contact_id IN (SELECT contact_id FROM contact_list WHERE list_id = '$listFilter')";
    }

    // Add LIMIT clause for pagination
    $sql .= " LIMIT $start, $itemsPerPage";

    // Execute the SQL query
    $result = mysqli_query($connect, $sql);

    // Prepare the filtered contacts HTML
    $filteredContactsTable = '';

    if ($result && mysqli_num_rows($result) > 0) {
        // Loop through the query result and construct HTML table rows
        while ($row = mysqli_fetch_assoc($result)) {

                if(isset($row["NAME"]) && $row["NAME"] !== ""){ $fullname = $row["NAME"]; } else { $fullname = "-na-" ; }
                if(isset($row["DESIGNATION"]) && $row["DESIGNATION"] !== ""){ $designation  = $row["DESIGNATION"]; } else { $designation = "-na-"; }
                if(isset($row["EMAIL"]) && $row["EMAIL"] !== ""){ $email = $row["EMAIL"]; } else { $email = "-na-"; }
                if(isset($row["MOBILE_NUMBER"]) && $row["MOBILE_NUMBER"] !== ""){ $mobile = $row["MOBILE_NUMBER"]; } else { $mobile = "-na-"; }
                if(isset($row["COMPANY"]) && $row["COMPANY"] !== ""){ $company = $row["COMPANY"]; } else { $company = "-na-"; }
                if(isset($row["CREATED_AT"]) && $row["CREATED_AT"] !== ""){ $created_at = $row["CREATED_AT"]; } else { $created_at = "-na-"; }
                $contact_id = $row["CONTACT_ID"];

            $filteredContactsTable .= '
                    <tr>
                        <td>
                            <div class="form-check mb-0 fs-8">
                              <input class="form-check-input" type="checkbox" />
                              <input type="hidden" value="'.base64_encode($contact_id).'" name="contact_idH" />
                            </div>
                        </td>
                        <td class="name align-middle white-space-nowrap ps-0 border-end border-start-0 border-translucent">
                            <div class="d-flex align-items-center">
                              <div><a class="fs-8 fw-bold" href="contact-details.php?contact_id='.base64_encode($contact_id).'">'.ucwords($fullname).'</a>
                                <div class="d-flex align-items-center">
                                  <p class="mb-0 text-body-highlight fw-semibold fs-9 me-2 ms-0">'.ucwords($designation).'</p>
                                </div>
                              </div>
                            </div>
                        </td>
                        <td class="email align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent"><a class="text-body-highlight" href="mailto:'. $email.'">'.$email.'</a>
                        </td>
                        <td class="phone align-middle white-space-nowrap fw-semibold ps-4 border-end border-translucent"><a class="text-body-highlight" href="tel:'.$mobile.'">'.$mobile.'</a>
                        </td>
                        <td class="company align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 border-end border-translucent fw-semibold text-body-highlight">'.ucwords($company).'
                        </td>
                        <td class="date align-middle white-space-nowrap text-body-tertiary text-opacity-85 ps-4 text-body-tertiary border-end border-translucent">' . $created_at.'
                        </td>
                        <td class="align-middle white-space-nowrap text-end pe-0 ps-4">
                            <div class="btn-reveal-trigger position-static">
                            <a class="send-an-email-modal btn btn-primary py-2 px-2" href="send-an-email.php"><span class="fa-solid fa-envelope me-2"></span>Email</a>                          
                              <button class="btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal fs-10" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                              <div class="dropdown-menu dropdown-menu-end py-2">
                              <a class="add-note-modal dropdown-item" href="add-note-popup.php?contact_id='.base64_encode($contact_id).'">Add a note</a>
                              <a class="add-task-modal dropdown-item" href="add-task-popup.php?contact_id='.base64_encode($contact_id).'">Add a task</a>
                              <a class="add-deal-modal dropdown-item" href="add-deal-popup.php">Add a deal</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-primary edit-contact" href="contact-details.php?contact_id='.base64_encode($contact_id).'">Edit contact</a>
                                <a class="dropdown-item text-danger delete-contact" data-cid="'.base64_encode($contact_id).'" href="javascript:;">Delete contact</a>
                              </div>
                            </div>
                        </td>
                    </tr>';
        }
    } else {
        $filteredContactsTable = '<tr><td colspan="7">No contacts found</td></tr>';
    }

    
    // Construct the base SQL query for counting total records
    $totalRecordsSql = "SELECT COUNT(*) AS total FROM contact_master WHERE 1 AND is_deleted != 0";

    // Include list filter condition
    if (!empty($listFilter)) {
        // Display contacts associated with the selected list id in contact_list table
        $totalRecordsSql .= " AND contact_id IN (SELECT contact_id FROM contact_list WHERE list_id = '$listFilter')";
        }

    // Execute the total records SQL query
    $totalRecordsResult = mysqli_query($connect, $totalRecordsSql);

    // Retrieve the total count
    $totalCount = 0;
    if ($totalRecordsResult) {
        $row = mysqli_fetch_assoc($totalRecordsResult);
        $totalCount = $row['total'];
    }

    // Prepare response array with filtered contacts HTML and total count
    $response = array(
        'html' => $filteredContactsTable,
        'totalCount' => $totalCount
    );

    // Encode response array as JSON and echo
    echo json_encode($response);

}


/******************************************************************************************/


/* Create Account */
if(isset( $_POST['frm_acnt_post'] ) && $_POST['frm_acnt_post'] == 'createAccount' ){
    //Get Current Date & Time
    $now = date('Y-m-d H:i:s');

    // Set Database Columns and the row data posted from front end
    $accountData = array(
        'account_name'              => $_POST['account_name'],
        'account_owner'             => $_POST['account_owner'],
        'url'                       => $_POST['url'],
        'phone'                     => $_POST['phone'],
        'phone2'                    => $_POST['phone2'],
        'phone3'                    => $_POST['phone3'],
        'email'                     => $_POST['email'],
        'email2'                    => $_POST['email2'],
        'email3'                    => $_POST['email3'],
        'number_of_employees'       => $_POST['noofemployees'],
        'address_1'                 => $_POST['address1'],
        'address_2'                 => $_POST['address2'],
        'city'                      => $_POST['city'],
        'state'                     => $_POST['state'],
        'country'                   => $_POST['country'],
        'zip'                       => $_POST['zipcode'],
        'annual_revenue'            => $_POST['revenue'],
        'industry'                  => $_POST['industry'],
        'description'               => $_POST['description'],
        'comments'                  => $_POST['comments'],
        'status'                    => 1,
        'created_at'                => $now,
        'modified_at'               => $now    
    );

    createAccounts($accountData, $connect);
}
/* Create Contact Ends Here */

/******************************************************************************************/

/*  Accounts Listing page */
if(isset($currFile) && $currFile == "accounts.php"){

    $data = array();

    $data = getAccounts($connect);
}
/* Accounts Listing page Ends here */

/******************************************************************************************/

/*  Account Detail page */
if(isset($currFile) && $currFile == "account-details.php"){

    $data = array(); 

    $data = getAccountById($account_id, $connect);

}
/* Account Detail page Ends here */

/******************************************************************************************/

/*  List Listing page */
if(isset($currFile) && $currFile == "lists.php"){

    $data = array(); 

    $data = getLists($connect);
    $listCount = getListCount($connect);


}
/* Account List Listing Ends here */

/******************************************************************************************/
/* Create List */

if(isset( $_POST['frm_list_post'] ) && $_POST['frm_list_post'] == 'createList' ){
    //Get Current Date & Time
    $now = date('Y-m-d H:i:s');

    // Get List details and insert data into List Master Table
    $listName           = mysqli_real_escape_string($connect, $_POST["list_name"]);
    $description        = mysqli_real_escape_string($connect, htmlspecialchars($_POST["list_description"]));

    $insertListData     =  "INSERT INTO `list_master` (`list_name`, `description`, `created_at`, `modified_at`) 
                            VALUES ('$listName', '$description', '$now', '$now')";
    $result_query       = mysqli_query($connect, $insertListData);
    $list_insert_id     = mysqli_insert_id($connect);

    // Get email ids and account names from account master and contact master table

    $emailArr = array(); $accountArr = array();$tags=array();

    $sql_getEmail = "SELECT contact_id, personal_email FROM contact_master";
    $getEmail_key = "contact_id"; $getEmail_value = "personal_email";

    $sql_getAccDetails = "SELECT account_id, account_name FROM account_master";
    $getAccDetails_key = "account_id"; $getAccDetails_value = "account_name";

    // Get data from database for contacts and accounts
    $emailArr   = getContactAccountData($connect, $sql_getEmail, $getEmail_key, $getEmail_value);
    $accountArr = getContactAccountData($connect, $sql_getAccDetails, $getAccDetails_key, $getAccDetails_value);


    // Read and Process the CSV file
    $csvFilePath = $_FILES['customFile']['tmp_name'];
    $handle = fopen($csvFilePath, "r");

    fgetcsv($handle);//Adding this line will skip the reading of the first line - Header

    while (($data = fgetcsv($handle)) !== false) {

        $first_name         = mysqli_real_escape_string($connect, $data[0]);
        $last_name          = mysqli_real_escape_string($connect, $data[1]);
        $contact_owner      = mysqli_real_escape_string($connect, $data[2]);
        $job_title          = mysqli_real_escape_string($connect, $data[3]);
        $personal_email     = mysqli_real_escape_string($connect, $data[4]);
        $work_email         = mysqli_real_escape_string($connect, $data[5]);
        $alternate_email    = mysqli_real_escape_string($connect, $data[6]);
        $linkedin           = mysqli_real_escape_string($connect, $data[7]);
        $mobile             = mysqli_real_escape_string($connect, $data[8]);
        $mobile2            = mysqli_real_escape_string($connect, $data[9]);
        $mobile3            = mysqli_real_escape_string($connect, $data[10]);
        $phone              = mysqli_real_escape_string($connect, $data[11]);
        $phone2             = mysqli_real_escape_string($connect, $data[12]);
        $phone3             = mysqli_real_escape_string($connect, $data[13]);
        $address_1          = mysqli_real_escape_string($connect, $data[14]);
        $address_2          = mysqli_real_escape_string($connect, $data[15]);
        $city               = mysqli_real_escape_string($connect, $data[16]);
        $state              = mysqli_real_escape_string($connect, $data[17]);
        $country            = mysqli_real_escape_string($connect, $data[18]);
        $zip                = mysqli_real_escape_string($connect, $data[19]);
        $twitter            = mysqli_real_escape_string($connect, $data[20]);
        $instagram          = mysqli_real_escape_string($connect, $data[21]);
        $facebook           = mysqli_real_escape_string($connect, $data[22]);
        $telegram           = mysqli_real_escape_string($connect, $data[23]);
        $skype              = mysqli_real_escape_string($connect, $data[24]);
        $comments           = mysqli_real_escape_string($connect, $data[25]);
                
        $company_name       = mysqli_real_escape_string($connect, $data[26]);
        $company_url        = mysqli_real_escape_string($connect, $data[27]);

        $comp_linkedin      = mysqli_real_escape_string($connect, $data[28]);
        $industry           = mysqli_real_escape_string($connect, $data[29]);
        $comp_address_1     = mysqli_real_escape_string($connect, $data[30]);
        $comp_address_2     = mysqli_real_escape_string($connect, $data[31]);
        $comp_city          = mysqli_real_escape_string($connect, $data[32]);
        $comp_state         = mysqli_real_escape_string($connect, $data[33]);
        $comp_country       = mysqli_real_escape_string($connect, $data[34]);
        $comp_zip           = mysqli_real_escape_string($connect, $data[35]);
        $number_of_employees= mysqli_real_escape_string($connect, $data[36]);
        $annual_revenue     = mysqli_real_escape_string($connect, $data[37]);
        $comp_phone         = mysqli_real_escape_string($connect, $data[38]);
        $comp_phone2        = mysqli_real_escape_string($connect, $data[39]);
        $comp_phone3        = mysqli_real_escape_string($connect, $data[40]);
        $comp_email         = mysqli_real_escape_string($connect, $data[41]);
        $comp_email2        = mysqli_real_escape_string($connect, $data[42]);
        $comp_email3        = mysqli_real_escape_string($connect, $data[43]);
        // Tags field
        $tags = explode(',', mysqli_real_escape_string($connect, $data[44]));
        $status             = mysqli_real_escape_string($connect, $data[45]);


        // Handle Account Duplicates, If new account insert into account master else account duplicate
        $matched_account_id = array_search(mysqli_real_escape_string($connect, $data[26]), $accountArr);
        handleAccountDuplicates($connect, $data, $now, $contact_owner, $matched_account_id);

        // Handle Contact Duplicates, If new contact insert into contact master else contact duplicate
        handleContactDuplicates($connect, $data, $now, $list_insert_id, $emailArr, $contact_owner,$tags, $status);

    }
    // Get Count of contacts updated and Update List Master table with No. of Contacts

    $getCntcCountQuery = "SELECT COUNT(contact_id) AS count FROM contact_list WHERE list_id ='".$list_insert_id."'";
    $result_cntcCount = mysqli_query($connect, $getCntcCountQuery);
    $row_cntctCount = mysqli_fetch_assoc($result_cntcCount);
    $contactCount = $row_cntctCount['count'];
    
    $updateListQuery = "UPDATE list_master SET no_of_contacts=".$contactCount." WHERE list_id='".$list_insert_id."'";

    if (mysqli_query($connect, $updateListQuery)) {
        $message  = "SUCCESS";
        header("Location: ../lists.php?msg=".base64_encode($message));


    } else {
        $message  = "ERROR";
        header("Location: ../lists.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }
}
/* Create List Ends Here */


/*  List Detail page */
if(isset($currFile) && $currFile == "list-details.php"){

    $filterLists = array(); $filterTags = array();

    $filterTags   = getTags($connect);
    $filterLists  = getLists($connect);

    $listName     = getListNamebyId($connect, $list_id);
}
/* List Detail page Ends Here */

/*  Import Contacts to List page - On Pageload*/
if(isset($currFile) && $currFile == "add-contacts-list.php"){
    $listArr = array();
    $listArr = getListDetailsbyId($connect, $list_id);

    $listName = $listArr['name'];
    $listDesc = $listArr['description'];

}
/*  Import Contacts to List page Ends Here */

/* Import contacts to list - Post CSV*/

/* Import contacts to List */

if(isset( $_POST['frm_import_contacts'] ) && $_POST['frm_import_contacts'] == 'importContactsList' ){
    //Get Current Date & Time
    $now = date('Y-m-d H:i:s');

    // Get List details 
    $list_insert_id     = base64_decode($_POST['list_id']);

    // Get email ids and account names from account master and contact master table

    $emailArr = array(); $accountArr = array();$tags=array();

    $sql_getEmail = "SELECT contact_id, personal_email FROM contact_master";
    $getEmail_key = "contact_id"; $getEmail_value = "personal_email";

    $sql_getAccDetails = "SELECT account_id, account_name FROM account_master";
    $getAccDetails_key = "account_id"; $getAccDetails_value = "account_name";

    // Get data from database for contacts and accounts
    $emailArr   = getContactAccountData($connect, $sql_getEmail, $getEmail_key, $getEmail_value);
    $accountArr = getContactAccountData($connect, $sql_getAccDetails, $getAccDetails_key, $getAccDetails_value);


    // Read and Process the CSV file
    $csvFilePath = $_FILES['customFile']['tmp_name'];
    $handle = fopen($csvFilePath, "r");

    fgetcsv($handle);//Adding this line will skip the reading of the first line - Header

    while (($data = fgetcsv($handle)) !== false) {

        $first_name         = mysqli_real_escape_string($connect, $data[0]);
        $last_name          = mysqli_real_escape_string($connect, $data[1]);
        $contact_owner      = mysqli_real_escape_string($connect, $data[2]);
        $job_title          = mysqli_real_escape_string($connect, $data[3]);
        $personal_email     = mysqli_real_escape_string($connect, $data[4]);
        $work_email         = mysqli_real_escape_string($connect, $data[5]);
        $alternate_email    = mysqli_real_escape_string($connect, $data[6]);
        $linkedin           = mysqli_real_escape_string($connect, $data[7]);
        $mobile             = mysqli_real_escape_string($connect, $data[8]);
        $mobile2            = mysqli_real_escape_string($connect, $data[9]);
        $mobile3            = mysqli_real_escape_string($connect, $data[10]);
        $phone              = mysqli_real_escape_string($connect, $data[11]);
        $phone2             = mysqli_real_escape_string($connect, $data[12]);
        $phone3             = mysqli_real_escape_string($connect, $data[13]);
        $address_1          = mysqli_real_escape_string($connect, $data[14]);
        $address_2          = mysqli_real_escape_string($connect, $data[15]);
        $city               = mysqli_real_escape_string($connect, $data[16]);
        $state              = mysqli_real_escape_string($connect, $data[17]);
        $country            = mysqli_real_escape_string($connect, $data[18]);
        $zip                = mysqli_real_escape_string($connect, $data[19]);
        $twitter            = mysqli_real_escape_string($connect, $data[20]);
        $instagram          = mysqli_real_escape_string($connect, $data[21]);
        $facebook           = mysqli_real_escape_string($connect, $data[22]);
        $telegram           = mysqli_real_escape_string($connect, $data[23]);
        $skype              = mysqli_real_escape_string($connect, $data[24]);
        $comments           = mysqli_real_escape_string($connect, $data[25]);
                
        $company_name       = mysqli_real_escape_string($connect, $data[26]);
        $company_url        = mysqli_real_escape_string($connect, $data[27]);

        $comp_linkedin      = mysqli_real_escape_string($connect, $data[28]);
        $industry           = mysqli_real_escape_string($connect, $data[29]);
        $comp_address_1     = mysqli_real_escape_string($connect, $data[30]);
        $comp_address_2     = mysqli_real_escape_string($connect, $data[31]);
        $comp_city          = mysqli_real_escape_string($connect, $data[32]);
        $comp_state         = mysqli_real_escape_string($connect, $data[33]);
        $comp_country       = mysqli_real_escape_string($connect, $data[34]);
        $comp_zip           = mysqli_real_escape_string($connect, $data[35]);
        $number_of_employees= mysqli_real_escape_string($connect, $data[36]);
        $annual_revenue     = mysqli_real_escape_string($connect, $data[37]);
        $comp_phone         = mysqli_real_escape_string($connect, $data[38]);
        $comp_phone2        = mysqli_real_escape_string($connect, $data[39]);
        $comp_phone3        = mysqli_real_escape_string($connect, $data[40]);
        $comp_email         = mysqli_real_escape_string($connect, $data[41]);
        $comp_email2        = mysqli_real_escape_string($connect, $data[42]);
        $comp_email3        = mysqli_real_escape_string($connect, $data[43]);
        // Tags field
        $tags = explode(',', mysqli_real_escape_string($connect, $data[44]));
        $status             = mysqli_real_escape_string($connect, $data[45]);


        // Handle Account Duplicates, If new account insert into account master else account duplicate
        $matched_account_id = array_search(mysqli_real_escape_string($connect, $data[26]), $accountArr);
        handleAccountDuplicates($connect, $data, $now, $contact_owner, $matched_account_id);

        // Handle Contact Duplicates, If new contact insert into contact master else contact duplicate
        handleContactDuplicates($connect, $data, $now, $list_insert_id, $emailArr, $contact_owner,$tags, $status);

    }
    // Get Count of contacts updated and Update List Master table with No. of Contacts

    $getCntcCountQuery = "SELECT COUNT(contact_id) AS count FROM contact_list WHERE list_id ='".$list_insert_id."'";
    $result_cntcCount = mysqli_query($connect, $getCntcCountQuery);
    $row_cntctCount = mysqli_fetch_assoc($result_cntcCount);
    $contactCount = $row_cntctCount['count'];
    
    $updateListQuery = "UPDATE list_master SET no_of_contacts=".$contactCount." WHERE list_id='".$list_insert_id."'";

    if (mysqli_query($connect, $updateListQuery)) {
        $message  = "SUCCESS";
        header("Location: ../lists.php?msg=".base64_encode($message));


    } else {
        $message  = "ERROR";
        header("Location: ../lists.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }
}
/* Import contacts to Ends Here */

/*  Edit List - On Pageload*/
if(isset($currFile) && $currFile == "edit-list.php"){
    $listArr = array();
    $listArr = getListDetailsbyId($connect, $list_id);

    $listName = $listArr['name'];
    $listDesc = $listArr['description'];

}
/*  Edit List page Ends Here */


/*  Edit List - POST DATA */
if(isset( $_POST['frm_edit_list'] ) && $_POST['frm_edit_list'] == 'editList' ){
    //Get Current Date & Time
    $now = date('Y-m-d H:i:s');

    // Get List details 
    $list_id            = mysqli_real_escape_string($connect, base64_decode($_POST['list_id']));
    $list_name          = mysqli_real_escape_string($connect, trim($_POST['list_name']));
    $list_description   = mysqli_real_escape_string($connect, trim($_POST['list_description']));

    //$updateListQuery = "UPDATE list_master SET '".$list_name."', description='".$list_description."' WHERE list_id='".$list_id."'";

    $updateListQuery = "UPDATE list_master SET list_name='".$list_name."', description='".$list_description."' WHERE list_id='".$list_id."'";


    if (mysqli_query($connect, $updateListQuery)) {
        $message  = "ADDSUCCESS";
        $successmsg = "List Updated Successfully";
        header("Location: ../lists.php?msg=".base64_encode($message)."&successmsg=".base64_encode($successmsg));


    } else {
        $message  = "ADDERROR";
        header("Location: ../lists.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }
   


}
/*  Edit List page Ends Here */


/******************************************************************************************/

/*  Tags Listing page */
if(isset($currFile) && $currFile == "tags.php"){

    $data = array();

    $data = getTags($connect);
    $tagsCount = getTagsCount($connect);
    
}
/* Contacts Listing page Ends here */



/*************************************************************************************** */

/************************************************************************************** */
/***** ALL FUNCTION DEFINITION START HERE  ********/



// Function to insert data into the contact_master table 
function createContacts($data, $connect) {

    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Sanitize string inputs using mysqli_real_escape_string
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = mysqli_real_escape_string($connect, $value);
        }
    }

    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", $data) . "'";

    $sql = "INSERT INTO contact_master ($columns) VALUES ($values)";

   if (mysqli_query($connect, $sql)) {
        return mysqli_insert_id($connect); // Return the last inserted ID
    } else {
        return false;
    }

}


// Add a function to update an existing contact
function updateContact($contactData, $contactId, $connect) {

    $updateQuery = "UPDATE contact_master SET ";

    foreach ($contactData as $key => $value) {
        $updateQuery .= "$key = '$value', ";
    }

    $updateQuery = rtrim($updateQuery, ', ');
    $updateQuery .= " WHERE contact_id = $contactId";

    $result = mysqli_query($connect, $updateQuery);

    return $result;
}

// Common Function to get Emails and Account Names in to respective array
function getContactAccountData($connect, $query, $idField, $valueField) {
    $dataArr = array();
    $result_query = mysqli_query($connect, $query);

    while ($row = mysqli_fetch_assoc($result_query)) {
        $dataArr[$row[$idField]] = $row[$valueField];
    }

    return $dataArr;
}

// Function to handle duplicate data for Contacts Table ** USED FOR CHECKING DUPLICATE DATA **
function handleContactDuplicates($connect, $data, $now, $list_insert_id, $emailArr, $contact_owner, $tags, $status) {
    $matched_contact_id = array_search(mysqli_real_escape_string($connect, $data[4]), $emailArr);

    // If Personal Email found in our database then add contact to duplicates table
    if ($matched_contact_id) {
        insertContactDuplicate($connect, $contact_owner, mysqli_real_escape_string($connect, $data[0]), mysqli_real_escape_string($connect, $data[1]), mysqli_real_escape_string($connect, $data[8]), mysqli_real_escape_string($connect, $data[9]), mysqli_real_escape_string($connect, $data[10]), mysqli_real_escape_string($connect, $data[11]), mysqli_real_escape_string($connect, $data[12]), mysqli_real_escape_string($connect, $data[13]), mysqli_real_escape_string($connect, $data[4]), mysqli_real_escape_string($connect, $data[5]), mysqli_real_escape_string($connect, $data[6]), mysqli_real_escape_string($connect, $data[26]), mysqli_real_escape_string($connect, $data[14]), mysqli_real_escape_string($connect, $data[15]), mysqli_real_escape_string($connect, $data[16]), mysqli_real_escape_string($connect, $data[17]), mysqli_real_escape_string($connect, $data[18]), mysqli_real_escape_string($connect, $data[19]), mysqli_real_escape_string($connect, $data[27]), mysqli_real_escape_string($connect, $data[3]), mysqli_real_escape_string($connect, $data[20]), mysqli_real_escape_string($connect, $data[21]), mysqli_real_escape_string($connect, $data[7]), mysqli_real_escape_string($connect, $data[22]), mysqli_real_escape_string($connect, $data[23]), mysqli_real_escape_string($connect, $data[24]), mysqli_real_escape_string($connect, $data[25]), $now, $list_insert_id);
    } else {
        // Email does not exist, insert into contacts table
        insertContact($connect, $contact_owner, mysqli_real_escape_string($connect, $data[0]), mysqli_real_escape_string($connect, $data[1]), mysqli_real_escape_string($connect, $data[8]), mysqli_real_escape_string($connect, $data[9]), mysqli_real_escape_string($connect, $data[10]), mysqli_real_escape_string($connect, $data[11]), mysqli_real_escape_string($connect, $data[12]), mysqli_real_escape_string($connect, $data[13]), mysqli_real_escape_string($connect, $data[4]), mysqli_real_escape_string($connect, $data[5]), mysqli_real_escape_string($connect, $data[6]), mysqli_real_escape_string($connect, $data[26]), mysqli_real_escape_string($connect, $data[14]), mysqli_real_escape_string($connect, $data[15]), mysqli_real_escape_string($connect, $data[16]), mysqli_real_escape_string($connect, $data[17]), mysqli_real_escape_string($connect, $data[18]), mysqli_real_escape_string($connect, $data[19]), mysqli_real_escape_string($connect, $data[27]), mysqli_real_escape_string($connect, $data[3]), mysqli_real_escape_string($connect, $data[20]), mysqli_real_escape_string($connect, $data[21]), mysqli_real_escape_string($connect, $data[7]), mysqli_real_escape_string($connect, $data[22]), mysqli_real_escape_string($connect, $data[23]), mysqli_real_escape_string($connect, $data[24]), mysqli_real_escape_string($connect, $data[25]), $now, $list_insert_id, $tags, $status);
    }
}


//If duplicate data - insert to contact duplicate table
function insertContactDuplicate($connect, $contact_owner, $first_name, $last_name, $mobile, $mobile2, $mobile3, $phone, $phone2, $phone3, $personal_email, $work_email, $alternate_email, $company_name, $address_1, $address_2, $city, $state, $country, $zip, $company_url, $job_title, $twitter, $instagram, $linkedin, $facebook, $telegram, $skype, $comments, $now, $matched_contact_id) {
    $insertDuplicateQuery = "INSERT INTO contact_duplicate (contact_id, contact_owner, first_name, last_name, mobile, mobile2, mobile3, phone, phone2, phone3, personal_email, work_email, alternate_email, company_name, address_1, address_2, city, state, country, zip, website,  job_title, twitter, instagram, linkedin, facebook, telegram, skype, comments, created_at)
        VALUES ('$matched_contact_id', '$contact_owner', '$first_name', '$last_name', '$mobile', '$mobile2', '$mobile3', '$phone', '$phone2','$phone3', '$personal_email', '$work_email', '$alternate_email', '$company_name', '$address_1', '$address_2', '$city', '$state','$country', '$zip', '$company_url', '$job_title', '$twitter', '$instagram', '$linkedin', '$facebook', '$telegram', '$skype', '$comments', '$now')";

    mysqli_query($connect, $insertDuplicateQuery);
}

function insertContact($connect, $contact_owner, $first_name, $last_name, $mobile, $mobile2, $mobile3, $phone, $phone2, $phone3, $personal_email, $work_email, $alternate_email, $company_name, $address_1, $address_2, $city, $state, $country, $zip, $company_url, $job_title, $twitter, $instagram, $linkedin, $facebook, $telegram, $skype, $comments, $now, $list_insert_id, $tags, $status) {
    if(isset($first_name) && $first_name != ""){
        $personal_email = filter_var($personal_email, FILTER_SANITIZE_EMAIL);

        // Validate email
        if (filter_var($personal_email, FILTER_VALIDATE_EMAIL)) {
            $insertQuery = "INSERT INTO contact_master (contact_owner, first_name, last_name, mobile, mobile2, mobile3, phone, phone2, phone3, personal_email, work_email, alternate_email, company_name, address_1, address_2, city, state, country, zip, website,  job_title, twitter, instagram, linkedin, facebook, telegram, skype, comments, status, created_at, modified_at)
                            VALUES ('$contact_owner', '$first_name', '$last_name', '$mobile', '$mobile2', '$mobile3', '$phone', '$phone2','$phone3', '$personal_email', '$work_email', '$alternate_email', '$company_name', '$address_1', '$address_2', '$city', '$state','$country', '$zip', '$company_url', '$job_title', '$twitter', '$instagram', '$linkedin', '$facebook', '$telegram', '$skype', '$comments', '$status', '$now', '$now')";
            mysqli_query($connect, $insertQuery);
            $contact_insert_id = mysqli_insert_id($connect);

            // INSERT LIST
            $insertContactListQuery = "INSERT INTO contact_list (list_id, contact_id, created_at)
                                       VALUES ($list_insert_id,  $contact_insert_id, '$now')";
            mysqli_query($connect, $insertContactListQuery);

            // INSERT TAGS
            if(!empty($tags)){
                // Insert tags into contact_tag table
                insertListTags($tags, $contact_insert_id, $connect, $now);
            }

            // INSERT ACTIVITY
            $activity_title = "Contact Added";
            $response = addActivity($connect, $activity_title, $contact_insert_id,'0', $now, '0');
        }
    } else {
        insertContactDuplicate($connect, $contact_owner, $first_name, $last_name, $mobile, $mobile2, $mobile3, $phone, $phone2, $phone3, $personal_email, $work_email, $alternate_email, $company_name, $address_1, $address_2, $city, $state, $country, $zip, $company_url, $job_title, $twitter, $instagram, $linkedin, $facebook, $telegram, $skype, $comments, $now, 0);
    }
}


// Function to handle duplicate data for Contacts Table ** USED FOR CHECKING DUPLICATE DATA ** Ends Here
function handleAccountDuplicates($connect, $data, $now, $contact_owner, $matched_account_id){
    // If Account Name found in our database then add account to duplicates table
    if ($matched_account_id) {
        $insertAcDuplicateQuery = "INSERT INTO account_duplicate (account_id, account_owner, account_name, url, phone, phone2, phone3, email, email2, email3, address_1, address_2, city, state, country, zip, number_of_employees, annual_revenue, industry, linkedin, created_at)
            VALUES ('$matched_account_id', '$contact_owner', '" . mysqli_real_escape_string($connect, $data[26]) . "', '" . mysqli_real_escape_string($connect, $data[27]) . "', '" . mysqli_real_escape_string($connect, $data[38]) . "', '" . mysqli_real_escape_string($connect, $data[39]) . "', '" . mysqli_real_escape_string($connect, $data[40]) . "', '" . mysqli_real_escape_string($connect, $data[41]) . "', '" . mysqli_real_escape_string($connect, $data[42]) . "', '" . mysqli_real_escape_string($connect, $data[43]) . "', '" . mysqli_real_escape_string($connect, $data[30]) . "', '" . mysqli_real_escape_string($connect, $data[31]) . "', '" . mysqli_real_escape_string($connect, $data[32]) . "', '" . mysqli_real_escape_string($connect, $data[33]) . "', '" . mysqli_real_escape_string($connect, $data[34]) . "', '" . mysqli_real_escape_string($connect, $data[35]) . "', '" . mysqli_real_escape_string($connect, $data[36]) . "', '" . mysqli_real_escape_string($connect, $data[37]) . "', '" . mysqli_real_escape_string($connect, $data[29]) . "', '" . mysqli_real_escape_string($connect, $data[28]) . "', '$now')";

        mysqli_query($connect, $insertAcDuplicateQuery);
    } else {
        // Account name does not exist and not null, insert into accounts master table
        if(isset($data[26]) && mysqli_real_escape_string($connect, $data[26]) != ""){
            $insertAcQuery = "INSERT INTO account_master (account_owner, account_name, url, phone, phone2, phone3, email, email2, email3, address_1, address_2, city, state, country, zip, number_of_employees, annual_revenue, industry, linkedin, created_at, modified_at)
            VALUES ('$contact_owner', '" . mysqli_real_escape_string($connect, $data[26]) . "', '" . mysqli_real_escape_string($connect, $data[27]) . "', '" . mysqli_real_escape_string($connect, $data[38]) . "', '" . mysqli_real_escape_string($connect, $data[39]) . "', '" . mysqli_real_escape_string($connect, $data[40]) . "', '" . mysqli_real_escape_string($connect, $data[41]) . "', '" . mysqli_real_escape_string($connect, $data[42]) . "', '" . mysqli_real_escape_string($connect, $data[43]) . "', '" . mysqli_real_escape_string($connect, $data[30]) . "', '" . mysqli_real_escape_string($connect, $data[31]) . "', '" . mysqli_real_escape_string($connect, $data[32]) . "', '" . mysqli_real_escape_string($connect, $data[33]) . "', '" . mysqli_real_escape_string($connect, $data[34]) . "', '" . mysqli_real_escape_string($connect, $data[35]) . "', '" . mysqli_real_escape_string($connect, $data[36]) . "', '" . mysqli_real_escape_string($connect, $data[37]) . "', '" . mysqli_real_escape_string($connect, $data[29]) . "', '" . mysqli_real_escape_string($connect, $data[28]) . "', '$now', '$now')";

            mysqli_query($connect, $insertAcQuery);
        }
    }
}


// Function used from the LIST - insert tags into contact_tag table
function insertListTags($tags, $contactId, $connect, $now)
{
    foreach ($tags as $tagName) {
        // Assuming you have a table named 'tag_master' with columns 'tag_id' and 'tag_name'
        $tagName = mysqli_real_escape_string($connect, trim($tagName));
        $tagIdQuery = "SELECT tag_id FROM tag_master WHERE tag_name = '$tagName'";
        $tagIdResult = mysqli_query($connect, $tagIdQuery);
        
        if ($tagIdResult && $tagIdRow = mysqli_fetch_assoc($tagIdResult)) {
            $tagId = $tagIdRow['tag_id'];
        } else {
            // If tag doesn't exist, insert it into 'tag_master'
            if(isset($tagName) && $tagName != ""){

                $insertTagQuery = "INSERT INTO tag_master (tag_name, created_at, created_by) VALUES ('$tagName','$now','0')";
                mysqli_query($connect, $insertTagQuery);
                $tagId = mysqli_insert_id($connect);
            }
        }

        // Insert tag into 'contact_tag' table
        $sql = "INSERT INTO contact_tag (tag_id, contact_id, created_at, created_by) VALUES ('$tagId', '$contactId', '$now', '0')";
        mysqli_query($connect, $sql) or die(mysqli_error($connect));
    }
    // Call the function to update the number of contacts for each tag
    updateTagContactCount($connect);
}


// Function to update the number of contacts for each tag in tag_master table
function updateTagContactCount($connect)
{
    // Select all tags from tag_master
    $tagQuery = "SELECT tag_id FROM tag_master";
    $tagResult = mysqli_query($connect, $tagQuery);

    if ($tagResult) {
        while ($tagRow = mysqli_fetch_assoc($tagResult)) {
            $tagId = $tagRow['tag_id'];
            
            // Count number of contacts for the current tag
            $countQuery = "SELECT COUNT(*) AS count FROM contact_tag WHERE tag_id = '$tagId'";
            $countResult = mysqli_query($connect, $countQuery);

            if ($countResult) {
                $countRow = mysqli_fetch_assoc($countResult);
                $contactCount = $countRow['count'];

                // Update the number of contacts for the current tag in tag_master table
                $updateQuery = "UPDATE tag_master SET no_of_contacts = '$contactCount' WHERE tag_id = '$tagId'";
                mysqli_query($connect, $updateQuery);
            }
        }
    }
}

// Function to insert tags into contact_tag table
function insertTags($tags, $contactId, $connect)
{
    foreach ($tags as $tagId) {
        $sql = "INSERT INTO contact_tag (tag_id, contact_id) VALUES ('$tagId', '$contactId')";
        mysqli_query($connect, $sql) or die(mysqli_error($connect));
    }
}

// Function to insert lists into contact_list table
function insertLists($lists, $contactId, $connect)
{
    foreach ($lists as $listId) {
        $sql = "INSERT INTO contact_list (list_id, contact_id) VALUES ('$listId', '$contactId')";
        mysqli_query($connect, $sql) or die(mysqli_error($connect));
    }
}

// Function Add Activity
function addActivity ($connect, $activity_title, $contact_id, $account_id, $created_at, $created_by){
    
    $insertActivityQuery =  "INSERT INTO activity (activity_title, contact_id, account_id, created_at, created_by)
                 VALUES ( '$activity_title',  '$contact_id', '$account_id', '$created_at', '$created_by')";
    
    
     $return_val = mysqli_query($connect, $insertActivityQuery);
     return $return_val;
}

// Function to get all contacts data from contact_master table
function getContacts($connect) {

    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT 
                contact_id AS CONTACT_ID,
                CONCAT(first_name, ' ', last_name) AS NAME,
                personal_email AS EMAIL,
                mobile AS MOBILE_NUMBER,
                company_name AS COMPANY,
                job_title AS DESIGNATION,
                DATE_FORMAT(created_at ,'%d-%m-%Y') AS CREATED_AT
            FROM contact_master WHERE is_deleted != 0";

    $result = mysqli_query($connect, $sql);
    
    return $result;
   
}

// Function to get details a contact through contact id
function getContactById($contact_id, $connect){

     // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

     $sql = "SELECT * FROM contact_master WHERE is_deleted != 0 AND contact_id=".$contact_id;

    $result = mysqli_query($connect, $sql);

    return $result;


}



// Function to insert data into the accounts_master table 
function createAccounts($data, $connect) {

    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Sanitize string inputs using mysqli_real_escape_string
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = mysqli_real_escape_string($connect, $value);
        }
    }

    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", $data) . "'";

    $sql = "INSERT INTO account_master ($columns) VALUES ($values)";

    if (mysqli_query($connect, $sql)) {
        $message  = "SUCCESS";
        header("Location: ../accounts.php?msg=".base64_encode($message));


    } else {
        $message  = "ERROR";
        header("Location: ../accounts.php?msg=".base64_encode($message)."&error=".base64_encode(mysqli_error($connect)));
    }

    mysqli_close($connect);

}

// Function to get all accounts data from account_master table
function getAccounts($connect) {

    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT 
                account_id  AS ACCOUNT_ID,
                account_name AS NAME,
                account_owner AS ACCOUNT_OWNER,
                url AS URL,
                DATE_FORMAT(created_at ,'%d-%m-%Y') AS CREATED_AT
            FROM account_master";

    $result = mysqli_query($connect, $sql);

    return $result;
   
}

// Function to get details a account through account id
function getAccountById($account_id, $connect){

     // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

     $sql = "SELECT * FROM account_master WHERE account_id=".$account_id;

    $result = mysqli_query($connect, $sql);

    return $result;
}

// Function to get all accounts data from account_master table
function getLists($connect) {

    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM list_master";

    $result = mysqli_query($connect, $sql);

    return $result;
   
}

// Function to get all Tags from tag_master table
function getTags($connect) {

    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM tag_master";

    $result = mysqli_query($connect, $sql);

    return $result;
   
}


// Function to get selected tags for a contact
function getSelectedTags($contactId, $connect) {
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT tag_id FROM contact_tag WHERE contact_id=".$contactId;
    $result = mysqli_query($connect, $sql);
    $selectedTags = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $selectedTags[] = $row['tag_id'];
    }
    return $selectedTags;
}

// Function to get selected lists for a contact
function getSelectedLists($contactId, $connect) {
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT list_id FROM contact_list WHERE contact_id=".$contactId;
    $result = mysqli_query($connect, $sql);
    $selectedLists = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $selectedLists[] = $row['list_id'];
    }
    return $selectedLists;
}


// Function to update tags associated with the contact
function updateContactTags($tags, $contactId, $connect) {
    // Delete existing tags associated with the contact
    $sqlDelete = "DELETE FROM contact_tag WHERE contact_id = $contactId";
    mysqli_query($connect, $sqlDelete);

    // Insert new tags associated with the contact
    foreach ($tags as $tag) {
        $sqlInsert = "INSERT INTO contact_tag (contact_id, tag_id) VALUES ($contactId, $tag)";
        mysqli_query($connect, $sqlInsert);
    }
}

// Function to update lists associated with the contact
function updateContactLists($lists, $contactId, $connect) {
    // Delete existing lists associated with the contact
    $sqlDelete = "DELETE FROM contact_list WHERE contact_id = $contactId";
    mysqli_query($connect, $sqlDelete);

    // Insert new lists associated with the contact
    foreach ($lists as $list) {
        $sqlInsert = "INSERT INTO contact_list (contact_id, list_id) VALUES ($contactId, $list)";
        mysqli_query($connect, $sqlInsert);
    }
}

//Function to get activities from contact id
function getActivitiesByContactId($connect, $contactId){

// Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT activity_title, activity_description, created_at FROM activity WHERE contact_id=".$contactId." ORDER BY created_at DESC";

    $result = mysqli_query($connect, $sql);

    return $result;

}

//Function to get notes from contact id
function getNotesByContactId($connect, $contactId){

// Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM contact_notes WHERE contact_id=".$contactId." ORDER BY created_at DESC";

    $result = mysqli_query($connect, $sql);

    return $result;

}

//Function to get notes from contact id
function getTasksByContactId($connect, $contactId){

// Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM tasks WHERE contact_id=".$contactId." ORDER BY due_on ASC";

    $result = mysqli_query($connect, $sql);

    return $result;

}

function getListNamebyId($connect, $list_id){
    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM list_master WHERE list_id =".$list_id;

    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
    $listName = $row['list_name'];


    return $listName;
}

function getListDetailsbyId($connect, $list_id){
    // Check connection
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $list = array();
    $sql = "SELECT * FROM list_master WHERE list_id =".$list_id;
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    $list['name']           = $row['list_name'];
    $list['description']    = $row['description'];
    $list['no_of_contacts'] = $row['no_of_contacts'];

    return $list;
}

function getTagsCount($connect){

    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $tagCount = 0;
    $sql = "SELECT COUNT(tag_id) AS tagCount FROM tag_master";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
    $tagCount = $row['tagCount'];

    return $tagCount;
}

function getListCount($connect){

    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $tagCount = 0;
    $sql = "SELECT COUNT(list_id) AS listCount FROM list_master";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
    $listCount = $row['listCount'];

    return $listCount;

}

?>
