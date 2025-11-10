<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getServer('HTTP_AUTHORIZATION') 
                    ?? $request->getServer('REDIRECT_HTTP_AUTHORIZATION');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(\App\Libraries\ApiResponse::error(
                    'Missing or invalid Authorization header'
                ));
        }

        $token = $matches[1];
        $key = getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $request->user = $decoded;

            // Check role if specified in filter arguments
            if ($arguments) {
                $requiredRoles = $arguments; // e.g., ['admin']
                if (!in_array($decoded->role, $requiredRoles)) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setJSON(\App\Libraries\ApiResponse::error(
                            'Forbidden: insufficient permissions'
                        ));
                }
            }

        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(\App\Libraries\ApiResponse::error(
                    'Invalid or expired token'
                ));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}
