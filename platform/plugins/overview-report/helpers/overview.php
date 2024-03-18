
<?php

if(!function_exists('getSelectionListBasedOnUserChoice')){
    function getSelectionListBasedOnUserChoice($request) {
        $list = [];
        if (!$request->filled('selectOption2')) {
            if ($request->selectOption1 == 1 || $request->selectOption1 == 0 || !$request->has('selectOption1')) {
                $list = get_agent_for_user()->pluck('id');
            } elseif ($request->selectOption1 == 2) {
                $list = get_showroom_for_user()->pluck('id');
            }
        } else {
            $list[] = $request->selectOption2;
        }

        return $list;
    }
}

