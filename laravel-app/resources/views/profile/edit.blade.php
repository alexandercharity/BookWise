@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="relative">
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
            @else
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-3xl font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
            <p class="text-gray-400 text-sm">{{ $user->email }}</p>
            <p class="text-gray-600 text-xs mt-1">Bergabung {{ $user->created_at->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('status') === 'profile-updated')
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-xl text-sm mb-6">
            ✅ Profil berhasil diperbarui.
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-xl text-sm mb-6">
            @foreach($errors->all() as $error)
                <p>❌ {{ $error }}</p>
            @endforeach
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-xl text-sm mb-6">
            ✅ Password berhasil diperbarui.
        </div>
    @endif

    {{-- Edit Profile --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-5">Informasi Profil</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PATCH')

            {{-- Avatar --}}
            <div>
                <label class="block text-sm text-gray-400 mb-2">Foto Profil</label>
                <div class="flex items-center gap-4">
                    @if($user->avatar)
                        <img id="avatar-preview" src="{{ Storage::url($user->avatar) }}" class="w-14 h-14 rounded-full object-cover border border-slate-700">
                        <div id="avatar-placeholder" class="hidden w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @else
                        <img id="avatar-preview" src="" class="hidden w-14 h-14 rounded-full object-cover border border-slate-700">
                        <div id="avatar-placeholder" class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden"
                               onchange="previewAvatar(this)">
                        <label for="avatar"
                               class="cursor-pointer bg-slate-800 hover:bg-slate-700 border border-slate-700 text-gray-300 text-sm px-4 py-2 rounded-xl transition">
                            Pilih Foto
                        </label>
                        <p class="text-gray-600 text-xs mt-1">JPG, PNG, max 2MB</p>
                    </div>
                </div>
                @error('avatar') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm text-gray-400 mb-1.5">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm text-gray-400 mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Genres --}}
            <div>
                <label class="block text-sm text-gray-400 mb-3">Genre Favorit <span class="text-gray-600">(pilih beberapa)</span></label>
                @php
                $genres = [
                    '📚 Fiction'       => 'fiction',
                    '🔮 Fantasy'       => 'fantasy',
                    '🚀 Sci-Fi'        => 'sci-fi',
                    '😱 Horror'        => 'horror',
                    '💕 Romance'       => 'romance',
                    '🔍 Mystery'       => 'mystery',
                    '🕵️ Thriller'      => 'thriller',
                    '📖 Historical'    => 'historical',
                    '😂 Comedy'        => 'comedy',
                    '🌱 Self-Help'     => 'self-help',
                    '🧠 Non-Fiction'   => 'non-fiction',
                    '👶 Children'      => 'children',
                    '💼 Biography'     => 'biography',
                    '🌍 Adventure'     => 'adventure',
                    '💡 Philosophy'    => 'philosophy',
                    '🎭 Drama'         => 'drama',
                ];
                $selected = $user->favorite_genres ?? [];
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($genres as $label => $value)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="genres[]" value="{{ $value }}"
                                   {{ in_array($value, $selected) ? 'checked' : '' }}
                                   class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-gray-300 group-hover:text-white transition">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- Update Password --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-5">Ubah Password</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Password Saat Ini</label>
                <div class="relative">
                    <input id="current_password" type="password" name="current_password"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 pr-12 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="togglePassword('current_password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition">👁</button>
                </div>
                @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Password Baru</label>
                <div class="relative">
                    <input id="password" type="password" name="password"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 pr-12 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition">👁</button>
                </div>
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 pr-12 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition">👁</button>
                </div>
            </div>

            <button type="submit"
                    class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
                Ubah Password
            </button>
        </form>
    </div>

    {{-- Hapus Akun --}}
    <div class="bg-slate-900 border border-red-900/40 rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-red-400 mb-2">Hapus Akun</h2>
        <p class="text-gray-500 text-sm mb-5">Akun yang dihapus tidak bisa dipulihkan.</p>

        <form method="POST" action="{{ route('profile.destroy') }}"
              onsubmit="return confirm('Yakin mau hapus akun? Tindakan ini tidak bisa dibatalkan.')">
            @csrf @method('DELETE')
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm text-gray-400 mb-1.5">Konfirmasi dengan password</label>
                    <input type="password" name="password" placeholder="Password kamu"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                    @error('password', 'userDeletion') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="bg-red-600/20 hover:bg-red-600/40 text-red-400 border border-red-700/50 px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    Hapus Akun
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁' : '🙈';
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('avatar-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            const placeholder = document.getElementById('avatar-placeholder');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
