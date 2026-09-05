<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\IdeaStatus;
use App\Models\Idea;
use App\Models\User;
use App\Notifications\IdeaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

class IdeaController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $filter = $request->get('status', 'all');

        // ✅ allowed filters
        $allowed = array_merge(
            ['all'],
            array_column(IdeaStatus::cases(), 'value')
        );

        // 🚨 reject invalid status
        if (! in_array($filter, $allowed, true)) {
            return redirect()->route('ideas'); // fallback to all
        }

        $query = Idea::where('user_id', $userId);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $ideas = $query->latest()->get();

        $counts = [
            'all' => Idea::where('user_id', $userId)->count(),
            'pending' => Idea::where('user_id', $userId)->where('status', IdeaStatus::Pending)->count(),
            'in progress' => Idea::where('user_id', $userId)->where('status', IdeaStatus::InProgress)->count(),
            'completed' => Idea::where('user_id', $userId)->where('status', IdeaStatus::Completed)->count(),
        ];

        return view('Pages.ideas', ['ideas' => $ideas, 'filter' => $filter, 'counts' => $counts]);
    }

    public function show(Idea $idea)
    {
        // security: user can only view own idea
        abort_if($idea->user_id !== Auth::id(), 403);

        $idea->load('steps');

        return view('Pages.ideas_show', ['idea' => $idea]);
    }

    public function destroy(Idea $idea)
    {
        abort_if($idea->user_id !== Auth::id(), 403);

        $idea->delete();

        return redirect()
            ->route('ideas')
            ->with('success', 'Idea deleted successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(IdeaStatus::class)],
            'description' => ['nullable', 'string'],
            'links' => ['nullable', 'array'],
            'links.*' => ['nullable', 'url'],
            'steps' => ['nullable', 'array'],
            'steps.*' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ideas', 'public');
        }

        $idea = Idea::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'status' => $validated['status'],
            'description' => $validated['description'],
            'links' => $validated['links'] ?? [],
            'image' => $imagePath,
        ]);

        // Save steps into steps table
        if (! empty($validated['steps'])) {
            foreach ($validated['steps'] as $stepDesc) {
                $idea->steps()->create([
                    'description' => $stepDesc,
                    'completed' => false,
                ]);
            }
        }
        Auth::user()->notify(new IdeaNotification($idea));

        return redirect()
            ->route('ideas')
            ->with('success', 'Idea created successfully.');
    }

    public function update(Request $request, Idea $idea)
    {
        abort_if($idea->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(IdeaStatus::class)],
            'description' => ['nullable', 'string'],
            'links' => ['nullable', 'array'],
            'links.*' => ['nullable', 'url'],
            'steps' => ['nullable', 'array'],
            'steps.*.id' => ['nullable', 'exists:steps,id'],
            'steps.*.description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        // ================= IMAGE =================
        $imagePath = $idea->image;

        if ($request->boolean('remove_image')) {
            if ($idea->image && Storage::disk('public')->exists($idea->image)) {
                Storage::disk('public')->delete($idea->image);
            }
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($idea->image && Storage::disk('public')->exists($idea->image)) {
                Storage::disk('public')->delete($idea->image);
            }
            $imagePath = $request->file('image')->store('ideas', 'public');
        }

        // ================= UPDATE IDEA =================
        $idea->update([
            'title' => $validated['title'],
            'status' => $validated['status'],
            'description' => $validated['description'],
            'links' => $validated['links'] ?? [],
            'image' => $imagePath,
        ]);

        // ===============================
        // 🧠 SMART STEP SYNC (SENIOR)
        // ===============================

        $steps = $request->input('steps', []);

        // collect IDs coming from form
        $incomingIds = collect($steps)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        // delete removed steps
        $idea->steps()
            ->whereNotIn('id', $incomingIds)
            ->delete();

        // process each step
        foreach ($steps as $stepData) {

            $description = trim($stepData['description'] ?? '');

            // skip empty rows
            if ($description === '') {
                continue;
            }

            // ================= UPDATE EXISTING =================
            if (! empty($stepData['id'])) {

                $idea->steps()
                    ->where('id', $stepData['id'])
                    ->update([
                        'description' => $description,
                    ]);

            }
            // ================= CREATE NEW =================
            else {

                $idea->steps()->create([
                    'description' => $description,
                    'completed' => false,
                ]);
            }
        }

        return back()->with('success', 'Idea updated successfully.');
    }
}
