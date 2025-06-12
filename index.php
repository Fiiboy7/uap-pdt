<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WarungKu - Sistem Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 text-center">
                    <div class="text-white">
                        <i class="fas fa-store text-4xl mb-2"></i>
                        <h1 class="text-2xl font-bold">WarungKu</h1>
                        <p class="text-blue-100">Sistem Kasir Digital</p>
                    </div>
                </div>

                <!-- Login Form -->
                <div class="px-8 py-6">
                    <?php
                    session_start();
                    if (isset($_SESSION['user_id'])) {
                        if ($_SESSION['role'] == 'admin') {
                            header("Location: admin/dashboard.php");
                        } else {
                            header("Location: kasir/dashboard.php");
                        }
                        exit;
                    }

                    if (isset($_SESSION['login_error'])) {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <i class="fas fa-exclamation-circle mr-2"></i>' . $_SESSION['login_error'] . '
                              </div>';
                        unset($_SESSION['login_error']);
                    }

                    if (isset($_SESSION['logout_success'])) {
                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                <i class="fas fa-check-circle mr-2"></i>' . $_SESSION['logout_success'] . '
                              </div>';
                        unset($_SESSION['logout_success']);
                    }
                    ?>

                    <form method="POST" action="auth/login.php" class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <input type="text" name="username" id="username" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
