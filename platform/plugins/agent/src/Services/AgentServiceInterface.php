<?php

namespace Botble\Agent\Services;

use Illuminate\Http\Request;

interface AgentServiceInterface
{
    public function execute(Request $request);
}
