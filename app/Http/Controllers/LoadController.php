<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoadRequest;
use App\Http\Requests\UpdateLoadRequest;
use App\Models\Load;
use App\Http\Requests\IndexLoadRequest;
use App\Http\Resources\LoadResource;
use App\Http\Concerns\ValidatesETag;

class LoadController extends Controller
{
    use ValidatesETag;
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

        return (new LoadResource($load))
            ->response()
            ->header('ETag', $load->etag());
    }

    public function update(UpdateLoadRequest $req, Load $load)
    {
        $this->authorize('update', $load);

        $this->validateETag($req, $load);

        $load->fill($req->validated());
        $load->version++;
        $load->save();

        return (new LoadResource($load))
            ->response()
            ->header('ETag', $load->etag());
    }

    public function destroy(Load $load)
    {
        $this->authorize('delete', $load);
        $load->delete();
        return response()->noContent();
    }
}
