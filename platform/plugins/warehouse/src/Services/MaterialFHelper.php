<?php

namespace Botble\Warehouse\Services;


class MaterialFHelper
{


    public function registerModule(string|array $model, string|null $name = null): self
    {

        if (! is_array($model)) {
            $supported[$model] = $name ?: $model;
        } else {
            foreach ($model as $item) {
                $supported[$item] = $name ?: $item;
            }
        }

        config(['packages.slug.general.supported' => $supported]);

        return $this;
    }
    public function supportedModels(): array
    {
        return config('packages.slug.general.supported', []);
    }
}
