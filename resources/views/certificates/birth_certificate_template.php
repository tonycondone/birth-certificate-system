<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birth Certificate</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
            line-height: 1.4;
        }
        
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            border: 3px solid #2c5aa0;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            position: relative;
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 20px;
        }
        
        .government-seal {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: #2c5aa0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
        
        .country-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .department-name {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .certificate-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .certificate-number {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 12px;
            color: #666;
        }
        
        .certificate-body {
            margin: 30px 0;
        }
        
        .intro-text {
            text-align: center;
            font-size: 16px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .details-section {
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .detail-label {
            font-weight: bold;
            width: 200px;
            color: #2c5aa0;
        }
        
        .detail-value {
            flex: 1;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            min-height: 20px;
        }
        
        .parents-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }
        
        .parent-info {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .parent-title {
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
            text-align: center;
            font-size: 16px;
        }
        
        .signatures-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        
        .signature-block {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 10px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .qr-code {
            position: absolute;
            bottom: 20px;
            left: 30px;
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(44, 90, 160, 0.1);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .certificate-container {
                border: 3px solid #2c5aa0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="watermark">OFFICIAL</div>
        
        <div class="certificate-number">
            Certificate No: <?= htmlspecialchars($certificate['certificate_number'] ?? 'BC' . date('Y') . '000001') ?>
        </div>
        
        <div class="certificate-header">
            <div class="government-seal">
                ⚖️
            </div>
            <div class="country-name">REPUBLIC OF GHANA</div>
            <div class="department-name">Department of Civil Registration</div>
            <div class="certificate-title">Birth Certificate</div>
        </div>
        
        <div class="certificate-body">
            <div class="intro-text">
                This is to certify that the following particulars have been compiled from the original record of birth:
            </div>
            
            <div class="details-section">
                <div class="detail-row">
                    <div class="detail-label">Full Name:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars(($application['child_first_name'] ?? '') . ' ' . ($application['child_middle_name'] ?? '') . ' ' . ($application['child_last_name'] ?? '')) ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars($application['date_of_birth'] ?? '') ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Time of Birth:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars(!empty($application['time_of_birth']) ? $application['time_of_birth'] : 'Not specified') ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Place of Birth:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars(!empty($application['place_of_birth']) ? $application['place_of_birth'] : 'Not specified') ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Gender:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars(!empty($application['gender']) ? ucfirst($application['gender']) : 'Not specified') ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Weight at Birth:</div>
                    <div class="detail-value">
                        <?= !empty($application['weight_at_birth']) ? htmlspecialchars($application['weight_at_birth']) . ' kg' : 'Not recorded' ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Length at Birth:</div>
                    <div class="detail-value">
                        <?= !empty($application['length_at_birth']) ? htmlspecialchars($application['length_at_birth']) . ' cm' : 'Not recorded' ?>
                    </div>
                </div>
            </div>
            
            <div class="parents-section">
                <div class="parent-info">
                    <div class="parent-title">FATHER'S INFORMATION</div>
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div class="detail-value">
                            <?php 
                            $fatherName = trim(($application['father_first_name'] ?? '') . ' ' . ($application['father_last_name'] ?? ''));
                            echo htmlspecialchars(!empty($fatherName) ? $fatherName : 'Not provided');
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">National ID:</div>
                        <div class="detail-value">
                            <?= htmlspecialchars(!empty($application['father_national_id']) ? $application['father_national_id'] : 'Not provided') ?>
                        </div>
                    </div>
                </div>
                
                <div class="parent-info">
                    <div class="parent-title">MOTHER'S INFORMATION</div>
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div class="detail-value">
                            <?php 
                            $motherName = trim(($application['mother_first_name'] ?? '') . ' ' . ($application['mother_last_name'] ?? ''));
                            echo htmlspecialchars(!empty($motherName) ? $motherName : 'Not provided');
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">National ID:</div>
                        <div class="detail-value">
                            <?= htmlspecialchars(!empty($application['mother_national_id']) ? $application['mother_national_id'] : 'Not provided') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($application['hospital_name'])): ?>
            <div class="details-section">
                <div class="detail-row">
                    <div class="detail-label">Hospital/Institution:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars($application['hospital_name']) ?>
                    </div>
                </div>
                
                <?php if (!empty($application['attending_physician'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Attending Physician:</div>
                    <div class="detail-value">
                        <?= htmlspecialchars($application['attending_physician']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="signatures-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Registrar of Births</div>
                <div class="signature-label">Date: <?= date('F j, Y') ?></div>
            </div>
            
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Director, Civil Registration</div>
                <div class="signature-label">Date: <?= date('F j, Y') ?></div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>This certificate is issued under the authority of the Civil Registration Act.</strong></p>
            <p>Any alteration or falsification of this document is a criminal offense.</p>
            <p>For verification, visit: www.civilregistration.gov or call: +1-800-VERIFY</p>
        </div>
        
        <div class="qr-code">
            QR Code<br>
            Verification
        </div>
    </div>
</body>
</html>
