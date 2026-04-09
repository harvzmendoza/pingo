<?php

namespace App\Enums;

enum MessageLogStatus: string
{
    case Ongoing = 'ongoing';
    case Sent = 'sent';
    case Failed = 'failed';
}
