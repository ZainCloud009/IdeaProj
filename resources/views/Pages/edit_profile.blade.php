<x-nav title="Edit Profile">
    <div class="max-w-md mx-auto pt-10">
        <div class="bg-[#0D0D0D] border border-white/5 rounded-3xl p-10 shadow-2xl">

            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-white mb-2">Edit Profile</h2>
                <p class="text-gray-500 text-sm">Keep your account details up to date</p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Full
                        Name</label>
                    <input type="text" name="name" value="{{ old('$user->name', $user->name) }}"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="John Doe">
                    @error('name')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Email
                        Address</label>
                    <input type="email" name="email" value="{{ old('$user->email', $user->email) }}"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="name@company.com">
                    @error('email')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <hr class="border-white/10 mb-6">
                    <h2 class="text-xs uppercase tracking-widest font-bold text-gray-400 mb-4">Security Verification
                    </h2>
                </div>

                <div>
                    <label class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Current
                        Password *</label>
                    <input type="password" name="current_password"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="Enter password to confirm changes">
                    @error('current_password')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-4 mt-4 rounded-2xl bg-white text-black font-bold hover:bg-gray-200 transition-all active:scale-[0.98]">
                    Update Profile
                </button>

            </form>
        </div>
    </div>
</x-nav>
