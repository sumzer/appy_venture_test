<?php

namespace App\Http\Controllers;

use App\Enums\LoadStatus;
use App\Http\Requests\StoreLoadRequest;
use App\Http\Requests\UpdateLoadRequest;
use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\IndexLoadRequest;
use App\Http\Resources\LoadResource;

class LoadController extends Controller
{
    protected function assertIfMatch(Request $req, Load $load): void
    {
        $ifMatch = $req->header('If-Match');

        abort_if(!$ifMatch, 428, 'If-Match required');
        abort_if($ifMatch !== $load->etag(), 412, 'ETag mismatch');
    }

    protected function assertEditable(Load $load): void
    {
        $allowed = [LoadStatus::Draft->value, LoadStatus::Open->value];

        abort_if(!in_array($load->status->value, $allowed, true), 422, 'Load not editable in current status.');
    }

    public function index(IndexLoadRequest $req)
    {
        $paginator = Load::query()
            ->filter($req->filters())
            ->orderBy($req->sort(), $req->order())
            ->paginate($req->perPage())
            ->appends($req->query());

        return LoadResource::collection($paginator);
    }

    public function store(StoreLoadRequest $req)
    {
        $load = Load::create([
            ...$req->validated(),
            'shipper_id' => $req->user()->id,
        ]);

        return (new LoadResource($load))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url("/api/loads/{$load->id}"));
    }

    public function show(Load $load)
    {
        $load->load([
            'shipper',
            'acceptedBid',
            'booking',
        ]);

        return new LoadResource($load);
    }

    public function update(UpdateLoadRequest $req, Load $load)
    {
        Gate::authorize('manage', $load);
        $this->assertIfMatch($req, $load);
        $this->assertEditable($load);

        $load->fill($req->validated());
        $load->version++;
        $load->save();

        return (new LoadResource($load))
            ->response()
            ->header('ETag', $load->etag());
    }

    public function destroy(Request $req, Load $load)
    {
        Gate::authorize('manage', $load);

        abort_unless(
            in_array($load->status->value, [LoadStatus::Draft->value, LoadStatus::Open->value, LoadStatus::Closed->value], true),
            422,
            'Load cannot be deleted in current status.'
        );

        $load->delete();
        return response()->noContent();
    }
}
