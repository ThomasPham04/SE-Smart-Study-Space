<?php
class CheckInOut {
    protected $conn;
    public $reservationId;
    public $qrCode;
    public $checkInTime;
    public $checkOutTime;
    public $status;

    public function __construct($conn, $reservationId = null, $qrCode = null, $checkInTime = null, $checkOutTime = null, $status = null) {
        $this->conn = $conn;
        $this->reservationId = $reservationId;
        $this->qrCode = $qrCode;
        $this->checkInTime = $checkInTime;
        $this->checkOutTime = $checkOutTime;
        $this->status = $status;
    }

    public function generateQRCode($data, $outputFile = null) {
        require_once __DIR__ . '/../lib/phpqrcode-2010100721_1.1.4/phpqrcode/qrlib.php';
        if ($outputFile) {
            QRcode::png($data, $outputFile, 'L', 4, 2);
            return $outputFile;
        } else {
            QRcode::png($data);
            return true;
        }
    }
} 