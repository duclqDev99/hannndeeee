<?php

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentUser;

if(!function_exists('get_agent_for_user')){
    function get_agent_for_user(){
        if(request()->user()->isSuperUser() || Auth::guard()->user()->hasPermission('agent.all')){
            return \Botble\Agent\Models\Agent::query()->wherePublished()->orderBy('name','asc')->get();
        }
        $user = request()->user();
        return $user->agent;
    }
}

if(!function_exists('addAllPermissionAgent')){
    function addAllPermissionAgent(array $permissions): array {
        if (!in_array('agent.all', $permissions)) {
            $permissions[] = 'agent.all';
        }
        return $permissions;
    }
}

if(!function_exists('getListAgentIdByUser')){
    function getListAgentIdByUser(): array {
        $agent_id_by_user = get_agent_for_user()->pluck('id')->toArray();
        $agentList = Agent::where('status', 'published')
                ->whereIn('id', $agent_id_by_user)
                ->pluck('id')
                ->all();
        return $agentList;
    }
}

/**
 * @param int|string $id: ID warehouse of the receipt/issue - proposal receipt/issue of hub
 * @return bool
 */
if(!function_exists('check_user_depent_of_agent')){
function check_user_depent_of_agent($id){
        $user = \Auth::user();
        $listAgent = AgentUser::query()->where(['user_id' => $user->id])->pluck('agent_id')->all();

        $check = true;

        if($user->isSuperUser()){
            $check = false;
        }

        if(!empty($listAgent)){
            if(in_array($id, $listAgent))
            {
                $check = false;
            }
        }
        return $check;
    }
}
