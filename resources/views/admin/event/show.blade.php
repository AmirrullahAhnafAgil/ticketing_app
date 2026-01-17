<x-layouts.admin title="Detail Event">
    <div class="container mx-auto p-10">

        {{-- TOAST SUCCESS --}}
        @if (session('success'))
            <div class="toast toast-bottom toast-center z-50">
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                </div>
            </div>

            <script>
                setTimeout(() => {
                    document.querySelector('.toast')?.remove()
                }, 3000)
            </script>
        @endif

        {{-- DETAIL EVENT --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-6">Detail Event</h2>

                <form class="space-y-4">
                    {{-- Judul --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Judul Event</span></label>
                        <input type="text" class="input input-bordered w-full" value="{{ $event->judul }}" disabled />
                    </div>

                    {{-- Deskripsi --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Deskripsi</span></label>
                        <textarea class="textarea textarea-bordered h-24 w-full" disabled>{{ $event->deskripsi }}</textarea>
                    </div>

                    {{-- Tanggal --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Tanggal & Waktu</span></label>
                        <input type="datetime-local" class="input input-bordered w-full"
                            value="{{ $event->tanggal_waktu->format('Y-m-d\TH:i') }}" disabled />
                    </div>

                    {{-- Lokasi --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Lokasi</span></label>
                        <input type="text" class="input input-bordered w-full" value="{{ $event->lokasi }}" disabled />
                    </div>

                    {{-- Kategori --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Kategori</span></label>
                        <select class="select select-bordered w-full" disabled>
                            @foreach ($categories as $category)
                                <option {{ $category->id == $event->kategori_id ? 'selected' : '' }}>
                                    {{ $category->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Preview Gambar --}}
                    @if ($event->gambar)
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">Gambar Event</span></label>
                            <div class="max-w-sm">
                                <img
                                    src="{{ asset('storage/'.$event->gambar) }}"
                                    class="rounded-lg"
                                >
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- LIST TICKET --}}
        <div class="mt-10">
            <div class="flex">
                <h1 class="text-3xl font-semibold mb-4">List Ticket</h1>
                <button onclick="add_ticket_modal.showModal()" class="btn btn-primary ml-auto">
                    Tambah Ticket
                </button>
            </div>

            <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $i => $ticket)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ticket->tipe }}</td>
                                <td>Rp {{ number_format($ticket->harga,0,',','.') }}</td>
                                <td>{{ $ticket->stok }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        onclick="openEditModal(this)"
                                        data-id="{{ $ticket->id }}"
                                        data-tipe="{{ $ticket->tipe }}"
                                        data-harga="{{ $ticket->harga }}"
                                        data-stok="{{ $ticket->stok }}">
                                        Edit
                                    </button>

                                    <button class="btn btn-sm bg-red-500 text-white"
                                        onclick="openDeleteModal(this)"
                                        data-id="{{ $ticket->id }}">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada ticket.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- MODAL ADD / EDIT / DELETE TICKET --}}
    {{-- (tetap seperti punyamu, tidak diubah) --}}

</x-layouts.admin>
