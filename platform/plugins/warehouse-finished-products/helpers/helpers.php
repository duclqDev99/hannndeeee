<?php

use Botble\Department\Models\Department;

if(!function_exists('get_proposal_receipt_product_code'))
{
    function get_proposal_receipt_product_code($id)
    {
        $prefix = 'NK';//Tiền tố của mã đơn đề xuất
        $length = 7;//Chiều dài của chuỗi sau tiền tố

        $num_length = str_pad($id, $length, '0', STR_PAD_LEFT);

        return $prefix . $num_length;
    }
}

if(!function_exists('get_proposal_receipt_product_code_not_prefix'))
{
    function get_proposal_receipt_product_code_not_prefix($id)
    {
        $length = 7;//Chiều dài của chuỗi sau tiền tố

        $num_length = str_pad($id, $length, '0', STR_PAD_LEFT);

        return $num_length;
    }
}

if(!function_exists('get_proposal_issue_product_code'))
{
    function get_proposal_issue_product_code($id)
    {
        $prefix = 'XK';//Tiền tố của mã đơn đề xuất
        $length = 7;//Chiều dài của chuỗi sau tiền tố

        $num_length = str_pad($id, $length, '0', STR_PAD_LEFT);

        return $prefix . $num_length;
    }
}

if(!function_exists('get_name_department_by_user'))
{
    function get_name_department_by_user()
    {
        $getNameDepartment = '';

        $nameDepartment = \Auth::user()->department;
        foreach ($nameDepartment as $key => $role) {
            # code...
            $department = Department::where('code', $role->department_code)->first();
            if(empty($getNameDepartment)){
                $getNameDepartment = $department?->name ?? '';
            }else{
                // $getNameDepartment = $getNameDepartment . ', ' . $department->name;
            }
        }

        return $getNameDepartment;
    }
}