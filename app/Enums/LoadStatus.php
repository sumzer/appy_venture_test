<?php
namespace App\Enums;
enum LoadStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Booked = 'booked';
    case Closed = 'closed';
}
