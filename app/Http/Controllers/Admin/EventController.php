<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('admin.event.index', compact('events'));
    }

    public function create()
    {
        $categories = Kategori::all();
        return view('admin.event.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_waktu' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // ✅ SIMPAN KE STORAGE
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('events', 'public');
            $validatedData['gambar'] = $path; // contoh: events/poster.jpg
        }

        $validatedData['user_id'] = auth()->id();

        Event::create($validatedData);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $categories = Kategori::all();
        $tickets = $event->tikets;

        return view('admin.event.show', compact('event', 'categories', 'tickets'));
    }

    public function edit(string $id)
    {
        $event = Event::findOrFail($id);
        $categories = Kategori::all();
        return view('admin.event.edit', compact('event', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_waktu' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // ✅ UPDATE KE STORAGE
        if ($request->hasFile('gambar')) {
            if ($event->gambar) {
                Storage::disk('public')->delete($event->gambar);
            }

            $path = $request->file('gambar')->store('events', 'public');
            $validatedData['gambar'] = $path;
        }

        $event->update($validatedData);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);

        if ($event->gambar) {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
