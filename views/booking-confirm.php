<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKSpace - Booking</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">  
</head>
<body>
    <?php require '../components/header.php'; ?>

    <main class="main-content mt-4">
        <h1 class="page-title">FORM ĐĂNG KÝ ĐẶT CHỖ</h1>
        
        <form class="booking-form" action="/confirm-booking" method="POST">
            <div class="form-group">
                <label for="fullname">HỌ VÀ TÊN</label>
                <input 
                    type="text" 
                    id="fullname" 
                    name="fullname" 
                    required
                    placeholder="Nhập họ và tên của bạn"
                >
                <span class="hint">Ví dụ: Nguyễn Văn A</span>
            </div>

            <div class="form-group">
                <label for="student_id">MÃ SỐ SINH VIÊN</label>
                <input 
                    type="text" 
                    id="student_id" 
                    name="student_id" 
                    required
                    placeholder="Nhập mã số sinh viên"
                >
                <span class="hint">Ví dụ: 2213370</span>
            </div>

            <div class="form-group">
                <label for="start_time">THỜI GIAN BẮT ĐẦU</label>
                <input 
                    type="time" 
                    id="start_time" 
                    name="start_time" 
                    required
                >
                <span class="hint">Ví dụ: 7:00</span>
            </div>

            <div class="form-group">
                <label for="end_time">THỜI GIAN KẾT THÚC</label>
                <input 
                    type="time" 
                    id="end_time" 
                    name="end_time" 
                    required
                >
                <span class="hint">Ví dụ: 14:00</span>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-confirm">XÁC NHẬN</button>
                <button type="button" class="btn btn-back" onclick="history.back()">TRỞ LẠI</button>
            </div>
        </form>
    </main>

    <?php require '../components/footer.php'; ?>


    <!-- <script>
        // Form validation
        document.querySelector('.booking-form').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
            }
        });
    </script> -->
</body>
</html> 