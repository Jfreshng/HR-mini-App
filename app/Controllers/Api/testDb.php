<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;

class TestDb extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();

        try {
            $tables = $db->listTables();
            return $this->response->setJSON($tables);
        } catch (DatabaseException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }
}
