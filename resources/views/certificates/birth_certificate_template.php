<?php
// Ensure all variables are available and provide fallbacks
$certificate = $certificate ?? [];
$application = $application ?? [];

// Set default values if not provided
$certificate_number = $certificate['certificate_number'] ?? 'BC' . date('Y') . '-PENDING';
$issued_at = $certificate['issued_at'] ?? date('Y-m-d H:i:s');
$qr_code_data = $certificate['qr_code_data'] ?? json_encode(['certificate' => true]);

// Application data with fallbacks
$child_first_name = $application['child_first_name'] ?? 'N/A';
$child_middle_name = $application['child_middle_name'] ?? '';
$child_last_name = $application['child_last_name'] ?? 'N/A';
$date_of_birth = $application['date_of_birth'] ?? 'N/A';
$time_of_birth = $application['time_of_birth'] ?? 'N/A';
$place_of_birth = $application['place_of_birth'] ?? 'N/A';
$gender = $application['gender'] ?? 'N/A';
$weight_at_birth = $application['weight_at_birth'] ?? 'N/A';
$length_at_birth = $application['length_at_birth'] ?? 'N/A';

$father_first_name = $application['father_first_name'] ?? 'N/A';
$father_last_name = $application['father_last_name'] ?? 'N/A';
$father_national_id = $application['father_national_id'] ?? 'N/A';
$mother_first_name = $application['mother_first_name'] ?? 'N/A';
$mother_last_name = $application['mother_last_name'] ?? 'N/A';
$mother_national_id = $application['mother_national_id'] ?? 'N/A';
$hospital_name = $application['hospital_name'] ?? 'N/A';
$attending_physician = $application['attending_physician'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birth Certificate - <?= htmlspecialchars($certificate_number) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background: white;
            padding: 0;
            color: #000;
            line-height: 1.4;
        }

        .certificate-container {
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            background: white;
            border: 3px solid #000;
            position: relative;
            padding: 0.5in;
        }

        /* Official Header */
        .certificate-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .state-seal {
            width: 80px;
            height: 80px;
            border: 3px solid #000;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            background: white;
        }

        .department-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .certificate-title {
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .file-number {
            position: absolute;
            top: 0.5in;
            right: 0.5in;
            font-size: 12px;
            border: 1px solid #000;
            padding: 5px 10px;
        }

        /* Form Layout */
        .form-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            background: #000;
            color: white;
            padding: 5px 10px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .form-row {
            display: flex;
            margin-bottom: 12px;
            align-items: flex-end;
        }

        .form-field {
            flex: 1;
            margin-right: 20px;
            position: relative;
        }

        .form-field:last-child {
            margin-right: 0;
        }

        .field-label {
            font-size: 10px;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .field-value {
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 12px;
            min-height: 18px;
            font-weight: normal;
        }

        .field-number {
            position: absolute;
            right: 0;
            top: -15px;
            font-size: 8px;
            background: white;
            padding: 0 3px;
        }

        /* Two column layout for parents */
        .parents-section {
            display: flex;
            gap: 30px;
        }

        .parent-column {
            flex: 1;
        }

        /* Certification section */
        .certification-section {
            margin-top: 30px;
            border-top: 2px solid #000;
            padding-top: 20px;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.05);
            z-index: 1;
            pointer-events: none;
        }

        /* Print optimization */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .certificate-container {
                border: 3px solid #000;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="watermark">OFFICIAL</div>
        
        <div class="file-number">
            LOCAL FILE NO.<br>
            <?= htmlspecialchars($certificate_number) ?>
        </div>

        <div class="certificate-header">
            <div class="state-seal">
                STATE<br>SEAL
            </div>
            <div class="department-name">Department of Health</div>
            <div class="department-name">Division of Vital Statistics</div>
            <div class="certificate-title">Certificate of Live Birth</div>
        </div>

        <!-- Child Information -->
        <div class="form-section">
            <div class="section-title">Child Information</div>
            
            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">1</span>
                    <label class="field-label">Child's First Name</label>
                    <div class="field-value"><?= htmlspecialchars($child_first_name) ?></div>
                </div>
                <div class="form-field">
                    <span class="field-number">2</span>
                    <label class="field-label">Middle Name</label>
                    <div class="field-value"><?= htmlspecialchars($child_middle_name) ?></div>
                </div>
                <div class="form-field">
                    <span class="field-number">3</span>
                    <label class="field-label">Last Name</label>
                    <div class="field-value"><?= htmlspecialchars($child_last_name) ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">4</span>
                    <label class="field-label">Date of Birth (Month/Day/Year)</label>
                    <div class="field-value"><?= htmlspecialchars(date('m/d/Y', strtotime($date_of_birth))) ?></div>
                </div>
                <div class="form-field">
                    <span class="field-number">5</span>
                    <label class="field-label">Time of Birth</label>
                    <div class="field-value"><?= htmlspecialchars($time_of_birth) ?></div>
                </div>
                <div class="form-field">
                    <span class="field-number">6</span>
                    <label class="field-label">Sex</label>
                    <div class="field-value"><?= htmlspecialchars(strtoupper($gender)) ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">7</span>
                    <label class="field-label">Place of Birth (Hospital/Facility Name)</label>
                    <div class="field-value"><?= htmlspecialchars($hospital_name) ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">8</span>
                    <label class="field-label">City or Town of Birth</label>
                    <div class="field-value"><?= htmlspecialchars($application['city_of_birth'] ?? $place_of_birth) ?></div>
                </div>
                <div class="form-field">
                    <span class="field-number">9</span>
                    <label class="field-label">County/Region of Birth</label>
                    <div class="field-value"><?= htmlspecialchars($application['county_of_birth'] ?? 'Greater Accra Region') ?></div>
                </div>
            </div>
        </div>

        <!-- Parents Information -->
        <div class="form-section">
            <div class="section-title">Parents Information</div>
            
            <div class="parents-section">
                <!-- Father -->
                <div class="parent-column">
                    <div style="font-weight: bold; margin-bottom: 10px; font-size: 12px;">FATHER</div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">10</span>
                        <label class="field-label">Father's First Name</label>
                        <div class="field-value"><?= htmlspecialchars($father_first_name) ?></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">11</span>
                        <label class="field-label">Father's Last Name</label>
                        <div class="field-value"><?= htmlspecialchars($father_last_name) ?></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">12</span>
                        <label class="field-label">Father's ID Number</label>
                        <div class="field-value"><?= htmlspecialchars($father_national_id) ?></div>
                    </div>
                </div>

                <!-- Mother -->
                <div class="parent-column">
                    <div style="font-weight: bold; margin-bottom: 10px; font-size: 12px;">MOTHER</div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">13</span>
                        <label class="field-label">Mother's First Name</label>
                        <div class="field-value"><?= htmlspecialchars($mother_first_name) ?></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">14</span>
                        <label class="field-label">Mother's Last Name</label>
                        <div class="field-value"><?= htmlspecialchars($mother_last_name) ?></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span class="field-number">15</span>
                        <label class="field-label">Mother's ID Number</label>
                        <div class="field-value"><?= htmlspecialchars($mother_national_id) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="form-section">
            <div class="section-title">Medical and Health Information</div>
            
            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">16</span>
                    <label class="field-label">Weight at Birth</label>
                    <div class="field-value">
                        <?php
                        // Convert kg to lbs and oz for display, but show original unit
                        $weight = $weight_at_birth;
                        if (is_numeric($weight)) {
                            $weightLbs = round($weight * 2.20462, 1);
                            echo htmlspecialchars($weight) . " kg ($weightLbs lbs)";
                        } else {
                            echo htmlspecialchars($weight);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-field">
                    <span class="field-number">17</span>
                    <label class="field-label">Length at Birth</label>
                    <div class="field-value">
                        <?php
                        // Convert cm to inches for display, but show original unit
                        $length = $length_at_birth;
                        if (is_numeric($length)) {
                            $lengthInches = round($length / 2.54, 1);
                            echo htmlspecialchars($length) . " cm ($lengthInches in)";
                        } else {
                            echo htmlspecialchars($length);
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <span class="field-number">18</span>
                    <label class="field-label">Attendant's Name (MD, DO, CNM, CPM, Other)</label>
                    <div class="field-value"><?= htmlspecialchars($attending_physician) ?></div>
                </div>
            </div>
        </div>

        <!-- Certification -->
        <div class="certification-section">
            <div class="section-title">Certification</div>
            
            <p style="font-size: 11px; margin-bottom: 20px;">
                I hereby certify that this is a true and correct copy of the original Certificate of Live Birth on file 
                in the Office of Vital Statistics, Department of Health.
            </p>

            <div class="signature-area">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">State Registrar</div>
                </div>
                
                <div style="text-align: center; padding-top: 20px;">
                    <div style="border: 2px solid #000; width: 80px; height: 80px; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                        OFFICIAL<br>SEAL
                    </div>
                </div>
                
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Date Issued</div>
                    <div style="font-size: 10px; margin-top: 5px;">
                        <?= date('m/d/Y', strtotime($issued_at)) ?>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px; font-size: 10px; border-top: 1px solid #000; padding-top: 10px;">
                <strong>WARNING:</strong> Any person who willfully and knowingly makes any false statement in a birth certificate 
                shall be guilty of a misdemeanor and upon conviction thereof shall be punished by a fine or imprisonment or both.
            </div>
        </div>
    </div>
</body>
</html>
