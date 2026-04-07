<?php

namespace App\Enums;

enum MessageLogStatus: string
{
    case Sent = 'sent';
    case Failed = 'failed';
}
