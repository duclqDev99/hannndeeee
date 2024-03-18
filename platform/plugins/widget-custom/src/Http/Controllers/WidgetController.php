<?php

namespace Botble\WidgetCustom\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\WidgetCustom\Events\RenderingWidgetSettings;
use Botble\WidgetCustom\Facades\WidgetGroup;
use Botble\WidgetCustom\Models\Widget;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WidgetController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('packages/theme::theme.appearance'))
            ->add(trans('packages/widget::widget.name'), route('widgets-custom.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('packages/widget::widget.name'));

        Assets::addScripts(['sortable'])
            ->addScriptsDirectly('vendor/core/plugins/widget-custom/js/widget.js')
            ->addStylesDirectly('vendor/core/plugins/widget-custom/css/widget.css');

        RenderingWidgetSettings::dispatch();

        $widgets = Widget::query()->where('theme', Widget::getThemeName())->get();

        $groups = WidgetGroup::getGroups();
        
        foreach ($widgets as $widget) {
            if (! Arr::has($groups, $widget->sidebar_id)) {
                continue;
            }
            WidgetGroup::group($widget->sidebar_id)
            ->position($widget->position)
            ->addWidget($widget->widget_id, $widget->data);
        }

        return view('plugins/widget-custom::list');
    }

    public function update(Request $request)
    {
        try {
            $sidebarId = $request->input('sidebar_id');

            $themeName = Widget::getThemeName();

            Widget::query()->where([
                'sidebar_id' => $sidebarId,
                'theme' => $themeName,
            ])->delete();

            foreach (array_filter($request->input('items', [])) as $key => $item) {

                parse_str($item, $data);

                if (empty($data['id'])) {
                    continue;
                }

                Widget::query()->create([
                    'sidebar_id' => $sidebarId,
                    'widget_id' => $data['id'],
                    'theme' => $themeName,
                    'position' => $key,
                    'data' => $data,
                ]);
            }

            $widgetAreas = Widget::query()->where([
                'sidebar_id' => $sidebarId,
                'theme' => $themeName,
            ])->get();

            return $this
                ->httpResponse()
                ->setData(view('plugins/widget-custom::item', compact('widgetAreas'))->render())
                ->setMessage(trans('plugins/widget-custom::widget.save_success'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            Widget::query()->where([
                'theme' => Widget::getThemeName(),
                'sidebar_id' => $request->input('sidebar_id'),
                'position' => $request->input('position'),
                'widget_id' => $request->input('widget_id'),
            ])->delete();

            $sidebarId = $request->input('sidebar_id');

            $themeName = Widget::getThemeName();

            $widgetAreas = Widget::query()->where([
                'sidebar_id' => $sidebarId,
                'theme' => $themeName,
            ])->get();

            return $this
                ->httpResponse()
                ->setData(view('plugins/widget-custom::item', compact('widgetAreas'))->render())
                ->setMessage(trans('plugins/widget-custom::widget.delete_success'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
