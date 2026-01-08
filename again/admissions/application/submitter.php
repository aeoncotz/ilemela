<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; 

$submissionTime = date('Y-m-d H:i:s');

$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$rawID = "";
for ($i = 0; $i < 15; $i++) {
    $rawID .= $chars[rand(0, strlen($chars) - 1)];
}

$applicationID = implode('-', str_split($rawID, 5));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

/* ---------------------------
   BASIC SANITIZATION
---------------------------- */
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/* ---------------------------
   REQUIRED FIELDS CHECK
---------------------------- */
$required = [
    'fullName','dob','gender','nationality','address','grade',
    'fatherName','fatherPhone',
    'motherName','motherPhone'
];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        exit("Missing required field: {$field}");
    }
}

/* ---------------------------
   FORM DATA
---------------------------- */
$data = [
    'fullName'        => clean($_POST['fullName']),
    'dob'             => clean($_POST['dob']),
    'gender'          => clean($_POST['gender']),
    'nationality'     => clean($_POST['nationality']),
    'religion'        => clean($_POST['religion'] ?? ''),
    'healthIssues'    => clean($_POST['healthIssues'] ?? ''),
    'address'         => clean($_POST['address']),
    'grade'           => clean($_POST['grade']),
    'fatherName'      => clean($_POST['fatherName']),
    'fatherOccupation'=> clean($_POST['fatherOccupation'] ?? ''),
    'fatherPhone'     => clean($_POST['fatherPhone']),
    'fatherEmail'     => clean($_POST['fatherEmail'] ?? ''),
    'motherName'      => clean($_POST['motherName']),
    'motherOccupation'=> clean($_POST['motherOccupation'] ?? ''),
    'motherPhone'     => clean($_POST['motherPhone']),
    'motherEmail'     => clean($_POST['motherEmail'] ?? ''),
    'prevSchool'      => clean($_POST['prevSchool'] ?? ''),
    'lastGrade'       => clean($_POST['lastGrade'] ?? ''),
    'reasonLeaving'   => clean($_POST['reasonLeaving'] ?? '')
];

/* ---------------------------
   FILE UPLOAD HANDLING
---------------------------- */
$allowedTypes = ['pdf','jpg','jpeg','png'];
$attachments = [];

foreach (['birthCert','prevReport','photo'] as $fileKey) {
    if (!empty($_FILES[$fileKey]['name'])) {
        $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            exit("Invalid file type uploaded for {$fileKey}");
        }
        if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $attachments[] = $_FILES[$fileKey];
        }
    }
}

