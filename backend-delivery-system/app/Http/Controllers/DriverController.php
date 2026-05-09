<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\StoreDriverRequest;
use App\Models\Driver;
use App\Models\DriverLocationLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->adminOnly();

        $query = Driver::with('user')->withCount([
            'orders',
            'orders as completed_orders_count' => fn ($q) => $q->where('status', 'delivered'),
        ]);

        if ($request->status) $query->where('status', $request->status);

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 15)]);
    }

    public function store(StoreDriverRequest $request): JsonResponse
    {
        $this->adminOnly();

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'role'      => 'driver',
            'is_active' => true,
        ]);

        // Assign Spatie 'driver' role so role middleware works correctly
        $driverRole = Role::where('name', 'driver')->where('guard_name', 'api')->first();
        if ($driverRole) {
            $user->syncRoles([$driverRole]);
        }

        $driver = Driver::create([
            'user_id'        => $user->id,
            'license_number' => $request->license_number,
            'vehicle_type'   => $request->vehicle_type,
            'vehicle_plate'  => $request->vehicle_plate,
            'status'         => 'offline',
        ]);

        return response()->json(['success' => true, 'message' => 'Driver created successfully.', 'data' => $driver->load('user')], 201);
    }

    public function show(Driver $driver): JsonResponse
    {
        $driver->load([
            'user',
            'orders'             => fn ($q) => $q->latest()->limit(10),
            'performanceReports' => fn ($q) => $q->latest('period_date')->limit(7),
        ]);

        return response()->json(['success' => true, 'data' => $driver]);
    }

    public function updateStatus(Request $request, Driver $driver): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'driver' && $user->driver?->id !== $driver->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate(['status' => 'required|in:available,on_delivery,offline']);
        $driver->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Driver status updated.', 'data' => $driver->fresh('user')]);
    }

    public function liveLocations(): JsonResponse
    {
        $this->adminOnly();

        $drivers = Driver::with('user')
            ->whereIn('status', ['available', 'on_delivery'])
            ->whereNotNull('current_lat')
            ->get()
            ->map(fn ($d) => [
                'id'           => $d->id,
                'name'         => $d->user->name,
                'status'       => $d->status,
                'vehicle'      => $d->vehicle_plate,
                'lat'          => $d->current_lat,
                'lng'          => $d->current_lng,
                'last_updated' => $d->last_location_at,
            ]);

        return response()->json(['success' => true, 'data' => $drivers]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'lat'               => 'required|numeric|between:-90,90',
            'lng'               => 'required|numeric|between:-180,180',
            'speed_kmh'         => 'nullable|numeric|min:0',
            'heading'           => 'nullable|numeric|between:0,360',
            'accuracy'          => 'nullable|numeric|min:0',
            'delivery_order_id' => 'nullable|exists:delivery_orders,id',
        ]);

        $driver = auth()->user()->driver;

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver profile not found.'], 404);
        }

        $driver->update(['current_lat' => $request->lat, 'current_lng' => $request->lng, 'last_location_at' => now()]);

        DriverLocationLog::create([
            'driver_id'         => $driver->id,
            'delivery_order_id' => $request->delivery_order_id,
            'lat'               => $request->lat,
            'lng'               => $request->lng,
            'speed_kmh'         => $request->speed_kmh ?? 0,
            'heading'           => $request->heading,
            'accuracy'          => $request->accuracy,
            'is_stopped'        => ($request->speed_kmh ?? 0) < 2,
            'logged_at'         => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Location updated.']);
    }

    public function locationHistory(Request $request, Driver $driver): JsonResponse
    {
        $this->adminOnly();

        $request->validate(['order_id' => 'nullable|exists:delivery_orders,id', 'date' => 'nullable|date']);

        $query = DriverLocationLog::where('driver_id', $driver->id)->orderBy('logged_at');

        if ($request->order_id) $query->where('delivery_order_id', $request->order_id);
        if ($request->date)     $query->whereDate('logged_at', $request->date);

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    private function adminOnly(): void
    {
        $user = auth()->user();
        // Check both the role column and the Spatie role assignment
        $isAdmin = $user->role === 'admin' || $user->hasRole('admin');
        abort_if(! $isAdmin, 403, 'Admin access required.');
    }
}
