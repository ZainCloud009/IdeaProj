<x-nav title="Register">
    <div class="max-w-md mx-auto pt-10">
        <div class="bg-[#0D0D0D] border border-white/5 rounded-3xl p-10 shadow-2xl">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
                <p class="text-gray-500 text-sm">Join the community of thinkers</p>
            </div>

            <form method="POST" action="{{ route('storeregister') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Full
                        Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="John Doe">
                    @error('name')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Email
                        Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="name@company.com">
                    @error('email')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        class="block mb-2 text-xs uppercase tracking-widest font-semibold text-gray-500">Password</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-4 rounded-2xl bg-black border border-white/10 text-white focus:border-white focus:ring-1 focus:ring-white transition outline-none"
                        placeholder="Min. 8 characters">
                    @error('password')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    class="w-full py-4 mt-4 rounded-2xl bg-white text-black font-bold hover:bg-gray-200 transition-all active:scale-[0.98]">
                    Create Account
                </button>
            </form>
        </div>
    </div>
</x-nav>
