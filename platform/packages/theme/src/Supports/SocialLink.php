<?php

namespace Botble\Theme\Supports;

use Botble\Base\Facades\BaseHelper;
use Botble\Media\Facades\RvMedia;
use Illuminate\Support\HtmlString;

class SocialLink
{
    public function __construct(
        protected string|null $name,
        protected string|null $url,
        protected string|null $icon,
        protected string|null $image,
    ) {
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function getIcon(): string|null
    {
        return $this->icon;
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }

    public function getImage(): string|null
    {
        return $this->image;
    }

    public function getIconHtml(array $attributes = []): HtmlString|null
    {
        if ($this->image) {
            return RvMedia::image($this->image, $this->name, attributes: $attributes);
        }

        if (! $this->icon) {
            return null;
        }

        if (BaseHelper::hasIcon($this->icon)) {
            $icon = BaseHelper::renderIcon($this->icon, attributes: $attributes);
        } else {
            $icon = sprintf('<i class="%s"></i>', $this->icon);
        }

        return new HtmlString($icon);
    }
}