/* ---------------------------
   ELITE ADMISSION TEMPLATE
---------------------------- */
$messageBody = "
<div style='background-color: #f0f4f8; padding: 40px 10px; font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
    <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 650px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(30, 41, 59, 0.1); border: 1px solid #e2e8f0;'>
        
        <tr>
            <td align='center' style='background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 50px 20px;'>
                <div style='text-transform: uppercase; letter-spacing: 4px; color: #94a3b8; font-size: 12px; margin-bottom: 10px;'>Official Record</div>
                <h1 style='color: #ffffff; margin: 0; font-size: 26px; font-weight: 300; letter-spacing: 1px;'>Student Admission <span style='font-weight: 700;'>2026</span></h1>
                <div style='width: 50px; height: 3px; background-color: #3b82f6; margin-top: 20px;'></div>
            </td>
        </tr>

        <tr>
            <td style='padding: 40px 50px;'>
                
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 40px;'>
                    <tr>
                        <td style='padding-bottom: 15px;'>
                            <h2 style='font-size: 14px; text-transform: uppercase; color: #3b82f6; letter-spacing: 1px; margin: 0;'>pupil's Profile</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style='background-color: #f8fafc; border-radius: 12px; padding: 25px;'>
                            <table width='100%' style='font-size: 15px; color: #1e293b; line-height: 2;'>
                                <tr>
                                    <td width='35%' style='color: #64748b;'>Full Name</td>
                                    <td style='font-weight: 600; border-bottom: 1px solid #e2e8f0;'>{$data['fullName']}</td>
                                </tr>
                                <tr>
                                    <td width='35%' style='color: #64748b;'>Gender</td>
                                    <td style='font-weight: 600; border-bottom: 1px solid #e2e8f0;'>{$data['gender']}</td>
                                </tr>
                                <tr>
                                    <td width='35%' style='color: #64748b;'>Religion</td>
                                    <td style='font-weight: 600; border-bottom: 1px solid #e2e8f0;'>{$data['religion']}</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b;'>Grade Applied</td>
                                    <td><span style='background-color: none; border: 1px solid #143f9c; color: #143f9c; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;'>{$data['grade']}</span></td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b;'>Date of Birth</td>
                                    <td>{$data['dob']}</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b;'>Nationality</td>
                                    <td>{$data['nationality']}</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b;'>Residential Address</td>
                                    <td style='font-size: 13px;'>{$data['address']}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <br>
                <h2 style='font-size: 14px; text-transform: uppercase; color: #3b82f6; letter-spacing: 1px; margin: 0 0 15px 0;'>Form Details</h2>
                <div style='border-left: 3px solid #e2e8f0; padding-left: 20px;'>
                    <p style='margin: 0; font-size: 14px; color: #1e293b;'><strong>Application ID: {$applicationID}</p>
                    <p style='margin: 5px 0; font-size: 14px; color: #64748b;'>Processed on {$submissionTime}</p>
                </div>
                <br>

                <h2 style='font-size: 14px; text-transform: uppercase; color: #3b82f6; letter-spacing: 1px; margin: 0 0 15px 0;'>Health</h2>
                <div style='border-left: 3px solid #e2e8f0; padding-left: 20px;'>
                    <p style='margin: 0; font-size: 14px; color: #1e293b;'><strong>Health Issues</strong></p>
                    <p style='margin: 5px 0; font-size: 14px; color: #64748b;'>{$data['healthIssues']}</p>
                </div>
                <br>

                <h2 style='font-size: 14px; text-transform: uppercase; color: #3b82f6; letter-spacing: 1px; margin: 0 0 15px 0;'>Guardian Contact</h2>
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 40px;'>
                    <tr>
                        <td width='48%' style='background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; vertical-align: top;'>
                            <div style='color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;'>Father / Guardian</div>
                            <div style='color: #1e293b; font-weight: 600; font-size: 15px;'>{$data['fatherName']}</div>
                            <div style='color: #238baa; font-weight: 500; font-size: 15px;'>{$data['fatherOccupation']}</div>
                            <div style='color: #475569; font-size: 13px; margin-top: 5px;'>{$data['fatherPhone']}</div>
                            <div style='color: #3b82f6; font-size: 12px;'>{$data['fatherEmail']}</div>
                        </td>
                        <td width='4%'></td>
                        <td width='48%' style='background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; vertical-align: top;'>
                            <div style='color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;'>Mother / Guardian</div>
                            <div style='color: #1e293b; font-weight: 600; font-size: 15px;'>{$data['motherName']}</div>
                            <div style='color: #238baa; font-weight: 500; font-size: 15px;'>{$data['motherOccupation']}</div>
                            <div style='color: #475569; font-size: 13px; margin-top: 5px;'>{$data['motherPhone']}</div>
                            <div style='color: #3b82f6; font-size: 12px;'>{$data['motherEmail']}</div>
                        </td>
                    </tr>
                </table>

                <h2 style='font-size: 14px; text-transform: uppercase; color: #3b82f6; letter-spacing: 1px; margin: 0 0 15px 0;'>Academic Background</h2>
                <div style='border-left: 3px solid #e2e8f0; padding-left: 20px;'>
                    <p style='margin: 0; font-size: 14px; color: #1e293b;'><strong>Previous School:</strong> {$data['prevSchool']}</p>
                    <p style='margin: 5px 0; font-size: 14px; color: #64748b;'>Last Grade: {$data['lastGrade']}</p>
                    <p style='margin: 15px 0 0 0; font-size: 13px; color: #64748b; font-style: italic; line-height: 1.5;'>
                        <span style='color: #1e293b; font-weight: bold;'>Reason for Leaving:</span><br>
                        \"{$data['reasonLeaving']}\"
                    </p>
                </div>

            </td>
        </tr>

        <tr>
            <td align='center' style='padding: 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;'>
                <table width='100%'>
                    <tr>
                        <td align='center'>
                            <p style='margin: 0; color: #96aab6c9; font-size: 14px;'>Ilemela Schools | Admissions Office</p>
                            <p style='margin: 5px 0 0 0; color: #2b6fc2; font-size: 12px;'>This is an automated encrypted transmission.</p>
                            <p style='margin: 5px 0 0 0; color: #4b4e4c; font-size: 12px;'>Powered by John The Coda.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
";

/* ---------------------------
   PHPMailer Configuration
---------------------------- */
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'mail.ilemelaschools.ac.tz';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@ilemelaschools.ac.tz';
    $mail->Password   = 'school@ilemela2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('info@ilemelaschools.ac.tz', 'Ilemela Schools Admissions');
    $mail->addAddress('info@ilemelaschools.ac.tz');
    $mail->addReplyTo($data['fatherEmail'] ?: 'info@ilemelaschools.ac.tz');

    foreach ($attachments as $file) {
        $mail->addAttachment($file['tmp_name'], $file['name']);
    }

    $mail->isHTML(true); // REQUIRED
    $mail->Subject = 'Application ID: ' . ($applicationID) . ' - ' . $data['fullName'];
    $mail->Body    = $messageBody;
    $mail->AltBody = strip_tags($messageBody);

    $mail->send();
    
    header('Content-Type: application/json');
    echo json_encode([
    "status" => "success",
    "message" => "Thank you for applying to Ilemela English Medium School.",
    "id" => "IEMS-2026-8842",
]);
exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "System Error: {$mail->ErrorInfo}";
}