<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

enum ReportMode: string
{
    case Flow = 'flow';

    case Strict = 'strict';
}
