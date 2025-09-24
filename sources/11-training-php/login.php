<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập cookie session an toàn
session_set_cookie_params([
    'httponly' => true,
    'secure' => true, // Chỉ HTTPS
    'samesite' => 'Strict'
]);
session_start();

require_once 'models/UserModel.php';
require_once __DIR__ . '/middleware/csrf.php';


$userModel = new UserModel();

// Thông tin Redis Cloud
$redisHost = 'redis-16955.c245.us-east-1-3.ec2.redns.redis-cloud.com';
$redisPort = 16955;
$redisPassword = 'xOXxBh22pzacgo4x9eVljRj4meFNqmPC';

// Kết nối Redis an toàn
$canUseRedis = false;
try {
    $redis = new Redis();
    $redis->connect($redisHost, $redisPort);
    $redis->auth($redisPassword);
    if ($redis->ping() === '+PONG') {
        $canUseRedis = true;
    }
} catch (Exception $e) {
    $canUseRedis = false;
}

// Sinh CSRF token
$csrf_token = CsrfMiddleware::generateToken();

// Captcha — chỉ sinh khi load form, không sinh lại khi submit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $captcha_code = rand(1000, 9999);
    $_SESSION['captcha'] = $captcha_code;
} else {
    $captcha_code = $_SESSION['captcha'] ?? '';
}

// Xử lý login
$loginSuccess = false;
$loginUser = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!CsrfMiddleware::verifyToken($_POST['csrf_token'] ?? '')) {
        $message = 'CSRF token không hợp lệ!';
    } elseif (!isset($_POST['captcha']) || $_POST['captcha'] != ($_SESSION['captcha'] ?? '')) {
        $message = 'Captcha không đúng!';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $user = $userModel->auth($username, $password);
            if ($user && isset($user[0])) {
                $loginUser = $user[0];
                $loginSuccess = true;

                $_SESSION['id'] = $loginUser['id'] ?? null;
                $_SESSION['message'] = 'Login successful';

                if ($canUseRedis) {
                    $sessionId = session_id();
                    $data = json_encode([
                        'id' => $loginUser['id'],
                        'username' => $loginUser['username']
                    ]);
                    $redis->setex("session:$sessionId", 3600, $data);
                }
            } else {
                $message = 'Login failed: invalid username or password';
            }
        } catch (Exception $e) {
            $message = 'Login error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
<?php include 'views/header.php'; ?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading"><div class="panel-title">Login</div></div>
            <div style="padding-top:30px" class="panel-body">
                <form method="post" class="form-horizontal" role="form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username" placeholder="Username or email" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <input type="checkbox" tabindex="3" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <label for="captcha">Captcha: </label>
                        <strong><?php echo $captcha_code; ?></strong>
                        <input type="text" name="captcha" placeholder="Enter captcha" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account! <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($loginSuccess && $loginUser): ?>
<script>
    localStorage.setItem('id', <?php echo json_encode($loginUser['id']); ?>);
    window.location.href = "list_users.php";
</script>
<?php endif; ?>

</body>
</html>