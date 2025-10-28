<?php

class Auth {
    private static $usersFile = __DIR__ . '/../data/users.json';
    private static $ticketsFile = __DIR__ . '/../data/tickets.json';

    public static function init() {
        session_start();
    }

    public static function signup($email, $password) {
        $users = self::getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                throw new Exception('User already exists');
            }
        }
        $newUser = [
            'id' => uniqid(),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        $users[] = $newUser;
        self::saveUsers($users);
        self::loginUser($newUser);
        return $newUser;
    }

    public static function login($email, $password) {
        $users = self::getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                self::loginUser($user);
                return $user;
            }
        }
        throw new Exception('Invalid email or password');
    }

    public static function logout() {
        session_destroy();
    }

    public static function getCurrentUser() {
        if (!isset($_SESSION['user'])) {
            return null;
        }
        return $_SESSION['user'];
    }

    public static function isAuthenticated() {
        return self::getCurrentUser() !== null;
    }

    private static function loginUser($user) {
        $_SESSION['user'] = $user;
    }

    private static function getUsers() {
        if (!file_exists(self::$usersFile)) {
            return [];
        }
        return json_decode(file_get_contents(self::$usersFile), true) ?: [];
    }

    private static function saveUsers($users) {
        if (!is_dir(dirname(self::$usersFile))) {
            mkdir(dirname(self::$usersFile), 0777, true);
        }
        file_put_contents(self::$usersFile, json_encode($users));
    }
}
