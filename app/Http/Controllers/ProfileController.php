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
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            
            $path = $request->file('foto')->store('user_photos', 'public');
            $user->foto = $path;
        }

        $user->name = $validated['name'];
        $user->nip = $validated['nip'];
        $user->email = $validated['email'];
        $user->jenis_kelamin = $validated['jenis_kelamin'];
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile berhasil diperbarui!');
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
