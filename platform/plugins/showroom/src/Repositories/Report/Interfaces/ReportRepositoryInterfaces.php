<?php

namespace Botble\Showroom\Repositories\Report\InterFaces;

use Illuminate\Support\Collection;

interface ReportRepositoryInterfaces
{
    public function getFirstShowroomIdByUser($showroomId):int;
    public function filterOrderInShowroomByUser($showroomId):array;
}
