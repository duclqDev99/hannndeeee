<?php

namespace Botble\Agent\Forms;

use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Agent\Http\Requests\AgentWarehouseRequest;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentWarehouse;

class AgentWarehouseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $agentUser = getListAgentIdByUser();
        $listAgent = Agent::query()
            ->wherePublished()
            ->when(!request()->user()->isSuperUser(), function($query) use ($agentUser) {
                $query->whereIn('id', $agentUser);
            })
            ->pluck('name', 'id');
        $this
            ->setupModel(new AgentWarehouse())
            ->setValidatorClass(AgentWarehouseRequest::class)
            ->withCustomFields()
            ->add('agent_id','customSelect',[
                'label' => 'Chọn đại lý',
                'selected' => isset($this->model?->id) ? (int)$this->model?->agent_id : (int)request()->select_id,
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => $listAgent->toArray(),
            ])
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => 'Địa chỉ',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Đia chỉ',
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' =>  __('Mô tả'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __('Mô tả'),
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => AgentStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
