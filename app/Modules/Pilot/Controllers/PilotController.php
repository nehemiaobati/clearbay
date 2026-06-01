<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Pilot\Models\PilotSignupModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class PilotController
 *
 * Handles pilot onboarding requests and email communications.
 *
 * @package App\Modules\Pilot\Controllers
 */
class PilotController extends BaseController
{
    /**
     * @var \App\Modules\Pilot\Libraries\PilotService
     */
    private \App\Modules\Pilot\Libraries\PilotService $_pilot_service;

    /**
     * PilotController constructor.
     */
    public function __construct()
    {
        $this->_pilot_service = new \App\Modules\Pilot\Libraries\PilotService();
        helper(['form', 'url']);
    }

    // ==========================================
    // // --- Helper Methods ---
    // ==========================================

    /**
     * Dispatches welcome email to the pilot program applicant.
     *
     * @param PilotSignup $signup Signup entity
     * @return bool
     */
    private function _sendWelcomeEmail(PilotSignup $signup): bool
    {
        $email = \Config\Services::email(null, false);
        $email->clear(true);
        $email_config = config('Email');

        $email->setFrom($email_config->fromEmail, $email_config->fromName);
        $email->setTo($signup->email_address);
        $email->setSubject('Welcome to the ClearBay Pilot Program!');

        $message = "
        <html>
        <head>
            <title>Welcome to ClearBay</title>
        </head>
        <body style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\">
            <h2 style=\"color: #0b5394;\">Hello " . esc($signup->full_name) . ",</h2>
            <p>Thank you for requesting pilot access to <strong>ClearBay</strong> — Nairobi's real-time ambulance off-load management platform.</p>
            <p>We are thrilled to welcome you to the first phase of our pilot. Our goal is to connect hospital emergency departments and ambulance crews, minimizing wait times and returning rescue teams to the community faster.</p>
            
            <h3 style=\"color: #333;\">What happens next?</h3>
            <ol>
                <li><strong>Review:</strong> Our operations team will review your application and organisation details (<strong>" . esc($signup->organisation) . "</strong>).</li>
                <li><strong>Contact:</strong> A representative will reach out to you at this email address within 48 hours to schedule a brief introductory call.</li>
                <li><strong>Onboarding:</strong> We will provide your team with training material and temporary system credentials for the 12-week pilot.</li>
            </ol>

            <p>If you have any immediate questions, feel free to reply to this email or write to us at <a href=\"mailto:info@clearbayhealthke.com\">info@clearbayhealthke.com</a>.</p>
            
            <p>Best regards,<br><strong>The ClearBay Operations Team</strong><br>Nairobi, Kenya</p>
        </body>
        </html>";

        $email->setMessage($message);
        $email->setMailType('html');

        $success = $email->send();
        if (!$success) {
            $debugger = $email->printDebugger(['headers', 'subject', 'body']);
            log_message('error', '[PilotController] Welcome email failed: ' . print_r($debugger, true));
        }

        return $success;
    }

    /**
     * Dispatches admin notification email for a new pilot request.
     *
     * @param PilotSignup $signup Signup entity
     * @return bool
     */
    private function _sendAdminNotificationEmail(PilotSignup $signup): bool
    {
        $email = \Config\Services::email(null, false);
        $email->clear(true);
        $email_config = config('Email');

        $email->setFrom($email_config->fromEmail, $email_config->fromName);
        $email->setTo('nehemiahobati@gmail.com'); // Admin address
        $email->setReplyTo($signup->email_address);
        $email->setSubject('New Pilot Request: ' . $signup->organisation);

        $message = "
        <html>
        <body style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\">
            <h2 style=\"color: #d9534f;\">New ClearBay Pilot Request</h2>
            <p>A new pilot request has been submitted. Details below:</p>
            <table border=\"1\" cellpadding=\"8\" cellspacing=\"0\" style=\"border-collapse: collapse; border-color: #ddd; width: 100%; max-width: 600px;\">
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold; width: 30%;\">Full Name:</td>
                    <td>" . esc($signup->full_name) . "</td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">Email Address:</td>
                    <td><a href=\"mailto:" . esc($signup->email_address) . "\">" . esc($signup->email_address) . "</a></td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">Organisation:</td>
                    <td>" . esc($signup->organisation) . "</td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">User Role:</td>
                    <td>" . esc($signup->user_role) . "</td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">Phone Number:</td>
                    <td>" . esc($signup->phone_number ?? 'Not provided') . "</td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">Message:</td>
                    <td>" . nl2br(esc($signup->message ?? 'No message provided')) . "</td>
                </tr>
                <tr>
                    <td style=\"background: #f9f9f9; font-weight: bold;\">Submitted At:</td>
                    <td>" . date('Y-m-d H:i:s') . " EAT</td>
                </tr>
            </table>
            <p><a href=\"" . base_url() . "\" style=\"display: inline-block; background: #0275d8; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;\">Go to Platform</a></p>
        </body>
        </html>";

        $email->setMessage($message);
        $email->setMailType('html');

        $success = $email->send();
        if (!$success) {
            $debugger = $email->printDebugger(['headers', 'subject', 'body']);
            log_message('error', '[PilotController] Admin notification email failed: ' . print_r($debugger, true));
        }

        return $success;
    }

    // ==========================================
    // Public Command Entry Actions
    // ==========================================

    /**
     * Handles the AJAX pilot signup submission.
     *
     * @return ResponseInterface
     */
    public function signup(): ResponseInterface
    {
        // Security: CSRF token is verified automatically by framework if enabled.
        // We will validate inputs.
        $rules = [
            'fullName'     => 'required|min_length[3]|max_length[255]',
            'emailAddress' => 'required|valid_email|max_length[255]',
            'organisation' => 'required|min_length[3]|max_length[255]',
            'userRole'     => 'required|in_list[Hospital Administrator,ED Manager / Charge Nurse,Emergency Physician,Paramedic / EMT,EMS Dispatcher / Operations Manager,Investor / Funder,Researcher / Academic,Other]',
            'phoneNumber'  => 'permit_empty|min_length[7]|max_length[50]',
            'message'      => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Please correct the errors in the form.',
                'errors'     => $this->validator->getErrors(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        // Collect fields
        $full_name     = (string) $this->request->getPost('fullName');
        $email_address = (string) $this->request->getPost('emailAddress');
        $organisation  = (string) $this->request->getPost('organisation');
        $user_role     = (string) $this->request->getPost('userRole');
        $phone_number  = $this->request->getPost('phoneNumber') ? (string) $this->request->getPost('phoneNumber') : null;
        $message       = $this->request->getPost('message') ? (string) $this->request->getPost('message') : null;

        // Instantiate entity and populate
        $signup = new PilotSignup();
        $signup->full_name     = $full_name;
        $signup->email_address = $email_address;
        $signup->organisation  = $organisation;
        $signup->user_role     = $user_role;
        $signup->phone_number  = $phone_number;
        $signup->message       = $message;

        $success = $this->_pilot_service->registerSignup($signup);

        if (!$success) {
            log_message('error', '[PilotController] Database transaction failed while saving pilot signup.');
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'An error occurred while saving your request. Please try again.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        // Send emails asynchronously (in process lifecycle)
        $this->_sendWelcomeEmail($signup);
        $this->_sendAdminNotificationEmail($signup);

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Thank you for your interest in the ClearBay pilot. Our team will contact you within 48 hours to discuss next steps. You are part of something that matters.',
            'csrf_token' => csrf_hash(),
        ]);
    }
}
