<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $roles = Role::orderBy('role_name')->get();
        $guru = Guru::orderBy('nama')->get();
        $kepala = KepalaSekolah::with('guru')->orderBy('nama')->get();
        $siswa = Siswa::orderBy('nama')->get();
        
        return view('profile.edit', [
            'user' => Auth::user(),
            'roles' => $roles,
            'guru' => $guru,
            'kepala' => $kepala,
            'siswa' => $siswa
        ]);
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nip' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'jenis_kelamin' => ['sometimes', 'nullable', 'in:L,P'],
            'foto' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'foto_data' => ['sometimes', 'nullable', 'string'],
        ]);

        // Handle photo upload from file input or base64 data
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            $path = $request->file('foto')->store('user_photos', 'public');
            $user->foto = $path;
        } elseif (! empty($validated['foto_data'])) {
            if (preg_match('/^data:image\/(\w+);base64,(.*)$/', $validated['foto_data'], $matches)) {
                $extension = strtolower($matches[1]);
                $base64 = $matches[2];
                $decoded = base64_decode(str_replace(' ', '+', $base64));

                if ($decoded !== false && strlen($decoded) <= 2 * 1024 * 1024) {
                    if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                        Storage::disk('public')->delete($user->foto);
                    }

                    $filename = 'user_photos/' . uniqid('profile_', true) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
                    Storage::disk('public')->put($filename, $decoded);
                    $user->foto = $filename;
                }
            }
        }

        if ($request->has('name')) {
            $user->name = $validated['name'] ?? $user->name;
        }

        if ($request->has('nip')) {
            $user->nip = $validated['nip'] ?? null;
        }

        if ($request->has('email')) {
            $user->email = $validated['email'] ?? null;
        }

        if ($request->has('jenis_kelamin')) {
            $user->jenis_kelamin = $validated['jenis_kelamin'] ?? $user->jenis_kelamin;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile berhasil diperbarui!');
    }

    /**
     * Show the usage guide
     */
    public function panduan()
    {
        $user = Auth::user();
        $roles = explode(',', $user->roles ?? '');
        
        return view('profile.panduan', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['old_password'], $user->password)) {
            return back()->withErrors(['old_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.edit')->with('password_status', 'Password berhasil diubah!');
    }
}
