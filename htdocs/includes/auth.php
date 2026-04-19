<?php

class Auth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CHECK LOGIN STATUS
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // LOGIN FUNCTION (PLAIN PASSWORD)
    public function login($email, $password, $remember = false) {

        $email = $this->conn->real_escape_string($email);

        $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            // 🔥 MAIN FIX HERE (plain password compare)
            if ($password == $user['password']) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                return [
                    'success' => true,
                    'message' => 'Login successful'
                ];

            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid password'
                ];
            }

        } else {
            return [
                'success' => false,
                'message' => 'Email not found'
            ];
        }
    }

    // LOGOUT
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>