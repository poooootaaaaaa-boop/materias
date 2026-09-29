<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Support\LocationCatalog;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $role = $request->input('role', 'student');

        if (in_array($role, ['parent', 'admin'])) {
            // Registro simplificado para padres y administradores
            $validated = $request->validate([
                'name'                  => ['required', 'string', 'min:2', 'max:80'],
                'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password'              => ['required', 'string', 'min:4', 'confirmed'],
                'role'                  => ['required', 'in:parent,admin'],
            ]);
            $validated['terms_accepted_at'] = now();
            $validated['is_admin'] = ($role === 'admin');
            $validated['age'] = 30;
            $validated['school'] = 'N/A';
            $validated['gender'] = 'prefer_not_to_say';
            $validated['country'] = 'Mexico';
            $validated['state'] = 'Ciudad de Mexico';
        } else {
            // Registro completo para alumnos
            $validated = $request->validate([
                'name'         => ['required', 'string', 'min:2', 'max:18'],
                'age'          => ['required', 'integer', 'min:4', 'max:18'],
                'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'parent_email' => ['required_unless:age,18', 'nullable', 'email', 'different:email'],
                'school'       => ['required', 'string', 'max:160'],
                'gender'       => ['required', 'in:woman,man,other,prefer_not_to_say'],
                'country'      => ['required', 'string', 'max:80'],
                'state'        => ['required', 'string', 'max:120'],
                'terms_accepted' => ['accepted'],
                'password'     => ['required', 'string', 'min:4', 'confirmed'],
            ]);

            $country = LocationCatalog::canonical($validated['country'], LocationCatalog::countries());
            $state = LocationCatalog::canonical($validated['state'], LocationCatalog::states($country ?? ''));
            if (! $country || ! $state) {
                throw ValidationException::withMessages(['country' => ['Selecciona un país y estado válidos de las listas.']]);
            }
            unset($validated['terms_accepted']);
            $validated['country'] = $country;
            $validated['state'] = $state;
            $validated['terms_accepted_at'] = now();
            $parent = null;

if ((int) $validated['age'] < 18) {
    $parent = User::where('email', $validated['parent_email'])
        ->where('role', 'parent')
        ->first();

    if (! $parent) {
        throw ValidationException::withMessages([
            'parent_email' => [
                'No encontramos una cuenta de padre registrada con ese correo.'
            ],
        ]);
    }

    $validated['parent_id'] = $parent->id;
}
        }
        

        $user = User::create($validated);

        return response()->json([
            'user'     => $user,
            'is_admin' => $user->is_admin,
            'role'     => $user->role,
            'token'    => $user->createToken('frontend')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['El correo o la contraseña no son correctos.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'is_admin' => $user->is_admin,
            'role' => $user->role,
            'token' => $user->createToken('frontend')->plainTextToken,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}