<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../helpers.php';

class AuthController
{
    public static function loginForm()
    {
        self::view('login');
    }
    public static function logout()
    {
        start_session_once();
        $_SESSION = [];
        session_destroy();
        header('Location: /churchevents/');
        exit;
    }

    public static function login()
    {
        if (!verify_csrf()) return self::view('login', ['error' => 'Invalid form, try again.']);

        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (!valid_email($email)) {
            return self::view('login', ['error' => 'Invalid email.']);
        }

        // Throttle: block if too many failures in last 15 minutes
        $ipBin = client_ip_bin();
        $emailHash = email_key($email);

        $stmt = db()->prepare("
    SELECT COUNT(*) AS failures
    FROM login_attempts
    WHERE ip = ? AND email_hash = ? AND success = 0
      AND attempted_at > (NOW() - INTERVAL 15 MINUTE)
  ");
        $stmt->bind_param('ss', $ipBin, $emailHash);
        $stmt->execute();
        $failures = (int)$stmt->get_result()->fetch_assoc()['failures'];
        $stmt->close();

        if ($failures >= 5) {
            return self::view('login', ['error' => 'Too many attempts. Try again later.']);
        }

        $user  = User::findByEmail($email);
        $ok = $user && password_verify($pass, $user['password_hash']);

        // Log the attempt
        $stmt2 = db()->prepare("INSERT INTO login_attempts (ip, email_hash, attempted_at, success) VALUES (?, ?, NOW(), ?)");
        $succ = $ok ? 1 : 0;
        $stmt2->bind_param('ssi', $ipBin, $emailHash, $succ);
        $stmt2->execute();
        $stmt2->close();

        if (!$ok) {
            return self::view('login', ['error' => 'Email or password is incorrect.']);
        }

        // Successful login: regenerate session id to prevent fixation
        start_session_once();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => (int)$user['id'],
            'public_id' => $user['public_id'],
            'name'      => $user['name'],
            'handle'    => $user['handle'],
            'email'     => $user['email'],
            'role_id'   => (int)$user['role_id'],
        ];

        header('Location: /churchevents/');
        exit;
    }


    public static function registerForm()
    {
        self::view('register');
    }

    public static function register()
    {
        if (!verify_csrf()) return self::view('register', ['error' => 'Invalid form, try again.']);

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (!valid_name($name)) {
            return self::view('register', ['error' => 'Name must be between 2 and 120 characters.']);
        }
        if (!valid_email($email)) {
            return self::view('register', ['error' => 'Invalid email.']);
        }
        if (!valid_password($pass)) {
            return self::view('register', ['error' => 'Password must be 8+ chars with upper, lower, number, and symbol.']);
        }

        // Prevent duplicate email quickly (unique index will also enforce)
        if (User::findByEmail($email)) {
            return self::view('register', ['error' => 'An account with this email already exists.']);
        }

        $created = User::createAutoHandle($name, $email, $pass, 3); // 3 = volunteer
        if (!$created) {
            return self::view('register', ['error' => 'Could not create account. Please try again.']);
        }

        start_session_once();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => (int)$created['id'],
            'public_id' => $created['public_id'],
            'name'      => $created['name'],
            'handle'    => $created['handle'],
            'email'     => $created['email'],
            'role_id'   => (int)$created['role_id'],
        ];

        header('Location: /churchevents/');
        exit;
    }


    private static function view(string $name, array $data = [])
    {
        extract($data);
        // Use the same variable name the layout expects
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}
