<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Libraries\ApiResponse;

class ReportsController extends BaseController
{
    protected $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
    }

    // Salary report grouped by department
    public function salaryByDepartment()
    {
        $builder = $this->employeeModel
            ->select('departments.name as department, SUM(employees.salary) as total_salary, AVG(employees.salary) as avg_salary, COUNT(employees.id) as total_employees')
            ->join('departments', 'departments.id = employees.department_id')
            ->groupBy('employees.department_id')
            ->orderBy('total_salary', 'DESC');

        $report = $builder->get()->getResult();

        return $this->response->setJSON(ApiResponse::success(
            'Salary report by department retrieved successfully',
            $report
        ));
    }

    // List employees by department (optionally filtered by department and status)
    public function employeesByDepartment()
    {
        $departmentId = $this->request->getGet('department_id');
        $status       = $this->request->getGet('status'); // filter by active/inactive

        $builder = $this->employeeModel
            ->select('departments.name as department, employees.id, employees.name, employees.email, employees.position, employees.salary, employees.status')
            ->join('departments', 'departments.id = employees.department_id')
            ->orderBy('departments.name', 'ASC')
            ->orderBy('employees.name', 'ASC');

        if ($departmentId) {
            $builder->where('employees.department_id', $departmentId);
        }

        if ($status) {
            $builder->where('employees.status', $status);
        }

        $employees = $builder->get()->getResult();

        return $this->response->setJSON(ApiResponse::success(
            'Employees by department retrieved successfully',
            $employees
        ));
    }

    public function salaryByEmployee($employeeId)
    {
        $user = $this->request->user; // JWT payload attached in JwtAuth filter

        // Users can only access their own report
        if ($user->role !== 'admin' && $user->sub != $employeeId) {
            return $this->response->setStatusCode(403)
                                ->setJSON(ApiResponse::error('Forbidden: cannot access other employees\' reports'));
        }

        $employee = $this->employeeModel
                        ->select('employees.id, employees.name, employees.salary, departments.name as department')
                        ->join('departments', 'departments.id = employees.department_id')
                        ->where('employees.id', $employeeId)
                        ->first();

        if (!$employee) {
            return $this->response->setStatusCode(404)
                                ->setJSON(ApiResponse::error('Employee not found'));
        }

        return $this->response->setJSON(ApiResponse::success(
            'Employee salary report retrieved successfully',
            $employee
        ));
    }

}
