<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\ApiResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends BaseController
{
    private $userModel;
    private $key;
    private $jwtExpirationTimeSpan;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->key = getenv('JWT_SECRET');
        $this->jwtExpirationTimeSpan = getenv('JWT_EXPIRATION');
    }

    // User registration
    public function register()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['name'], $data['email'], $data['password'])) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(ApiResponse::error(
                                      'Missing required fields',
                                      ['name', 'email', 'password']
                                  ));
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role'] = $data['role'] ?? 'user';

        if (!$this->userModel->insert($data)) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(ApiResponse::error(
                                      'Registration failed',
                                      $this->userModel->errors()
                                  ));
        }

        $user = $this->userModel->find($this->userModel->getInsertID());

        return $this->response->setJSON(ApiResponse::success(
            'User registered successfully',
            $user
        ));
    }

    // User login
    public function login()
    {
        $data = $this->request->getJSON(true);

        // Check required fields
        if (empty($data['email']) || empty($data['password'])) {
            return $this->response->setStatusCode(400)
                                ->setJSON(ApiResponse::error(
                                    'Email and password are required',
                                    ['email', 'password']
                                ));
        }

        $user = $this->userModel->where('email', $data['email'])->first();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->response->setStatusCode(401)
                                ->setJSON(ApiResponse::error('Invalid credentials'));
        }

        $payload = [
            'iss'  => 'CodeIgniterAPI',
            'sub'  => $user['id'],
            'role' => $user['role'],
            'email' => $user['email'],
            'iat'  => time(),
            'exp'  => time() + (int)$this->jwtExpirationTimeSpan,
        ];

        $jwt = JWT::encode($payload, $this->key, 'HS256');

        return $this->response->setJSON(ApiResponse::success(
            'Login successful',
            ['token' => $jwt]
        ));
    }

}
