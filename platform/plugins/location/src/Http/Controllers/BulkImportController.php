<?php

namespace Botble\Location\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Base\Supports\Helper;
use Botble\Location\Exports\TemplateLocationExport;
use Botble\Location\Location;
use Botble\Location\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel;

class BulkImportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/location::location.name'))
            ->add(trans('plugins/location::bulk-import.name'), route('location.bulk-import.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/location::bulk-import.name'));

        $mimetypes = collect(config('plugins.location.general.bulk-import.mime_types', []))->implode(',');

        Assets::addScriptsDirectly('vendor/core/plugins/location/js/bulk-import.js')
            ->addScripts('dropzone')
            ->addStyles('dropzone');

        $locations = $this->getAvailableRemoteLocations();

        return view('plugins/location::bulk-import.index', compact('mimetypes', 'locations'));
    }

    public function downloadTemplate(Request $request)
    {
        $extension = $request->input('extension');
        $extension = $extension === 'csv' ? $extension : Excel::XLSX;
        $writeType = $extension === 'csv' ? Excel::CSV : Excel::XLSX;
        $contentType = $extension === 'csv' ? ['Content-Type' => 'text/csv'] : ['Content-Type' => 'text/xlsx'];
        $fileName = 'template_locations_import.' . $extension;

        return (new TemplateLocationExport($extension))->download($fileName, $writeType, $contentType);
    }

    protected function getAvailableRemoteLocations()
    {
        $location = app(Location::class);

        $remoteLocations = $location->getRemoteAvailableLocations();

        $availableLocations = Country::query()->pluck('code')->all();

        $listCountries = Helper::countries();

        $locations = [];

        foreach ($remoteLocations as $location) {
            $location = strtoupper($location);

            if (in_array($location, $availableLocations)) {
                continue;
            }

            foreach ($listCountries as $key => $country) {
                if ($location === strtoupper($key)) {
                    $locations[$location] = $country;
                }
            }
        }

        return array_unique($locations);
    }

    public function importLocationData(Request $request, Location $location)
    {
        $request->validate([
            'country_code' => ['required', 'string', Rule::in(array_keys($this->getAvailableRemoteLocations()))],
        ]);

        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $result = $location->downloadRemoteLocation(strtolower($request->input('country_code')));

        return $this
            ->httpResponse()
            ->setError($result['error'])
            ->setMessage($result['message']);
    }
}
