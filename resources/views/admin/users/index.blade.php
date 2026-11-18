@extends('layouts.internal')

@section('title', 'Akun Staff — Admin GOR Futsal Nusantara')

@section('content')
<div x-data="{ editUser: null }">

    <div class="mb-6">
        <span class="block text-xs font-bold uppercase tracking-wider text-accent">HAK AKSES INTERNAL</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">Manajemen Akun Admin & Kasir</h1>
    </div>

    <div class="wf-box rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-bold text-ink mb-4">+ Tambah Akun Staf Baru</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold mb-1">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Nama staf" class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div>
                <label class="block font-bold mb-1">Email Login *</label>
                <input type="email" name="email" required placeholder="staf@gor.test" class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div>
                <label class="block font-bold mb-1">Password *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div>
                <label class="block font-bold mb-1">Role Akses *</label>
                <select name="role" class="w-full rounded-xl border-ink/25 text-xs">
                    <option value="kasir">Kasir Operasional</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-paper font-bold">
                    Simpan Akun Staf
                </button>
            </div>
        </form>
    </div>

    <div class="wf-box rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-ink/15 bg-sand/65 font-bold text-ink">
                        <th class="py-3.5 px-4">Nama Staf</th>
                        <th class="py-3.5 px-4">Email Login</th>
                        <th class="py-3.5 px-4">Role Akses</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10 bg-paper">
                    @foreach($users as $u)
                        @php
                            $uPayload = [
                                'id'         => $u->id,
                                'name'       => $u->name,
                                'email'      => $u->email,
                                'role'       => $u->role,
                                'is_self'    => $u->id === Auth::guard('admin')->id(),
                                'update_url' => route('admin.users.update', $u),
                            ];
                        @endphp
                        <tr class="hover:bg-cream/40">
                            <td class="py-3.5 px-4 font-bold text-ink">{{ $u->name }}</td>
                            <td class="py-3.5 px-4 text-ink/80">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $u->role === 'admin' ? 'bg-ink text-paper' : 'bg-amber-200 text-amber-950' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <button type="button" @click='editUser = @json($uPayload)'
                                            class="px-3 py-1 rounded-lg bg-cream hover:bg-sand border border-ink/20 font-bold text-ink">
                                        Edit / Reset Password
                                    </button>

                                    @if($u->id !== Auth::guard('admin')->id())
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun staf ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-bold text-rose-700 hover:underline">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-ink/50 text-[11px] font-medium">Akun Anda</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    
    <div x-show="editUser" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="editUser = null" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Edit Akun & Reset Password</h3>
                <button type="button" @click="editUser = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="editUser">
                <form :action="editUser.update_url" method="POST" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-bold mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Email Login *</label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Role Akses *</label>
                        <select name="role" x-model="editUser.role" class="w-full rounded-xl border-ink/25 text-xs">
                            <option value="kasir">Kasir Operasional</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Password Baru (Opsional — kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" minlength="6" placeholder="Minimal 6 karakter"
                               class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editUser = null" class="px-4 py-2 rounded-xl bg-cream border border-ink/20 font-bold">Batal</button>
                        <button type="submit" class="wf-btn px-5 py-2 rounded-xl bg-accent text-paper font-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
