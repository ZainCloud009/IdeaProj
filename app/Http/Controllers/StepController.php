<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StepController extends Controller
{
    public function toggle(Step $step, Request $request)
    {
        abort_if($step->idea->user_id !== Auth::id(), 403);

        $step->completed = $request->boolean('completed'); // ✅ cast to boolean
        $step->save();

        return response()->json(['success' => true, 'completed' => $step->completed]);
    }
}
