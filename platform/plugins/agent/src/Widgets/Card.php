<?php

namespace Botble\Agent\Widgets;

use Botble\Base\Widgets\Widget;

abstract class Card extends Widget
{
    protected string $view = 'card';

    protected string $chartColor = '#4ade80';

    public function __construct()
    {
        parent::__construct();
    }

    public function getOptions(): array
    {
        return [];
    }

    public function options(): array
    {
        return [
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'tooltip' => [
                'enabled' => false,
            ],
            'chart' => [
                'height' => 20,
                'toolbar' => [
                    'show' => false,
                ],
                'sparkline' => [
                    'enabled' => true,
                ],
                'type' => 'area',
            ],
            'colors' => [$this->chartColor],
            'series' => [],
        ];
    }

    public function getColumns(): int
    {
        return 3;
    }

    public function getContent(): string|null
    {
        return null;
    }

    public function getViewData(): array
    {
        $options = $this->options() ? array_merge($this->options(), $this->getOptions()) : null;
        $hasChart = $options && (count($options['series'][0]['data']) > 1);

        return array_merge(parent::getViewData(), [
            'content' => $this->getContent(),
            'columns' => $this->getColumns(),
            'chart' => $this->chart ?? null,
            'options' => $options,
            'hasChart' => $hasChart,
        ]);
    }
}
