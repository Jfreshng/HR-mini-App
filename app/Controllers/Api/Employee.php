<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Libraries\ApiResponse;

class Employee extends BaseController
{
    private $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
    }

    // List all employees with optional filters: department, salary range, status
    public function index()
    {
        $department = $this->request->getGet('department_id');
        $salaryMin  = $this->request->getGet('salary_min');
        $salaryMax  = $this->request->getGet('salary_max');
        $status     = $this->request->getGet('status'); // new status filter

        $builder = $this->employeeModel;

        if ($department) {
            $builder = $builder->where('department_id', $department);
        }
        if ($salaryMin) {
            $builder = $builder->where('salary >=', $salaryMin);
        }
        if ($salaryMax) {
            $builder = $builder->where('salary <=', $salaryMax);
        }
        if ($status) {
            $builder = $builder->where('status', $status);
        }

        $employees = $builder->findAll();

        return $this->response->setJSON(ApiResponse::success(
            'Employees retrieved successfully',
            $employees
        ));
    }

    public function getById($id)
{
    $user = $this->request->user; // JWT payload attached by JwtAuth

    // Admins can fetch any employee by ID
    if ($user->role === 'admin') {
        $employee = $this->employeeModel->find($id);
    } 
    else {
        // Regular users: fetch by their email instead
        $employee = $this->employeeModel->where('email', $user->email)->first();

        // Prevent users from accessing others even if they guess the ID
        if ($employee && $employee['id'] != $id) {
            return $this->response
                        ->setStatusCode(403)
                        ->setJSON(\App\Libraries\ApiResponse::error(
                            'Forbidden: cannot access other employees'
                        ));
        }
    }

    if (!$employee) {
        return $this->response
                    ->setStatusCode(404)
                    ->setJSON(\App\Libraries\ApiResponse::error(
                        'Employee not found'
                    ));
    }

    return $this->response->setJSON(\App\Libraries\ApiResponse::success(
        'Employee retrieved successfully',
        $employee
    ));
}


    // Create new employee
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Default status to active if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        if (!$this->employeeModel->insert($data)) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(ApiResponse::error(
                                      'Failed to create employee',
                                      $this->employeeModel->errors()
                                  ));
        }

        $employee = $this->employeeModel->find($this->employeeModel->getInsertID());

        return $this->response->setJSON(ApiResponse::success(
            'Employee created successfully',
            $employee
        ));
    }

    // Update employee
    public function update($id)
    {
        $data = $this->request->getJSON(true);

        // Set validation rules, ignore email uniqueness for current employee
        $this->employeeModel->setValidationRules([
            'email' => "required|valid_email|is_unique[employees.email,id,{$id}]",
            'name' => 'required|min_length[2]',
            'position' => 'required',
            'salary' => 'required|decimal',
            'department_id' => 'required|integer',
            'status' => 'required|in_list[active,inactive]'
        ]);

        if (!$this->employeeModel->update($id, $data)) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(ApiResponse::error(
                                      'Failed to update employee',
                                      $this->employeeModel->errors()
                                  ));
        }

        $employee = $this->employeeModel->find($id);

        return $this->response->setJSON(ApiResponse::success(
            'Employee updated successfully',
            $employee
        ));
    }

    // Delete employee
    public function delete($id)
    {
        if (!$this->employeeModel->delete($id)) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(ApiResponse::error(
                                      'Failed to delete employee'
                                  ));
        }

        return $this->response->setJSON(ApiResponse::success(
            'Employee deleted successfully'
        ));
    }
}
