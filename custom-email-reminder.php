<?php
/*
Plugin Name: Custom Email Reminder
Description: A custom plugin to send email reminders to users without paid memberships.
Version: 1.4
Author: Your Name
*/

// Hook into WordPress admin menu
add_action('admin_menu', 'custom_email_reminder_menu');

function custom_email_reminder_menu() {
    add_menu_page('Email Reminder Settings', 'Email Reminder', 'manage_options', 'custom-email-reminder', 'custom_email_reminder_page');
}

// Admin page content
function custom_email_reminder_page() {
    ?>
    <div class="wrap">
        <h2>Email Reminder Settings</h2>
        <form method="post" action="">
            <?php
            // Check if form is submitted
            if (isset($_POST['send_email'])) {
                // Get user-defined settings
                $time_frame = sanitize_text_field($_POST['time_frame']);
                $custom_message = sanitize_text_field($_POST['custom_message']);
                $email_subject = sanitize_text_field($_POST['email_subject']);

                // Call the function to send emails
                custom_send_emails($time_frame, $custom_message, $email_subject);
            }
            ?>
            <label for="time_frame">Select Time Frame:</label>
            <select name="time_frame" id="time_frame">
                <option value="week">Last Week</option>
                <option value="month">Last Month</option>
                <!-- Add more options as needed -->
            </select>
			<br>

            <label for="email_subject">Email Subject:</label>
            <input type="text" name="email_subject" id="email_subject" value="Reminder: Upgrade Your Membership">
			<br>

            <label for="custom_message">Custom Message:</label>
            <textarea name="custom_message" id="custom_message" rows="4" cols="50">Dear User, 

It seems like you do not have a paid membership. Upgrade now to enjoy exclusive benefits.</textarea>
			<br>

            <input type="submit" name="send_email" class="button button-primary" value="Send Email">
        </form>
    </div>
    <?php
}

// Function to send emails based on user-defined settings
function custom_send_emails($time_frame, $custom_message, $email_subject) {
    global $wpdb;

    // Add your logic here to get users without a membership
    // and where the user was created in the specified time frame
    $users_query = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $wpdb->users WHERE user_registered >= %s",
            date('Y-m-d H:i:s', strtotime('-1 ' . $time_frame))
        )
    );

    // Loop through users and send email
    foreach ($users_query as $user) {
        $user_id = $user->ID;

        // Check if the user does not have a membership entry in pmpro_memberships_users
        $membership_entry = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->pmpro_memberships_users} WHERE user_id = %d",
                $user_id
            )
        );

        // If the user does not have a membership, send the email
        if (empty($membership_entry)) {
            $user_email = $user->user_email;

            // Customize the email content with the user-defined message
            $subject = $email_subject;
            $message = $custom_message;

            // Send the email
            wp_mail($user_email, $subject, $message);
        }
    }

    // Display success message
    echo '<div class="updated"><p>Emails sent successfully!</p></div>';
}
?>
