<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan riwayat pembelian user
     */
    public function index()
    {
        $user = Auth::user() ?? \App\Models\User::first();

        $orders = Order::where('user_id', $user->id)
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Menampilkan detail satu order
     */
    public function show(Order $order)
    {
        $order->load('detailOrders.tiket', 'event');
        return view('orders.show', compact('order'));
    }

    /**
     * Menyimpan order (AJAX checkout)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|exists:events,id',
            'items' => 'required|array|min:1',
            'items.*.tiket_id' => 'required|integer|exists:tikets,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($data, $user) {
                $total = 0;

                /**
                 * 1. Validasi stok & hitung total harga
                 */
                foreach ($data['items'] as $item) {
                    $tiket = Tiket::findOrFail($item['tiket_id']);

                    if ($tiket->stok < $item['jumlah']) {
                        throw new \Exception(
                            "Stok tidak cukup untuk tiket: {$tiket->tipe}"
                        );
                    }

                    $total += ($tiket->harga ?? 0) * $item['jumlah'];
                }

                /**
                 * 2. Buat order
                 */
                $order = Order::create([
                    'user_id' => $user->id,
                    'event_id' => $data['event_id'],
                    'order_date' => Carbon::now(),
                    'total_harga' => $total,
                ]);

                /**
                 * 3. Simpan detail order & kurangi stok
                 */
                foreach ($data['items'] as $item) {
                    $tiket = Tiket::findOrFail($item['tiket_id']);
                    $subtotal = ($tiket->harga ?? 0) * $item['jumlah'];

                    DetailOrder::create([
                        'order_id' => $order->id,
                        'tiket_id' => $tiket->id,
                        'jumlah' => $item['jumlah'],
                        'subtotal_harga' => $subtotal,
                    ]);

                    $tiket->stok -= $item['jumlah'];
                    $tiket->save();
                }

                return $order;
            });

            // flash message untuk halaman riwayat
            session()->flash('success', 'Pesanan berhasil dibuat.');

            return response()->json([
                'ok' => true,
                'order_id' => $order->id,
                'redirect' => route('orders.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
