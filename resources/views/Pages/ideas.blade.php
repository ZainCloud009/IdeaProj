<x-nav title="Dashboard">

    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tighter">
                Your Ideas
            </h1>
            <p class="text-gray-500 mt-2 text-lg">Managing your creative pipeline.</p>
        </div>

        <button id="openIdeaModal"
            class="flex items-center gap-2 px-6 py-3 bg-white text-black rounded-xl font-bold hover:bg-gray-200 transition active:scale-95 shadow-[0_0_20px_rgba(255,255,255,0.1)]">
            <span>+</span>
            New Idea
        </button>
    </div>

    {{-- ✅ FILTER TABS --}}
    <div class="flex flex-wrap gap-3 mb-10">
        @php
            $tabs = [
                'all' => 'All',
                'pending' => 'Pending',
                'in progress' => 'In Progress',
                'completed' => 'Completed',
            ];
        @endphp

        @foreach ($tabs as $key => $label)
            <a href="{{ route('ideas', ['status' => $key]) }}"
                class="px-5 py-2 rounded-xl text-sm font-semibold border transition
               {{ $filter === $key
                   ? 'bg-white text-black border-white'
                   : 'bg-white/5 text-gray-400 border-white/10 hover:border-white/30 hover:text-white' }}">
                {{ $label }}
                <span class="ml-2 text-xs opacity-70">({{ $counts[$key] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    {{-- ✅ EMPTY STATE OR GRID --}}
    @if ($ideas->isEmpty())
        <div
            class="relative group overflow-hidden text-center py-32 border border-white/5 rounded-[2rem] bg-gradient-to-b from-white/[0.03] to-transparent">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/[0.05] via-transparent to-transparent opacity-50">
            </div>
            <div class="relative z-10">
                <div
                    class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 border border-white/10">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-white mb-2">
                    No {{ $filter !== 'all' ? $filter : '' }} ideas yet
                </h3>

                <p class="text-gray-500 max-w-xs mx-auto">
                    Try switching filters or create a new idea.
                </p>
            </div>
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($ideas as $idea)
                @php
                    $statusColors = [
                        'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                        'in progress' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    ];
                    $status = strtolower($idea->status->value ?? $idea->status);
                    $badgeClass = $statusColors[$status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                @endphp

                <a href="{{ route('ideas.show', $idea) }}" class="block">
                    <div
                        class="group relative flex flex-col h-full rounded-[2rem] border border-white/[0.08] bg-[#0A0A0A] p-8 transition-all duration-500 hover:border-white/30 hover:shadow-[0_20px_50px_rgba(0,0,0,0.7)]">
                        <div
                            class="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-white/[0.03] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        </div>
                        <div class="relative z-10 flex flex-col h-full">
                            {{-- Idea Image --}}
                            @if (!empty($idea->image))
                                <div class="mb-6 overflow-hidden rounded-xl border border-white/10">
                                    <img src="{{ asset('storage/' . $idea->image) }}" alt="{{ $idea->title }}"
                                        class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @endif
                            <div class="flex justify-between items-start mb-6">
                                <span
                                    class="text-[10px] px-3 py-1 rounded-full border font-bold uppercase tracking-[0.15em] {{ $badgeClass }}">
                                    {{ $idea->status->value ?? $idea->status }}
                                </span>

                                <div class="text-gray-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 text-left">
                                <h2
                                    class="text-xl font-bold text-white leading-tight mb-3 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-white group-hover:to-gray-400 transition-all duration-300">
                                    {{ $idea->title }}
                                </h2>

                                <p
                                    class="text-gray-400 text-sm leading-relaxed line-clamp-3 group-hover:text-gray-300 transition-colors">
                                    {{ $idea->description }}
                                </p>
                            </div>

                            <div class="mt-8 pt-6 border-t border-white/[0.05] flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-2 h-2 rounded-full bg-white/20 group-hover:bg-white transition-colors">
                                    </div>
                                    <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $idea->created_at?->diffForHumans() ?? 'Just now' }}
                                    </span>
                                </div>

                                <div
                                    class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-x-2 group-hover:translate-x-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </div>

                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif


    <!-- ================= NEW IDEA MODAL ================= -->
    <div id="ideaModal" role="dialog" aria-modal="true" aria-labelledby="ideaModalTitle"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm overflow-hidden p-4">

        <div
            class="w-full max-w-lg flex flex-col max-h-[90vh] rounded-[2rem] border border-white/10 bg-[#0A0A0A] shadow-2xl relative">

            <div class="p-8 pb-4">
                <button id="closeIdeaModal" aria-label="Close modal"
                    class="absolute top-6 right-6 text-gray-500 hover:text-white transition-colors">✕</button>
                <h2 id="ideaModalTitle" class="text-2xl font-bold text-white">Create New Idea</h2>
            </div>

            <div
                class="flex-1 overflow-y-auto px-8 pb-8 
                    scrollbar-thin 
                    scrollbar-track-transparent 
                    scrollbar-thumb-white/10 
                    hover:scrollbar-thumb-white/20 
                    scrollbar-thumb-rounded-full">

                <form id="ideaForm" method="POST" action="{{ route('ideas.store') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Title
                            *</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Enter idea title"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 focus:bg-white/[0.05] transition-all">
                        @error('title')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status
                            *</label>
                        <div class="relative">
                            <select name="status"
                                class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 appearance-none [&>option]:bg-[#0A0A0A]">
                                <option value="">Select status</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in progress" {{ old('status') == 'in progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                            </select>
                        </div>
                        @error('status')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</label>
                        <textarea name="description" rows="4" placeholder="Optional description..."
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 resize-none">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Image
                        </label>

                        <input type="file" name="image" id="imageInput" accept="image/*"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white
               focus:outline-none focus:border-white/30">

                        @error('image')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror

                        <!-- Preview -->
                        <div id="imagePreviewWrapper" class="mt-4 hidden">
                            <img id="imagePreview" class="w-full h-48 object-cover rounded-xl border border-white/10">
                        </div>
                    </div>

                    <!-- Steps -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Steps</label>
                        <div id="stepsWrapper" class="space-y-3">
                            <div class="flex gap-2 step-row">
                                <input type="text" name="steps[]" placeholder="Describe this step"
                                    class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 focus:bg-white/[0.05] transition-all">
                                <button type="button"
                                    class="remove-step px-3 rounded-xl border border-red-500/20 text-red-400/50 hover:text-red-400 hover:bg-red-500/10 transition-all">✕</button>
                            </div>
                        </div>
                        <button type="button" id="addStepBtn"
                            class="mt-4 text-xs font-bold text-gray-500 hover:text-white transition-colors underline underline-offset-4">+
                            Add another step</button>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Links</label>
                        <div id="linksWrapper" class="space-y-3">
                            <div class="flex gap-2 link-row">
                                <input type="url" name="links[]" placeholder="https://example.com"
                                    class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30">
                                <button type="button"
                                    class="remove-link px-3 rounded-xl border border-red-500/20 text-red-400/50 hover:text-red-400 hover:bg-red-500/10 transition-all">✕</button>
                            </div>
                        </div>
                        <button type="button" id="addLinkBtn"
                            class="mt-4 text-xs font-bold text-gray-500 hover:text-white transition-colors underline underline-offset-4">+
                            Add another link</button>
                    </div>
                </form>
            </div>

            <div class="p-8 pt-4 border-t border-white/5 flex justify-end gap-3">
                <button type="button" id="cancelIdeaModal"
                    class="px-5 py-2 rounded-xl text-gray-400 hover:text-white transition-colors">Cancel</button>
                <button type="submit" form="ideaForm"
                    class="px-6 py-2 rounded-xl bg-white text-black font-bold hover:bg-gray-200 active:scale-95 transition-all">
                    Create Idea
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('ideaModal');
            const openBtn = document.getElementById('openIdeaModal');
            const closeBtn = document.getElementById('closeIdeaModal');
            const cancelBtn = document.getElementById('cancelIdeaModal');
            const linksWrapper = document.getElementById('linksWrapper');
            const addLinkBtn = document.getElementById('addLinkBtn');
            const stepsWrapper = document.getElementById('stepsWrapper');
            const addStepBtn = document.getElementById('addStepBtn');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            openBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);

            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeModal();
            });

            addLinkBtn?.addEventListener('click', function() {
                const div = document.createElement('div');
                div.className = 'flex gap-2 link-row';
                div.innerHTML = `
                    <input type="url" name="links[]" placeholder="https://example.com" class="flex-1 rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30">
                    <button type="button" class="remove-link px-3 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10">✕</button>
                `;
                linksWrapper.appendChild(div);
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-link')) {
                    e.target.closest('.link-row').remove();
                }
            });
            // Add new step
            addStepBtn?.addEventListener('click', function() {
                const div = document.createElement('div');
                div.className = 'flex gap-2 step-row';
                div.innerHTML = `
        <input type="text" name="steps[]" placeholder="Describe this step" class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 focus:bg-white/[0.05] transition-all">
        <button type="button" class="remove-step px-3 rounded-xl border border-red-500/20 text-red-400/50 hover:text-red-400 hover:bg-red-500/10 transition-all">✕</button>
    `;
                stepsWrapper.appendChild(div);
            });

            // Remove step
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-step')) {
                    e.target.closest('.step-row').remove();
                }
            });

            // Image preview
            const imageInput = document.getElementById('imageInput');
            const previewWrapper = document.getElementById('imagePreviewWrapper');
            const previewImage = document.getElementById('imagePreview');

            imageInput?.addEventListener('change', function() {
                const file = this.files[0];

                if (!file) {
                    previewWrapper.classList.add('hidden');
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewWrapper.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            });

            @if ($errors->any())
                openModal();
            @endif
        });
    </script>
</x-nav>
