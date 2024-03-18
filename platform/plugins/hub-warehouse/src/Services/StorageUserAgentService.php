<?php

namespace Botble\HubWarehouse\Services;

use Botble\ACL\Models\User;
use Botble\Agent\Models\AgentUser;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Services\Abstracts\StoreUserAgentServiceAbstract;
use Botble\Warehouse\Models\Material;
use Illuminate\Http\Request;

class StorageUserAgentService extends StoreUserAgentServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        AgentUser::query()->where('user_id', $user->id)->delete();
        $agentIds = $request->input('agent_id', []);
        foreach ($agentIds as $agentId) {
            $dataInsert = [
                'user_id' => $user->id,
                'agent_id' => $agentId
            ];
            $agent = AgentUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        AgentUser::query()->where('user_id', $user->id)->delete();
    }
}
