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

// Gender with validation
$gender = $application['gender'] ?? 'N/A';
// Auto-correct common gender inconsistencies based on names
if (!empty($child_first_name)) {
    $femaleNames = ['ama', 'akosua', 'adwoa', 'yaa', 'efua', 'aba', 'akua', 'afia'];
    $maleNames = ['kwame', 'kwaku', 'kwadwo', 'yaw', 'kofi', 'kwabena', 'kweku', 'kojo'];
    
    $firstName = strtolower(trim($child_first_name));
    if (in_array($firstName, $femaleNames) && strtolower($gender) === 'male') {
        $gender = 'Female'; // Auto-correct to female
    } elseif (in_array($firstName, $maleNames) && strtolower($gender) === 'female') {
        $gender = 'Male'; // Auto-correct to male
    }
}
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
    <title>Birth Certificate - <?= htmlspecialchars($child_first_name . ' ' . $child_last_name) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&family=Crimson+Text:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Crimson Text', serif;
            background: #f5f5f5;
            padding: 20px;
            color: #2c1810;
            line-height: 1.6;
        }

        .certificate-container {
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            background: 
                radial-gradient(circle at 20% 20%, rgba(218, 165, 32, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 69, 19, 0.03) 0%, transparent 50%),
                linear-gradient(45deg, #faf8f3 0%, #f5f1e8 100%);
            position: relative;
            padding: 0.5in;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        }

        /* Ornate Border */
        .ornate-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 4px solid #DAA520;
            border-radius: 15px;
            background: 
                linear-gradient(45deg, transparent 0%, rgba(218, 165, 32, 0.05) 50%, transparent 100%);
            box-shadow: 
                inset 0 0 0 2px #DAA520,
                inset 0 0 0 8px #faf8f3,
                inset 0 0 0 12px #DAA520;
        }

        /* Corner decorations */
        .corner-decoration {
            position: absolute;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, #DAA520 30%, transparent 70%);
            border-radius: 50%;
            opacity: 0.3;
        }

        .corner-decoration.top-left { top: 10px; left: 10px; }
        .corner-decoration.top-right { top: 10px; right: 10px; }
        .corner-decoration.bottom-left { bottom: 10px; left: 10px; }
        .corner-decoration.bottom-right { bottom: 10px; right: 10px; }

        /* Vintage pattern overlay */
        .certificate-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 2px 2px, rgba(218, 165, 32, 0.1) 1px, transparent 0),
                radial-gradient(circle at 20px 20px, rgba(139, 69, 19, 0.05) 1px, transparent 0);
            background-size: 40px 40px, 60px 60px;
            opacity: 0.3;
            pointer-events: none;
        }

        /* Header */
        .certificate-header {
            text-align: center;
            margin-top: 60px;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        .certificate-title {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            color: #2c1810;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: 3px;
        }

        .certificate-subtitle {
            font-size: 18px;
            color: #5d4e37;
            margin-bottom: 40px;
            font-style: italic;
        }

        /* Child Name Section */
        .child-name-section {
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 2;
        }

        .child-name {
            font-family: 'Dancing Script', cursive;
            font-size: 56px;
            font-weight: 700;
            color: #DAA520;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.2;
        }

        .name-underline {
            width: 400px;
            height: 2px;
            background: linear-gradient(to right, transparent 0%, #DAA520 20%, #DAA520 80%, transparent 100%);
            margin: 20px auto;
        }

        /* Content sections */
        .birth-details {
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 2;
        }

        .parents-section {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
            z-index: 2;
        }

        .parent-name {
            font-family: 'Dancing Script', cursive;
            font-size: 28px;
            font-weight: 700;
            color: #DAA520;
            margin: 10px 0;
        }

        .birth-info {
            font-size: 18px;
            color: #2c1810;
            margin: 15px 0;
            line-height: 1.8;
        }

        .birth-date {
            font-size: 20px;
            font-weight: 600;
            color: #2c1810;
            margin: 20px 0;
        }

        .hospital-info {
            font-size: 16px;
            color: #5d4e37;
            margin: 15px 0;
            font-style: italic;
        }

        .measurements {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin: 30px 0;
            font-size: 16px;
            color: #2c1810;
        }

        /* Medical Symbol */
        .medical-symbol {
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 2;
        }

        .caduceus {
            font-size: 48px;
            color: #DAA520;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Signatures */
        .signatures-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 30px;
            position: relative;
            z-index: 2;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            width: 180px;
            height: 1px;
            background: #2c1810;
            margin: 40px auto 10px;
        }

        .signature-label {
            font-size: 14px;
            color: #5d4e37;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Footer branding */
        .footer-brand {
            position: absolute;
            bottom: 30px;
            right: 50px;
            font-size: 12px;
            color: #DAA520;
            font-weight: 600;
            z-index: 2;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .certificate-container {
                box-shadow: none;
                margin: 0;
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .certificate-container {
                width: 100%;
                height: auto;
                padding: 30px 20px;
            }
            
            .certificate-title {
                font-size: 36px;
            }
            
            .child-name {
                font-size: 42px;
            }
            
            .parents-section {
                flex-direction: column;
                gap: 20px;
            }
            
            .measurements {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Ornate border and decorations -->
        <div class="ornate-border"></div>
        <div class="corner-decoration top-left"></div>
        <div class="corner-decoration top-right"></div>
        <div class="corner-decoration bottom-left"></div>
        <div class="corner-decoration bottom-right"></div>

        <!-- Header -->
        <div class="certificate-header">
            <h1 class="certificate-title">Birth Certificate</h1>
            <p class="certificate-subtitle">This certifies that</p>
        </div>

        <!-- Child Name -->
        <div class="child-name-section">
            <div class="child-name">
                <?= htmlspecialchars(trim($child_first_name . ' ' . ($child_middle_name ? $child_middle_name . ' ' : '') . $child_last_name)) ?>
            </div>
            <div class="name-underline"></div>
        </div>

        <!-- Birth Details -->
        <div class="birth-details">
            <div class="birth-info">was born to</div>
            
            <div class="parents-section">
                <div class="parent-name">
                    <?= htmlspecialchars(trim($father_first_name . ' ' . $father_last_name)) ?>
                </div>
                <div style="align-self: center; font-size: 16px; color: #5d4e37;">and</div>
                <div class="parent-name">
                    <?= htmlspecialchars(trim($mother_first_name . ' ' . $mother_last_name)) ?>
                </div>
            </div>

            <div class="birth-date">
                on <?= !empty($date_of_birth) && $date_of_birth !== 'N/A' ? date('j F, Y', strtotime($date_of_birth)) : 'Not specified' ?>
            </div>

            <div class="hospital-info">
                at <?= htmlspecialchars($hospital_name) ?> in <?= htmlspecialchars($place_of_birth) ?>
            </div>

            <div class="measurements">
                <div>
                    <strong>Weight:</strong> 
                    <?php
                    if (is_numeric($weight_at_birth) && $weight_at_birth !== 'N/A') {
                        $weightLbs = round($weight_at_birth * 2.20462, 1);
                        echo htmlspecialchars($weightLbs) . " lbs";
                    } else {
                        echo "Not specified";
                    }
                    ?>
                </div>
                <div>
                    <strong>Length:</strong> 
                    <?php
                    if (is_numeric($length_at_birth) && $length_at_birth !== 'N/A') {
                        $lengthInches = round($length_at_birth / 2.54, 1);
                        echo htmlspecialchars($lengthInches) . " in";
                    } else {
                        echo "Not specified";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Medical Symbol -->
        <div class="medical-symbol">
            <div class="caduceus">⚕</div>
        </div>

        <!-- Signatures -->
        <div class="signatures-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">
                    <?= htmlspecialchars($attending_physician) ?><br>
                    Doctor on Duty
                </div>
            </div>
            
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">
                    Registrar<br>
                    Medical Officer
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-brand">
            Birth Registry
        </div>
    </div>
</body>
</html>
