<?php

use Botble\Department\Models\Department;
use Illuminate\Support\Collection;

if (!function_exists('get_department_code_curr_user')) {
    function get_department_code_curr_user()
    {
       return auth()->user()?->department ?? null;
    }
}

if (!function_exists('get_departments')) {
    function get_departments(): Collection
    {
       return constant('DEPARTMENTS');
    }
}

