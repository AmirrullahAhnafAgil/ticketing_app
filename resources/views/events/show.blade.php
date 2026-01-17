<x-layouts.app>
  <section class="max-w-7xl mx-auto py-12 px-6">
    <nav class="mb-6">
      <div class="breadcrumbs">
        <ul>
          <li><a href="{{ route('home') }}" class="link link-neutral">Beranda</a></li>
          <li>Event</li>
          <li>{{ $event->judul }}</li>
        </ul>
      </div>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- LEFT -->
      <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
          <figure>
            <img
              src="{{ $event->gambar ? asset('storage/'.$event->gambar) : 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp' }}"
              class="w-full h-96 object-cover"
              alt="{{ $event->judul }}"
            />
          </figure>

          <div class="card-body">
            <h1 class="text-3xl font-extrabold">{{ $event->judul }}</h1>
            <p class="text-sm text-gray-500">
              {{ \Carbon\Carbon::parse($event->tanggal_waktu)->locale('id')->translatedFormat('d F Y, H:i') }}
              • 📍 {{ $event->lokasi }}
            </p>

            <p class="mt-4">{{ $event->deskripsi }}</p>

            <div class="divider"></div>
            <h3 class="text-xl font-bold">Pilih Tiket</h3>

            @foreach($event->tikets as $tiket)
              <div class="card card-side p-4 shadow-sm mt-4">
                <div class="flex-1">
                  <h4 class="font-bold">{{ $tiket->tipe }}</h4>
                  <p class="text-sm text-gray-500">Stok: {{ $tiket->stok }}</p>
                </div>

                <div class="w-44 text-right">
                  <div class="font-bold">
                    Rp {{ number_format($tiket->harga,0,',','.') }}
                  </div>

                  <div class="flex justify-end gap-2 mt-2">
                    <button class="btn btn-sm" data-action="dec" data-id="{{ $tiket->id }}">−</button>
                    <input
                      id="qty-{{ $tiket->id }}"
                      type="number"
                      min="0"
                      max="{{ $tiket->stok }}"
                      value="0"
                      class="input input-bordered w-16 text-center"
                    />
                    <button class="btn btn-sm" data-action="inc" data-id="{{ $tiket->id }}">+</button>
                  </div>

                  <div class="text-sm mt-2">
                    Subtotal: <span id="subtotal-{{ $tiket->id }}">Rp 0</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

       <!-- Right / Summary -->
      <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-4 bg-base-100 shadow">
          <h4 class="font-bold text-lg">Ringkasan Pembelian</h4>

          <div class="mt-4">
            <div class="flex justify-between text-sm text-gray-500"><span>Item</span><span id="summaryItems">0</span>
            </div>
            <div class="flex justify-between text-xl font-bold mt-1"><span>Total</span><span id="summaryTotal">Rp
                0</span></div>
          </div>

          <div class="divider"></div>

          <div id="selectedList" class="space-y-2 text-sm text-gray-700">
            <p class="text-gray-500">Belum ada tiket dipilih</p>
          </div>

          @auth
            <button id="checkoutButton" class="btn btn-primary !bg-blue-900 text-white btn-block mt-6" onclick="openCheckout()" disabled>Checkout</button>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block mt-6 text-white">Login untuk Checkout</a>
          @endauth

        </div>
      </aside>
    </div>

    <!-- Checkout Modal -->
    <dialog id="checkout_modal" class="modal">
      <form method="dialog" class="modal-box">
        <h3 class="font-bold text-lg">Konfirmasi Pembelian</h3>
        <div class="mt-4 space-y-2 text-sm">
          <div id="modalItems">
            <p class="text-gray-500">Belum ada item.</p>
          </div>

          <div class="divider"></div>
          <div class="flex justify-between items-center">
            <span class="font-bold">Total</span>
            <span class="font-bold text-lg" id="modalTotal">Rp 0</span>
          </div>
        </div>

        <div class="modal-action">
          <button class="btn">Tutup</button>
          <button type="button" class="btn btn-primary px-4 !bg-blue-900 text-white" id="confirmCheckout">Konfirmasi</button>
        </div>
      </form>
    </dialog>
  </section>

  {{-- ================= SCRIPT ================= --}}

  <script>
    const formatRupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

    window.tickets = {
      @foreach($event->tikets as $tiket)
        {{ $tiket->id }}: {
          id: {{ $tiket->id }},
          price: {{ $tiket->harga }},
          stock: {{ $tiket->stok }},
          tipe: "{{ e($tiket->tipe) }}"
        },
      @endforeach
    };

    const summaryItemsEl = document.getElementById('summaryItems');
    const summaryTotalEl = document.getElementById('summaryTotal');
    const selectedListEl = document.getElementById('selectedList');
    const checkoutButton = document.getElementById('checkoutButton');

    function updateSummary() {
      let qtyTotal = 0;
      let priceTotal = 0;
      let html = '';

      Object.values(tickets).forEach(t => {
        const qty = Number(document.getElementById('qty-' + t.id).value || 0);
        if (qty > 0) {
          qtyTotal += qty;
          priceTotal += qty * t.price;
          html += `<div class="flex justify-between">
                    <span>${t.tipe} x ${qty}</span>
                    <span>${formatRupiah(qty * t.price)}</span>
                  </div>`;
        }
      });

      summaryItemsEl.textContent = qtyTotal;
      summaryTotalEl.textContent = formatRupiah(priceTotal);
      selectedListEl.innerHTML = html || 'Belum ada tiket dipilih';
      checkoutButton.disabled = qtyTotal === 0;
    }

    document.querySelectorAll('[data-action]').forEach(btn => {
      btn.onclick = () => {
        const id = btn.dataset.id;
        const input = document.getElementById('qty-' + id);
        let val = Number(input.value);

        if (btn.dataset.action === 'inc' && val < tickets[id].stock) val++;
        if (btn.dataset.action === 'dec' && val > 0) val--;

        input.value = val;
        document.getElementById('subtotal-' + id).textContent =
          formatRupiah(val * tickets[id].price);
        updateSummary();
      };
    });

    function openCheckout() {
      let html = '';
      let total = 0;

      Object.values(tickets).forEach(t => {
        const qty = Number(document.getElementById('qty-' + t.id).value || 0);
        if (qty > 0) {
          html += `<div class="flex justify-between">
                    <span>${t.tipe} x ${qty}</span>
                    <span>${formatRupiah(qty * t.price)}</span>
                  </div>`;
          total += qty * t.price;
        }
      });

      document.getElementById('modalItems').innerHTML = html;
      document.getElementById('modalTotal').textContent = formatRupiah(total);
      checkout_modal.showModal();
    }

    document.getElementById('confirmCheckout').onclick = async () => {
      const btn = document.getElementById('confirmCheckout');
      btn.disabled = true;
      btn.textContent = 'Memproses...';

      const items = [];
      Object.values(tickets).forEach(t => {
        const qty = Number(document.getElementById('qty-' + t.id).value || 0);
        if (qty > 0) items.push({ tiket_id: t.id, jumlah: qty });
      });

      const res = await fetch("{{ route('orders.store') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ event_id: {{ $event->id }}, items })
      });

      const data = await res.json();
      window.location.href = data.redirect;
    };

    updateSummary();
  </script>
</x-layouts.app>
