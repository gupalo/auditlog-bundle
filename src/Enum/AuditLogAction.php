<?php

namespace Gupalo\AuditLogBundle\Enum;


enum AuditLogAction: string
{
    case Create = 'create';
    case Edit = 'edit';

    case List = 'list';
    case View = 'view';

    case Archive = 'archive';
    case Restore = 'restore';

    case Export = 'export';
    case Login = 'login';
}
