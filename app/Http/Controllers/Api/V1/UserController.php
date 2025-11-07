<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseCode;
use App\Helpers\ActivityHelper;
use App\Helpers\CodeHelper;
use App\Helpers\LogHelper;
use App\Helpers\NumberHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\IndexRequest;
use App\Http\Requests\Api\V1\User\ListRequest;
use App\Http\Requests\Api\V1\User\StoreFcmTokenRequest;
use App\Http\Requests\Api\V1\User\StoreRequest;
use App\Http\Requests\Api\V1\User\UpdateActiveRequest;
use App\Http\Requests\Api\V1\User\UpdatePermissionRequest;
use App\Http\Requests\Api\V1\User\UpdateRequest;
use App\Models\ActivityLogMessage;
use App\Models\GuestSession;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UserController extends Controller
{
    public function list(ListRequest $request)
    {
        $roleId = $request->whenFilled('roleCode', function (string $input) {
            return DB::table('user_roles')->where('code', $input)->value('id');
        }, function () {
            return null;
        });

        $users = DB::table('users')
            ->select([
                'users.id',
                'users.code',
                'users.name',
                'user_roles.name as role',
                'users.profile_image as profileImage'
            ])
            ->join('user_roles', 'users.role_id', '=', 'user_roles.id')
            ->where('users.is_active', 1)
            ->when(isset($roleId), function (QueryBuilder $query) use ($roleId) {
                $query->where('users.role_id', $roleId);
            })
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'code' => $item->code,
                    'name' => $item->name,
                    'role' => $item->role,
                    'profileImage' => Storage::url($item->profileImage),
                ];
            });


        return ResponseHelper::generate(ResponseCode::Ok, [
            'items' => $users
        ]);
    }

    public function index(IndexRequest $request)
    {
        $roleId = $request->whenFilled('roleCode', function (string $input) {
            return DB::table('user_roles')->where('code', $input)->value('id');
        }, function () {
            return null;
        });

        $tenantId = $request->whenFilled('tenantCode', function (string $input) {
            return DB::table('tenants')->where('code', $input)->value('id');
        }, function () {
            return null;
        });

        $users = User::query()
            ->select([
                'code',
                'name',
                'email',
                'profile_image',
                'role_id',
                'is_active',
                'created_at'
            ])
            ->with('role:id,code,name')
            ->when(isset($roleId), function (Builder $query) use ($roleId) {
                $query->where('role_id', $roleId);
            })
            ->when(isset($tenantId), function (Builder $query) use ($tenantId) {
                $query->whereHas('tenants', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                });
            })
            ->when($request->filled('searchQuery'), function (Builder $query) use ($request) {
                $query->where($request->searchBy, 'ilike', "%{$request->searchQuery}%");
            })
            ->orderBy($request->sortBy, $request->sortDirection)
            ->orderBy('id', $request->sortDirection)
            ->paginate($request->perPage)
            ->through(function ($item) {
                return [
                    'code' => $item->code,
                    'name' => $item->name,
                    'email' => $item->email,
                    'isActive' => $item->is_active,
                    'profileImage' => $item->profile_image,
                    'role' => [
                        'code' => $item->role->code,
                        'name' => $item->role->name
                    ],
                    'createdAt' => $item->created_at
                ];
            });

        return ResponseHelper::paginate(
            items: $users->items(),
            currentPage: $users->currentPage(),
            lastPage: $users->lastPage(),
            perPage: $users->perPage(),
            total: $users->total()
        );
    }

    public function store(StoreRequest $request)
    {
        $roleId = DB::table('user_roles')->where('code', $request->roleCode)->value('id');

        DB::beginTransaction();

        try {
            $tenantId = null;
            if (!is_null($request->tenantCode)) {
                $tenantId = Tenant::where('code', $request->tenantCode)
                    ->where('is_active', '1')
                    ->whereNotNull('verifier_id')
                    ->whereNotNull('verified_at')
                    ->value('id');

                if (is_null($tenantId)) {
                    return ResponseHelper::generate(responseCode: ResponseCode::InvalidTenant);
                }
            }

            $existingEmail = User::where('email', $request->email)
                ->whereHas('tenants', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                })
                ->first();

            if ($existingEmail) {
                return ResponseHelper::generate(ResponseCode::InvalidUser, message: "Email already used");
            }

            if ($roleId == UserRole::SLS) {
                do {
                    $code = CodeHelper::generateReferralCode();
                } while (DB::table('users')->where('code', $code)->exists());
            } else {
                do {
                    $code = NumberHelper::randomDigit(6);
                } while (DB::table('users')->where('code', $code)->exists());
            }

            $user = User::create(array_merge(
                $request->safe()->only(['name', 'email', 'phone', 'address']),
                [
                    'code' => $code,
                    'password' => bcrypt('password'),
                    'profile_image' => 'assets/images/user-profiles/' . rand(1, 10) . '.jpg',
                    'role_id' => $roleId,
                ]
            ));

            if ($tenantId) {
                $user->tenants()->attach($tenantId, ['is_active' => 1]);
            }

            $user->permissions()->attach(PermissionHelper::defaultPermissions($roleId));

            ActivityHelper::create(message: ActivityLogMessage::CREATE_USER, model: $user);
            DB::commit();
        } catch (Throwable $th) {
            LogHelper::create(LogHelper::GENERAL, 'Create user', LogHelper::context($th));
            DB::rollBack();
            return ResponseHelper::generate(ResponseCode::GeneralError);
        }

        return ResponseHelper::generate(ResponseCode::Ok, message: 'User has been created');
    }

    public function update(UpdateRequest $request, string $code)
    {
        $user = User::where('code', $code)->first();

        if (is_null($user)) {
            return ResponseHelper::generate(ResponseCode::InvalidUser);
        }

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $request->name,
                'password' => $request->filled('password') ? bcrypt($request->password) : $user->password
            ]);

            if (!$user->is_active) {
                $user->tokens()->delete();
            }

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            LogHelper::create(LogHelper::GENERAL, 'Update user', LogHelper::context($th));
            return ResponseHelper::generate(ResponseCode::GeneralError);
        }

        ActivityHelper::create(message: ActivityLogMessage::EDIT_USER, model: $user);

        return ResponseHelper::generate(responseCode: ResponseCode::Ok, message: 'User has been updated');
    }

    public function updateActive(UpdateActiveRequest $request, string $code)
    {
        $user = User::where('code', $code)->first();

        if (is_null($user)) {
            return ResponseHelper::generate(ResponseCode::InvalidUser);
        }

        DB::beginTransaction();

        try {
            $user->update([
                'is_active' => $request->isActive
            ]);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            LogHelper::create(LogHelper::GENERAL, 'Update User Active Status', LogHelper::context($th));
            return ResponseHelper::generate(ResponseCode::GeneralError);
        }

        ActivityHelper::create(message: ActivityLogMessage::SET_ACTIVE_USER, model: $user);

        return ResponseHelper::generate(
            responseCode: ResponseCode::Ok,
            message: 'User active status updated successfully',
        );
    }

    public function getPermissions(string $code)
    {
        $userId = DB::table('users')->where('code', $code)->value('id');

        if (is_null($userId)) {
            return ResponseHelper::generate(ResponseCode::InvalidUser);
        }

        $activePermissionIds = DB::table('user_permission')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();

        $permissions = PermissionGroup::query()
            ->select(['id', 'name'])
            ->with(['permissions' => function ($query) use ($activePermissionIds) {
                $query->select(['id', 'code', 'name', 'description', 'group_id'])
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->filter(fn($group) => $group->permissions->isNotEmpty())
            ->transform(function ($item) use ($activePermissionIds) {
                return [
                    'group' => $item->name,
                    'permissions' => $item->permissions->transform(function ($item) use ($activePermissionIds) {
                        return [
                            'code' => $item->code,
                            'name' => $item->name,
                            'description' => $item->description,
                            'is_active' => in_array($item->id, $activePermissionIds)
                        ];
                    })
                ];
            })->values();

        return ResponseHelper::generate(ResponseCode::Ok, ['items' => $permissions]);
    }

    public function updatePermissions(UpdatePermissionRequest $request, $code)
    {
        $user = User::where('code', $code)->first();

        if (is_null($user)) {
            return ResponseHelper::generate(ResponseCode::InvalidUser);
        }

        $permissionIds = DB::table('permissions')->whereIn('code', $request->permissionCodes)->pluck('id');

        $user->permissions()->sync($permissionIds);

        return ResponseHelper::generate(responseCode: ResponseCode::Ok, message: 'Permission has been updated');
    }

    public function storeFcmToken(StoreFcmTokenRequest $request, $code)
    {
        if ($request->type === 'GUEST') {
            $user = GuestSession::query()
                ->where('token', $code)
                ->first();
        } else {
            $user = User::query()
                ->where('is_active', '1')
                ->where('code', $code)
                ->first();
        }

        if (is_null($user)) {
            return ResponseHelper::generate(ResponseCode::InvalidUser);
        }

        $user->update([
            'fcm_token' => $request->fcmToken
        ]);

        return ResponseHelper::generate(responseCode: ResponseCode::Ok);
    }
}
