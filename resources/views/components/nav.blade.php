<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Idea</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass {
            background: rgba(5, 5, 5, 0.8);
            backdrop-filter: blur(12px);
        }

        .nav-glow {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="bg-[#050505] text-gray-300 min-h-screen selection:bg-white selection:text-black">

    @php
        // Simple helper to check active route
        $isActive = fn($route) => request()->routeIs($route)
            ? 'text-white font-semibold'
            : 'text-gray-500 hover:text-gray-300';
        $activeDot = '<span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-white rounded-full"></span>';
    @endphp

    <nav class="sticky top-0 z-50 glass border-b border-white/[0.05] nav-glow">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            @guest
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center group-hover:rotate-12 transition-transform duration-300">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white uppercase italic">Idea</span>
                </a>

                <div class="flex items-center gap-4 md:gap-8">
                    <div class="flex items-center gap-4 md:gap-6">
                        <a href="{{ route('login') }}"
                            class="relative text-xs md:text-sm transition {{ $isActive('login') }}">
                            Login
                            {!! request()->routeIs('login') ? $activeDot : '' !!}
                        </a>
                    </div>

                    <div class="h-4 w-[1px] bg-white/10 hidden sm:block"></div>

                    <a href="{{ route('register') }}"
                        class="px-4 py-2 md:px-5 md:py-2 rounded-full bg-white text-black hover:bg-gray-200 transition font-bold text-xs md:text-sm shadow-[0_0_20px_rgba(255,255,255,0.1)] active:scale-95">
                        Register
                    </a>
                </div>
            @endguest

            @auth
                <a href="{{ route('ideas') }}" class="flex items-center gap-2 group">
                    <div
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center group-hover:rotate-12 transition-transform duration-300">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white uppercase italic">Idea</span>
                </a>

                <div class="flex items-center gap-6">
                    <span class="text-sm text-gray-500 italic">
                        Welcome, {{ auth()->user()->name }}
                    </span>

                    <!-- Edit Profile Button -->
                    <a href="{{ route('profile.edit') }}"
                        class="px-4 py-1.5 rounded-full border border-white/20 text-white hover:bg-white hover:text-black transition font-medium text-xs uppercase tracking-widest">
                        Edit Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="px-4 py-1.5 rounded-full border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition font-medium text-xs uppercase tracking-widest">
                            Logout
                        </button>
                    </form>
                </div>
            @endauth


        </div>
    </nav>

    @if (session('success'))
        <div id="success-toast"
            class="fixed top-24 left-1/2 -translate-x-1/2 z-[60] w-full max-w-sm px-4 opacity-0 transition-all duration-500 transform -translate-y-4">

            <div
                class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-4 shadow-[0_0_30px_rgba(255,255,255,0.1)] flex items-center gap-4">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)]">
                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-white text-sm font-semibold tracking-wide">{{ session('success') }}</p>
                </div>
                <button onclick="closeToast()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('success-toast');

                // 1. Show the toast (Fade in & Slide down)
                setTimeout(() => {
                    toast.classList.remove('opacity-0', '-translate-y-4');
                    toast.classList.add('opacity-100', 'translate-y-0');
                }, 100);

                // 2. Automatically hide after 4 seconds
                setTimeout(() => {
                    closeToast();
                }, 4500);
            });

            function closeToast() {
                const toast = document.getElementById('success-toast');
                if (toast) {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', '-translate-y-4');
                    // Remove from DOM after transition ends
                    setTimeout(() => toast.remove(), 200);
                }
            }
        </script>
    @endif

    <main class="max-w-7xl mx-auto px-6 py-12">
        {{ $slot }}
    </main>

</body>

</html>
