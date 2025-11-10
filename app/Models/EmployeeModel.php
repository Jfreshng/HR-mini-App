<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'position', 'salary', 'department_id', 'status'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[2]',
        'email' => 'required|valid_email|is_unique[employees.email,id,{id}]',
        'position' => 'required',
        'salary' => 'required|decimal',
        'department_id' => 'required|integer',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already in use.'
        ],
        'salary' => [
            'decimal' => 'Salary must be a number.'
        ],
        'status' => [
            'in_list' => 'Status must be either active or inactive.'
        ]
    ];
}
