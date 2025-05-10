<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(Request $request): ServiceCollection | JsonResponse
    {
        $services = Service::all();
        return new ServiceCollection($services);
    }

    public function show(int $id): ServiceResource | JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        return new ServiceResource($service);
    }
    
    public function store(StoreServiceRequest $request): ServiceResource
    {
        $validatedData = $request->validated();
        $service = Service::create($validatedData);
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request,int $id): ServiceResource
    {
        $service = Service::findOrFail($id);
        $service->update($request->validated());
        return new ServiceResource($service);
    }

    public function destroy(int $id): JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        Service::destroy($id);
        return response()->json(['message' => 'Service deleted']);
    }
}
