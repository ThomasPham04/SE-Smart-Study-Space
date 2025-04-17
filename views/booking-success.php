<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng thành công - BKSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php require '../components/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="card-title mb-4">Đặt phòng thành công!</h2>
                        <p class="card-text mb-4">Cảm ơn bạn đã sử dụng dịch vụ của BKSpace. Đặt phòng của bạn đã được xác nhận.</p>
                        <div class="d-grid gap-3">
                            <a href="../index.php" class="btn btn-primary">Về trang chủ</a>
                            <a href="booking.php" class="btn btn-outline-primary">Đặt phòng khác</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 