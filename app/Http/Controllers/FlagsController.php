<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FlagsController extends Controller
{
    public function index(): View
    {
        return view('flags.index', ['flags' => Flag::all()]);
    }

    public function create(): View
    {
        return view('flags.create');
    }

    public function edit(int $flagId): View
    {
        $flag = Flag::find($flagId);
        return view('flags.edit', ['flag' => $flag]);
    }

    public function delete(int $flagId): RedirectResponse
    {
        Flag::find($flagId)->delete();
        return redirect("/flags");
    }

    public function update(int $flagId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'color' => 'required',
        ]);

        $flag = Flag::find($flagId);
        $flag->fill($formParams);
        $flag->save();

        return redirect("/flags");
    }

    public function store(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'color' => 'required',
        ]);

        $flag = new Flag($formParams);
        $flag->save();

        return redirect("/flags");
    }

    public function showEvents(int $flagId): View
    {
        $flag = Flag::with(['events.protocolLines', 'events.competition'])->find($flagId);
        return view('flags.events', ['flag' => $flag]);
    }
}
