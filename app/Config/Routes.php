<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('api/test-db', 'Api\TestDb::index');

// $routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
//     $routes->post('register', 'Auth::register');
//     $routes->post('login', 'Auth::login');
// });

// $routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
//     $routes->post('register', 'Auth::register');
//     $routes->post('login', 'Auth::login');

//     // Protected routes
//     $routes->group('', ['filter' => 'jwt:admin'], function($routes) {
//         $routes->get('employees', 'Employee::index'); // list employees
//         $routes->post('employees', 'Employee::create');
//         $routes->put('employees/(:num)', 'Employee::update/$1');
//         $routes->delete('employees/(:num)', 'Employee::delete/$1');

//         // reports route
//         $routes->get('reports/salary', 'ReportsController::salaryByDepartment');
//         $routes->get('reports/employees', 'ReportsController::employeesByDepartment', ['filter' => 'jwt']);

//     });
// });

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Public routes
    $routes->post('register', 'Auth::register');
    $routes->post('login', 'Auth::login');

    // Admin-only routes
    $routes->group('', ['filter' => 'jwt:admin'], function($routes) {
        $routes->get('employees', 'Employee::index');        
        $routes->post('employees', 'Employee::create');      
        $routes->put('employees/(:num)', 'Employee::update/$1');  
        $routes->delete('employees/(:num)', 'Employee::delete/$1'); 

        $routes->get('reports/salary', 'ReportsController::salaryByDepartment');  
        $routes->get('reports/employees', 'ReportsController::employeesByDepartment'); 
    });

    // User-specific reports
    $routes->group('', ['filter' => 'jwt'], function($routes) {
        $routes->get('reports/salary/(:num)', 'ReportsController::salaryByEmployee/$1'); // only own report -> logged in users report
        $routes->get('employees/(:num)', 'Employee::getById/$1'); // Get employee by ID
    });
});