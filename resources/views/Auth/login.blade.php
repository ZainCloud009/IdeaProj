<x-nav title="Login">
    <div class="max-w-md mx-auto pt-10">
        <div class="bg-[#0D0D0D] border border-white/5 rounded-3xl p-10 shadow-2xl">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-gray-500 text-sm">Enter your credentials to continue</p>
            </div>

            <form method="POST" action="{{ route('storelogin') }}" class="space-y-6">
                @csrf
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
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-red-400 text-xs mt-2 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    class="w-full py-4 rounded-2xl bg-white text-black font-bold hover:bg-gray-200 transition-all active:scale-[0.98] shadow-lg shadow-white/5">
                    Sign In
                </button>
            </form>

            <p class="text-center mt-8 text-sm text-gray-500">
                Don't have an account? <a href="{{ route('register') }}" class="text-white hover:underline">Register</a>
            </p>
        </div>
    </div>
</x-nav>
