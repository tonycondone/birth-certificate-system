<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birth Certificate - <?= htmlspecialchars(($application['child_first_name'] ?? '') . ' ' . ($application['child_last_name'] ?? '')) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap');
        
        @page {
            size: A4;
            margin: 0;
            background: white;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .certificate-wrapper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .certificate-container {
            padding: 40mm 25mm 30mm 25mm;
            position: relative;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(102, 126, 234, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.03) 0%, transparent 50%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
        }
        
        /* Decorative Border */
        .certificate-border {
            position: absolute;
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 3px solid #667eea;
            border-radius: 4px;
            pointer-events: none;
        }
        
        .certificate-border::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border: 1px solid #a0aec0;
            border-radius: 6px;
        }
        
        /* Corner Decorations */
        .corner-decoration {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 2px solid #667eea;
        }
        
        .corner-decoration.top-left {
            top: 20mm;
            left: 20mm;
            border-right: none;
            border-bottom: none;
            border-top-left-radius: 8px;
        }
        
        .corner-decoration.top-right {
            top: 20mm;
            right: 20mm;
            border-left: none;
            border-bottom: none;
            border-top-right-radius: 8px;
        }
        
        .corner-decoration.bottom-left {
            bottom: 20mm;
            left: 20mm;
            border-right: none;
            border-top: none;
            border-bottom-left-radius: 8px;
        }
        
        .corner-decoration.bottom-right {
            bottom: 20mm;
            right: 20mm;
            border-left: none;
            border-top: none;
            border-bottom-right-radius: 8px;
        }
        
        /* Header Section */
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .government-emblem {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            position: relative;
        }
        
        .government-emblem::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 50%;
        }
        
        .emblem-icon {
            font-size: 36px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .country-name {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .department-name {
            font-size: 16px;
            color: #718096;
            margin-bottom: 25px;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        
        .certificate-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .certificate-subtitle {
            font-size: 14px;
            color: #a0aec0;
            font-style: italic;
            letter-spacing: 0.5px;
        }
        
        /* Certificate Number */
        .certificate-number {
            position: absolute;
            top: 25mm;
            right: 25mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        /* Certificate Body */
        .certificate-body {
            margin: 40px 0;
        }
        
        .intro-text {
            text-align: center;
            font-size: 18px;
            margin-bottom: 35px;
            color: #4a5568;
            font-style: italic;
            line-height: 1.8;
            padding: 0 20px;
        }
        
        /* Child Information Section */
        .child-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
        }
        
        .child-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px 12px 0 0;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 13px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
            padding: 8px 0;
            border-bottom: 2px solid #e2e8f0;
            min-height: 32px;
            display: flex;
            align-items: center;
        }
        
        .detail-value.highlight {
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        /* Parents Section */
        .parents-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin: 35px 0;
        }
        
        .parent-info {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
        }
        
        .parent-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px 10px 0 0;
        }
        
        .parent-title {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Hospital Section */
        .hospital-section {
            background: rgba(72, 187, 120, 0.05);
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid rgba(72, 187, 120, 0.1);
            position: relative;
        }
        
        .hospital-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
            border-radius: 10px 10px 0 0;
        }
        
        /* Signatures Section */
        .signatures-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }
        
        .signature-block {
            text-align: center;
        }
        
        .signature-line {
            height: 50px;
            margin-bottom: 12px;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        
        .signature-line::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .signature-label {
            font-size: 14px;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .signature-date {
            font-size: 12px;
            color: #718096;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
            line-height: 1.6;
        }
        
        .footer-highlight {
            color: #667eea;
            font-weight: 600;
        }
        
        /* QR Code */
        .qr-code {
            position: absolute;
            bottom: 25mm;
            left: 25mm;
            width: 80px;
            height: 80px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #667eea;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        
        /* Security Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-family: 'Playfair Display', serif;
            font-size: 120px;
            font-weight: 700;
            color: rgba(102, 126, 234, 0.03);
            z-index: 0;
            pointer-events: none;
            letter-spacing: 8px;
        }
        
        /* Issue Date Badge */
        .issue-date {
            position: absolute;
            top: 25mm;
            left: 25mm;
            background: rgba(72, 187, 120, 0.1);
            color: #2f855a;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(72, 187, 120, 0.2);
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .certificate-wrapper {
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                min-height: 100vh !important;
            }
            
            .certificate-container {
                padding: 20mm 15mm !important;
            }
            
            .certificate-border {
                top: 10mm;
                left: 10mm;
                right: 10mm;
                bottom: 10mm;
            }
            
            .corner-decoration.top-left,
            .corner-decoration.top-right {
                top: 15mm;
            }
            
            .corner-decoration.bottom-left,
            .corner-decoration.bottom-right {
                bottom: 15mm;
            }
            
            .corner-decoration.top-left,
            .corner-decoration.bottom-left {
                left: 15mm;
            }
            
            .corner-decoration.top-right,
            .corner-decoration.bottom-right {
                right: 15mm;
            }
            
            .certificate-number {
                top: 15mm;
                right: 15mm;
            }
            
            .issue-date {
                top: 15mm;
                left: 15mm;
            }
            
            .qr-code {
                bottom: 15mm;
                left: 15mm;
            }
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .certificate-wrapper {
                width: 100%;
                margin: 10px;
            }
            
            .certificate-container {
                padding: 20px;
            }
            
            .parents-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .signatures-section {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-container">
            <!-- Decorative Elements -->
            <div class="certificate-border"></div>
            <div class="corner-decoration top-left"></div>
            <div class="corner-decoration top-right"></div>
            <div class="corner-decoration bottom-left"></div>
            <div class="corner-decoration bottom-right"></div>
            <div class="watermark">OFFICIAL</div>
            
            <!-- Certificate Number and Issue Date -->
            <div class="certificate-number">
                No: <?= htmlspecialchars($certificate['certificate_number'] ?? 'BC' . date('Y') . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT)) ?>
            </div>
            
            <div class="issue-date">
                Issued: <?= date('M j, Y') ?>
            </div>
            
            <!-- Header Section -->
            <div class="certificate-header">
                <div class="government-emblem">
                    <div class="emblem-icon">⚖️</div>
                </div>
                <div class="country-name">Republic of Ghana</div>
                <div class="department-name">Department of Civil Registration</div>
                <div class="certificate-title">Birth Certificate</div>
                <div class="certificate-subtitle">Official Record of Birth</div>
            </div>
            
            <!-- Certificate Body -->
            <div class="certificate-body">
                <div class="intro-text">
                    This is to certify that the following particulars have been compiled from the original record of birth
                    registered in accordance with the laws of the Republic of Ghana.
                </div>
                
                <!-- Child Information Section -->
                <div class="child-section">
                    <div class="section-title">Child Information</div>
                    
                    <div class="detail-grid">
                        <div class="detail-item" style="grid-column: 1 / -1;">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value highlight">
                                <?= htmlspecialchars(trim(($application['child_first_name'] ?? '') . ' ' . ($application['child_middle_name'] ?? '') . ' ' . ($application['child_last_name'] ?? ''))) ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">
                                <?= !empty($application['date_of_birth']) ? date('F j, Y', strtotime($application['date_of_birth'])) : 'Not specified' ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Time of Birth</div>
                            <div class="detail-value">
                                <?= !empty($application['time_of_birth']) ? date('g:i A', strtotime($application['time_of_birth'])) : 'Not specified' ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Place of Birth</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['place_of_birth'] ?? 'Not specified') ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">
                                <?= htmlspecialchars(!empty($application['gender']) ? ucfirst($application['gender']) : 'Not specified') ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($application['weight_at_birth'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Weight at Birth</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['weight_at_birth']) ?> kg
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($application['length_at_birth'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Length at Birth</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['length_at_birth']) ?> cm
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Parents Section -->
                <div class="parents-section">
                    <div class="parent-info">
                        <div class="parent-title">Father's Information</div>
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">
                                <?php 
                                $fatherName = trim(($application['father_first_name'] ?? '') . ' ' . ($application['father_last_name'] ?? ''));
                                echo htmlspecialchars(!empty($fatherName) ? $fatherName : 'Not provided');
                                ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">National ID</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['father_national_id'] ?? 'Not provided') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="parent-info">
                        <div class="parent-title">Mother's Information</div>
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">
                                <?php 
                                $motherName = trim(($application['mother_first_name'] ?? '') . ' ' . ($application['mother_last_name'] ?? ''));
                                echo htmlspecialchars(!empty($motherName) ? $motherName : 'Not provided');
                                ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">National ID</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['mother_national_id'] ?? 'Not provided') ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hospital Information Section -->
                <?php if (!empty($application['hospital_name'])): ?>
                <div class="hospital-section">
                    <div class="section-title">Birth Institution Information</div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Hospital/Institution</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['hospital_name']) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($application['attending_physician'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Attending Physician</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['attending_physician']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($application['physician_license'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Physician License</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($application['physician_license']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Signatures Section -->
            <div class="signatures-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Registrar of Births</div>
                    <div class="signature-date">Date: <?= date('F j, Y') ?></div>
                </div>
                
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Director, Civil Registration</div>
                    <div class="signature-date">Date: <?= date('F j, Y') ?></div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p><strong class="footer-highlight">This certificate is issued under the authority of the Civil Registration Act of Ghana.</strong></p>
                <p>Any alteration, falsification, or unauthorized reproduction of this document is a criminal offense punishable by law.</p>
                <p>For verification and authentication, visit: <strong class="footer-highlight">www.civilregistration.gov.gh</strong> or call: <strong class="footer-highlight">+233-800-VERIFY</strong></p>
                <p style="margin-top: 10px; font-size: 11px;">Certificate generated on <?= date('F j, Y \a\t g:i A T') ?> | Document ID: <?= htmlspecialchars($certificate['certificate_number'] ?? 'N/A') ?></p>
            </div>
            
            <!-- QR Code for Verification -->
            <div class="qr-code">
                <div style="font-size: 8px; margin-bottom: 4px;">🔍 VERIFY</div>
                <div style="font-size: 24px; margin: 4px 0;">⚏</div>
                <div style="font-size: 8px;">SCAN QR</div>
            </div>
        </div>
    </div>
</body>
</html>
