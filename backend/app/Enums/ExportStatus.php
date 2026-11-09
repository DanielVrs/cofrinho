<?php

namespace App\Enums;

// os três estados possíveis de uma exportação
enum ExportStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Failed = 'failed';
}
