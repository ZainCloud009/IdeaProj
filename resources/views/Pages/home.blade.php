<x-nav title="Home">
    <div class="relative py-32 flex flex-col items-center text-center">
        <div
            class="absolute top-0 -z-10 h-full w-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/5 via-transparent to-transparent blur-3xl">
        </div>

        <h1
            class="text-6xl md:text-8xl font-extrabold tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500 mb-6">
            Think. Build. <br>Create.
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl mb-10 leading-relaxed">
            The minimal space to capture your most ambitious thoughts. Simple, dark, and focused.
        </p>
        <div class="flex gap-4">
            <a href="{{ route('register') }}"
                class="px-8 py-4 bg-white text-black rounded-full font-bold hover:scale-105 transition">Start
                Building</a>
        </div>
    </div>
</x-nav>
