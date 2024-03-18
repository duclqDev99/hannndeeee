<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Http\Requests\AgentRequest;
use Botble\Agent\Models\Agent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\AgentTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AgentForm;
use Botble\Agent\Models\AgentProduct;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Facades\EcommerceHelper;

class AgentController extends BaseController
{
    public function index(AgentTable $table)
    {

        PageTitle::setTitle(trans('plugins/agent::agent.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/agent/js/discount.js',
        ]);
        PageTitle::setTitle(trans('plugins/agent::agent.create'));

        return $formBuilder->create(AgentForm::class)->renderForm();
    }

    public function store(AgentRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('discount_value') === null) {
            // Remove discount_value and discount_type from the request
            $request->request->remove('discount_value');
            $request->request->remove('discount_type');
        }

        $agent = Agent::query()->create($request->input());

        event(new CreatedContentEvent(AGENT_MODULE_SCREEN_NAME, $request, $agent));

        return $response
            ->setPreviousUrl(route('agent.index'))
            ->setNextUrl(route('agent.edit', $agent->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Agent $agent, FormBuilder $formBuilder)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/agent/js/discount.js',
        ]);

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $agent->name]));

        return $formBuilder->create(AgentForm::class, ['model' => $agent])->renderForm();
    }

    public function update(Agent $agent, AgentRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('discount_value') === null) {
            // Remove discount_value and discount_type from the request
            $request->request->remove('discount_value');
            $request->request->remove('discount_type');
        }
        $agent->fill($request->input());

        $agent->save();

        event(new UpdatedContentEvent(AGENT_MODULE_SCREEN_NAME, $request, $agent));


        return $response
            ->setPreviousUrl(route('agent.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Agent $agent, Request $request, BaseHttpResponse $response)
    {
        try {
            $agent->delete();

            event(new DeletedContentEvent(AGENT_MODULE_SCREEN_NAME, $request, $agent));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getListAgentForUser()
    {
        $agents = get_agent_for_user();
        return response()->json([
            'status' => true,
            'data' => $agents
        ]);
    }
    public function getAllAgent(){
        $agents = Agent::where('status', BaseStatusEnum::PUBLISHED)->get();
        return response()->json($agents, 200);
    }
}



