<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;
use App\Models\Load;

trait ValidatesETag
{
    protected function validateETag(Request $request, Load $model): void
    {
        $ifMatch = $request->header('If-Match');
        abort_if(!$ifMatch, 428, 'If-Match required');
        abort_if($ifMatch !== $model->etag(), 412, 'ETag mismatch');
    }
}
