<?php
namespace App\Services;

use App\Repositories\BirthApplicationRepository;
use TCPDF;

class CertificateGenerationService {
    private $applicationRepository;

    public function __construct(BirthApplicationRepository $applicationRepository) {
        $this->applicationRepository = $applicationRepository;
    }

    public function generateCertificate(int $applicationId): ?string {
        // Retrieve application details
        $application = $this->applicationRepository->findById($applicationId);

        if (!$application || $application['status'] !== 'approved') {
            return null;
        }

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Birth Certificate System');
        $pdf->SetAuthor('Government Vital Records');
        $pdf->SetTitle('Birth Certificate');
        $pdf->SetSubject('Official Birth Certificate');

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Certificate Content
        $html = $this->generateCertificateHTML($application);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Generate unique filename
        $filename = $this->generateUniqueFilename($application);
        $filepath = $this->getCertificateStoragePath($filename);

        // Output PDF
        $pdf->Output($filepath, 'F');

        // Update application with certificate details
        $this->applicationRepository->updateCertificateDetails(
            $applicationId, 
            $filename, 
            $filepath
        );

        return $filename;
    }

    private function generateCertificateHTML(array $application): string {
        return <<<HTML
        <style>
            .certificate { 
                border: 2px solid black; 
                padding: 20px; 
                text-align: center; 
                font-family: Arial, sans-serif; 
            }
            .header { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
            .detail { margin: 10px 0; }
        </style>
        <div class="certificate">
            <div class="header">Official Birth Certificate</div>
            <div class="detail"><strong>Name:</strong> {$application['full_name']}</div>
            <div class="detail"><strong>Date of Birth:</strong> {$application['date_of_birth']}</div>
            <div class="detail"><strong>Place of Birth:</strong> {$application['place_of_birth']}</div>
            <div class="detail"><strong>Parents:</strong> 
                {$application['father_name']} & {$application['mother_name']}
            </div>
            <div class="detail"><strong>Certificate Number:</strong> {$this->generateCertificateNumber($application)}</div>
        </div>
        HTML;
    }

    private function generateCertificateNumber(array $application): string {
        // Generate a unique certificate number
        return date('Y') . 
               str_pad($application['id'], 6, '0', STR_PAD_LEFT) . 
               substr(md5($application['full_name']), 0, 4);
    }

    private function generateUniqueFilename(array $application): string {
        return 'BC_' . 
               str_replace(' ', '_', $application['full_name']) . 
               '_' . 
               date('YmdHis') . 
               '.pdf';
    }

    private function getCertificateStoragePath(string $filename): string {
        $storagePath = __DIR__ . '/../../storage/certificates/';
        
        // Create directory if it doesn't exist
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        return $storagePath . $filename;
    }

    public function validateCertificate(string $certificateNumber): bool {
        // Implement certificate validation logic
        return $this->applicationRepository->validateCertificateNumber($certificateNumber);
    }
} 