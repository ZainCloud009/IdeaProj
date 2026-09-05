<x-nav title="Idea">


    {{-- ================= ACTION BAR ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">

        <a href="{{ route('ideas') }}"
            class="group inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-white transition-all">
            <span class="transition-transform group-hover:-translate-x-1">←</span>
            Back to Ideas
        </a>

        <div class="flex items-center gap-3">
            {{-- EDIT --}}
            <button onclick="openEditModal()"
                class="px-6 py-2.5 rounded-xl border border-white/5 bg-white/5 text-sm font-semibold text-gray-300 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all active:scale-95">
                Edit Idea
            </button>

            {{-- DELETE BUTTON --}}
            <button onclick="openDeleteModal()"
                class="px-6 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-sm font-semibold text-red-400 hover:bg-red-500/20 hover:border-red-500/40 transition-all active:scale-95">
                Delete
            </button>
        </div>
    </div>

    {{-- ================= IDEA CARD ================= --}}
    <div class="max-w-4xl">

        @php
            $statusColors = [
                'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                'in progress' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            ];

            $status = strtolower($idea->status->value ?? $idea->status);
            $badgeClass = $statusColors[$status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
        @endphp

        {{-- Main Container with subtle outer glow --}}
        <div class="relative group">
            <div
                class="absolute -inset-px bg-gradient-to-b from-white/10 to-transparent rounded-[2.5rem] pointer-events-none">
            </div>

            <div
                class="relative rounded-[2.5rem] border border-white/10 bg-[#0D0D0D] p-8 md:p-12 shadow-2xl overflow-hidden">

                {{-- Decorative background element --}}
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-white/5 rounded-full blur-[80px] pointer-events-none">
                </div>

                {{-- Status Badge --}}
                <span
                    class="inline-flex items-center gap-1.5 text-[11px] px-4 py-1.5 rounded-full border font-bold uppercase tracking-wider {{ $badgeClass }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                    {{ $idea->status->value ?? $idea->status }}
                </span>

                <h1 class="mt-8 text-4xl md:text-5xl font-black text-white leading-[1.1] tracking-tight">
                    {{ $idea->title }}
                </h1>
                {{-- Idea Image --}}
                @if (!empty($idea->image))
                    <div class="mt-8 overflow-hidden rounded-2xl border border-white/10">
                        <img src="{{ asset('storage/' . $idea->image) }}" alt="{{ $idea->title }}"
                            class="w-full max-h-[420px] object-cover">
                    </div>
                @endif
                <div class="mt-8 space-y-6">
                    <p class="text-lg text-gray-400 leading-relaxed font-light">
                        {{ $idea->description }}
                    </p>
                </div>

                <!-- Actionable Steps -->
                @if ($idea->steps->isNotEmpty())
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold text-white mb-4">Actionable Steps</h2>
                        <div class="space-y-3">
                            @foreach ($idea->steps as $step)
                                <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 cursor-pointer transition-all hover:bg-white/5 view-step-row"
                                    data-step-id="{{ $step->id }}">
                                    <div
                                        class="w-5 h-5 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-colors
                                @if ($step->completed) bg-emerald-500 border-emerald-500 @else border-white/20 @endif">
                                        @if ($step->completed)
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span
                                        class="text-gray-400 @if ($step->completed) line-through text-gray-500 @endif">{{ $step->description }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Links Section -->
                @if (!empty($idea->links))
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold text-white mb-4">Links</h2>
                        <ul class="space-y-2">
                            @foreach ($idea->links as $link)
                                <li>
                                    <a href="{{ $link }}" target="_blank"
                                        class="text-blue-400 hover:text-blue-500 underline break-all">
                                        {{ $link }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div
                    class="mt-12 pt-8 border-t border-white/5 flex flex-wrap items-center gap-6 text-xs font-medium uppercase tracking-widest text-gray-500">
                    <div class="flex items-center gap-2">
                        <span class="text-white/20">Posted</span>
                        <span class="text-gray-400">{{ $idea->created_at?->diffForHumans() }}</span>
                    </div>
                    <div class="h-1 w-1 rounded-full bg-white/20"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-white/20">Reference</span>
                        <span class="text-gray-400">#{{ str_pad($idea->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ================= EDIT MODAL ================= --}}
    <div id="editIdeaModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4">

        <div
            class="w-full max-w-lg rounded-[2rem] border border-white/10 bg-[#0A0A0A] shadow-2xl relative max-h-[90vh] flex flex-col">

            {{-- Header --}}
            <div class="p-8 pb-4">
                <button onclick="closeEditModal()"
                    class="absolute top-6 right-6 text-gray-500 hover:text-white">✕</button>
                <h2 class="text-2xl font-bold text-white">Edit Idea</h2>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto px-8 pb-8">

                <form id="editIdeaForm" method="POST" action="{{ route('ideas.update', $idea) }}"
                    enctype="multipart/form-data" class="space-y-6">

                    @csrf
                    @method('PUT')

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Title *</label>
                        <input type="text" name="title" value="{{ old('$idea->title', $idea->title) }}"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">
                        @error('title')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Status *</label>
                        <select name="status"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white focus:outline-none focus:border-white/30 appearance-none [&>option]:bg-[#0A0A0A]">
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="in progress" @selected($status === 'in progress')>In Progress</option>
                            <option value="completed" @selected($status === 'completed')>Completed</option>
                        </select>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">{{ $idea->description }}</textarea>
                    </div>

                    {{-- ================= LINKS ================= --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-3">
                            Links
                        </label>

                        <div id="editLinksContainer" class="space-y-3">

                            @php
                                $links = $idea->links ?? [];
                            @endphp

                            @forelse($links as $index => $link)
                                <div class="flex gap-2 edit-link-row">
                                    <input type="url" name="links[]" value="{{ $link }}"
                                        placeholder="https://example.com"
                                        class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">

                                    <button type="button" onclick="removeEditLink(this)"
                                        class="px-3 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30">
                                        ✕
                                    </button>
                                </div>
                            @empty
                                {{-- empty state --}}
                                <div class="flex gap-2 edit-link-row">
                                    <input type="url" name="links[]" placeholder="https://example.com"
                                        class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">

                                    <button type="button" onclick="removeEditLink(this)"
                                        class="px-3 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30">
                                        ✕
                                    </button>
                                </div>
                            @endforelse

                        </div>

                        <button type="button" onclick="addEditLink()"
                            class="mt-3 text-sm text-gray-400 hover:text-white">
                            + Add another link
                        </button>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm text-gray-300">Steps</label>

                        <div id="editStepsContainer" class="space-y-2 mt-2">
                            @foreach ($idea->steps as $index => $step)
                                <div class="flex gap-2 step-row">

                                    <input type="hidden" name="steps[{{ $index }}][id]"
                                        value="{{ $step->id }}">

                                    <input type="text" name="steps[{{ $index }}][description]"
                                        value="{{ $step->description }}"
                                        class="w-full px-3 py-2 rounded-lg bg-black/40 border border-white/20 text-white">

                                    <button type="button" onclick="removeStep(this)"
                                        class="px-3 py-2 bg-red-500 text-white rounded-lg">
                                        ✕
                                    </button>

                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addStep()" class="mt-2 text-sm text-yellow-400">
                            + Add Step
                        </button>
                    </div>

                    {{-- IMAGE SECTION --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Image</label>

                        <input type="file" name="image" id="editImageInput" accept="image/*"
                            class="w-full rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">

                        {{-- Hidden remove flag --}}
                        <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                        {{-- Current Image Preview --}}
                        <div id="editImagePreviewWrapper" class="mt-4 {{ $idea->image ? '' : 'hidden' }} relative">

                            <img id="editImagePreview"
                                src="{{ $idea->image ? asset('storage/' . $idea->image) : '' }}"
                                class="w-full h-48 object-cover rounded-xl border border-white/10">

                            {{-- Remove button --}}
                            <button type="button" onclick="removeExistingImage()"
                                class="absolute top-2 right-2 bg-black/70 hover:bg-black text-white rounded-full w-8 h-8 flex items-center justify-center">
                                ✕
                            </button>
                        </div>
                    </div>

                </form>
                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const modal = document.getElementById('editIdeaModal');
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            document.body.style.overflow = 'hidden';
                        });
                    </script>
                @endif
            </div>

            {{-- Footer --}}
            <div class="p-8 pt-4 border-t border-white/5 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()"
                    class="px-5 py-2 rounded-xl text-gray-400 hover:text-white">
                    Cancel
                </button>

                <button type="submit" form="editIdeaForm"
                    class="px-6 py-2 rounded-xl bg-white text-black font-bold hover:bg-gray-200">
                    Update Idea
                </button>
            </div>
        </div>
    </div>

    {{-- ================= DELETE MODAL ================= --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">

        {{-- Backdrop with heavier blur --}}
        <div class="absolute inset-0 bg-black/80 backdrop-blur-md transition-opacity" onclick="closeDeleteModal()">
        </div>

        {{-- Modal Content --}}
        <div
            class="relative w-full max-w-md transform rounded-[2rem] border border-white/10 bg-[#121212] p-8 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] transition-all">

            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-500/10 text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-7 h-7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>

            <h3 class="text-2xl font-bold text-white mb-2">Confirm Delete</h3>
            <p class="text-gray-400 leading-relaxed mb-8">
                Are you sure? This will permanently remove <span
                    class="text-white font-medium">"{{ $idea->title }}"</span>. This action is irreversible.
            </p>

            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button onclick="closeDeleteModal()"
                    class="order-2 sm:order-1 px-6 py-3 rounded-xl border border-white/5 text-sm font-bold text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                    Cancel
                </button>

                <form method="POST" action="{{ route('ideas.destroy', $idea) }}" class="order-1 sm:order-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full px-6 py-3 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-500 shadow-lg shadow-red-900/20 transition-all active:scale-95">
                        Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL SCRIPT ================= --}}
    <script>
        function openDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto'; // Re-enable scroll
        }

        document.querySelectorAll('.view-step-row').forEach(row => {
            row.addEventListener('click', async function() {
                const stepId = this.dataset.stepId;

                // Toggle visually
                const isCompleted = this.querySelector('div').classList.contains('bg-emerald-500');

                const circle = this.querySelector('div');
                const text = this.querySelector('span');

                if (isCompleted) {
                    circle.classList.remove('bg-emerald-500', 'border-emerald-500');
                    text.classList.remove('line-through', 'text-gray-500');
                    circle.innerHTML = '';
                } else {
                    circle.classList.add('bg-emerald-500', 'border-emerald-500');
                    text.classList.add('line-through', 'text-gray-500');
                    circle.innerHTML = `
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
                }

                // Send request to update step
                try {
                    await fetch(`/steps/${stepId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            completed: !isCompleted
                        })
                    });
                } catch (err) {
                    console.error('Failed to update step', err);
                }
            });
        });

        function openEditModal() {
            const modal = document.getElementById('editIdeaModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            const modal = document.getElementById('editIdeaModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // 🔥 remove existing image
        function removeExistingImage() {
            document.getElementById('editImagePreviewWrapper').classList.add('hidden');
            document.getElementById('removeImageFlag').value = '1';
        }

        // 🔥 preview new image
        document.getElementById('editImageInput')?.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.getElementById('editImagePreviewWrapper');
                const img = document.getElementById('editImagePreview');

                img.src = e.target.result;
                wrapper.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        function addEditLink() {
            const container = document.getElementById('editLinksContainer');

            const row = document.createElement('div');
            row.className = 'flex gap-2 edit-link-row';

            row.innerHTML = `
        <input type="url"
               name="links[]"
               placeholder="https://example.com"
               class="flex-1 rounded-xl bg-white/[0.03] border border-white/10 px-4 py-3 text-white">

        <button type="button"
                onclick="removeEditLink(this)"
                class="px-3 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30">
            ✕
        </button>
    `;

            container.appendChild(row);
        }


        function removeEditLink(button) {
            const container = document.getElementById('editLinksContainer');
            const rows = container.querySelectorAll('.edit-link-row');

            // 🔥 keep at least one row
            if (rows.length <= 1) {
                rows[0].querySelector('input').value = '';
                return;
            }

            button.parentElement.remove();
        }
        let stepIndex = {{ $idea->steps->count() }};

        function addStep() {
            const container = document.getElementById('editStepsContainer');

            const html = `
        <div class="flex gap-2 step-row">
            <input type="text"
                   name="steps[${stepIndex}][description]"
                   placeholder="Enter step..."
                   class="w-full px-3 py-2 rounded-lg bg-black/40 border border-white/20 text-white">

            <button type="button"
                    onclick="removeStep(this)"
                    class="px-3 py-2 bg-red-500 text-white rounded-lg">
                ✕
            </button>
        </div>
    `;

            container.insertAdjacentHTML('beforeend', html);
            stepIndex++;
        }

        function removeStep(button) {
            const container = document.getElementById('editStepsContainer');
            const rows = container.querySelectorAll('.step-row');

            // ✅ allow removing last row (FIX your previous bug)
            button.closest('.step-row').remove();

            // optional: keep one empty row if all removed
            if (container.children.length === 0) {
                addStep();
            }
        }
    </script>


</x-nav>
