@extends('layouts.app')

@section('title', 'Uniform & Merchandise Orders')
@section('page-title', 'Uniform Orders & Deliveries')
@section('page-subtitle', 'Manage uniform orders, record payments with Sinhala WhatsApp confirmations, and track deliveries')

@section('content')
<div class="space-y-6" x-data="{ newOrderModal: false, newItemModal: false }">

    <!-- KPI Summary Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Orders</span>
            <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ $summary['total_orders'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-emerald-800">Collected (Paid)</span>
            <p class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">LKR {{ number_format($summary['paid_amount'], 2) }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-amber-800">Pending Payment</span>
            <p class="text-xl sm:text-2xl font-black text-amber-700 mt-1">LKR {{ number_format($summary['pending_amount'], 2) }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-cyan-50 border border-cyan-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-cyan-800">Ready / In Stock</span>
            <p class="text-xl sm:text-2xl font-black text-cyan-700 mt-1">{{ $summary['ready_for_delivery'] }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-indigo-800">Delivered</span>
            <p class="text-xl sm:text-2xl font-black text-indigo-700 mt-1">{{ $summary['delivered'] }}</p>
        </div>
    </div>

    <!-- Actions & Filter Strip -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Filters -->
        <form action="{{ route('teacher.uniforms.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <select name="payment_status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>All Payment Statuses</option>
                <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Paid Only</option>
                <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending Payment Only</option>
            </select>

            <select name="delivery_status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                <option value="all" {{ $deliveryStatus === 'all' ? 'selected' : '' }}>All Delivery Statuses</option>
                <option value="ordered" {{ $deliveryStatus === 'ordered' ? 'selected' : '' }}>Ordered</option>
                <option value="ready" {{ $deliveryStatus === 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                <option value="delivered" {{ $deliveryStatus === 'delivered' ? 'selected' : '' }}>Delivered to Child</option>
            </select>
        </form>

        <div class="flex items-center gap-2">
            @if(Auth::user()->isAdmin())
                <button @click="newItemModal = true" 
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Add Uniform Item
                </button>
            @endif

            <button @click="newOrderModal = true" 
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md transition-all cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-shirt"></i>
                <span>New Uniform Order</span>
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Order ID</th>
                        <th class="py-3.5 px-4">Student Details</th>
                        <th class="py-3.5 px-4">Item & Size</th>
                        <th class="py-3.5 px-4">Amount (LKR)</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Delivery</th>
                        <th class="py-3.5 px-4 text-right">Actions & Sinhala Messages</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $parentPhone = preg_replace('/[^0-9]/', '', $order->student->guardian_phone ?: $order->student->contact_number);
                            $sinhalaPayMsg = $order->getSinhalaPaymentMessage($school->name);
                            $sinhalaDeliverMsg = $order->getSinhalaDeliveryMessage($school->name);
                            $waPayUrl = "https://wa.me/{$parentPhone}?text=" . urlencode($sinhalaPayMsg);
                            $waDeliverUrl = "https://wa.me/{$parentPhone}?text=" . urlencode($sinhalaDeliverMsg);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-900 text-sm">{{ $order->student->full_name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $order->student->admission_number }} &bull; {{ $order->classRoom?->name }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $order->item_name }}</span>
                                <p class="text-[11px] text-slate-500">Qty: {{ $order->quantity }} @if($order->size) &bull; Size: <strong class="text-indigo-600">{{ $order->size }}</strong>@endif</p>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[9px]"></i> Paid
                                    </span>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $order->receipt_no }}</p>
                                @else
                                    <form action="{{ route('teacher.uniforms.pay', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[11px] transition-colors cursor-pointer" title="Collect Cash">
                                            Mark Paid
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($order->delivery_status === 'delivered')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                        <i class="fa-solid fa-box-open text-[9px]"></i> Delivered
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $order->delivered_date?->format('Y-m-d') }}</p>
                                @else
                                    <form action="{{ route('teacher.uniforms.deliver', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-[11px] transition-colors cursor-pointer">
                                            Mark Delivered
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Sinhala Payment WhatsApp Receipt -->
                                    @if($order->payment_status === 'paid')
                                        <a href="{{ $waPayUrl }}" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-[11px] transition-colors flex items-center gap-1"
                                           title="Send Sinhala Payment Receipt via WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                            <span>ගෙවීම් පණිවිඩය</span>
                                        </a>
                                    @endif

                                    <!-- Sinhala Delivered WhatsApp Alert -->
                                    @if($order->delivery_status === 'delivered')
                                        <a href="{{ $waDeliverUrl }}" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-[11px] transition-colors flex items-center gap-1"
                                           title="Send Sinhala Uniform Delivered Notification">
                                            <i class="fa-brands fa-whatsapp text-indigo-600"></i>
                                            <span>භාරදීමේ පණිවිඩය</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-shirt text-3xl mb-2 text-slate-300 block"></i>
                                <span>No uniform orders recorded yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Create Uniform Order Modal -->
    <div x-show="newOrderModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;" x-transition>

        <div @click.away="newOrderModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-shirt text-indigo-600"></i>
                    <span>Record New Uniform Order</span>
                </h3>
                <button @click="newOrderModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.uniforms.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Student *</label>
                    <select name="student_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose student --</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->admission_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name *</label>
                        <input type="text" name="item_name" required placeholder="e.g. Pre-School Uniform Set"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Size Option</label>
                        <input type="text" name="size" placeholder="e.g. Size 24, S, M"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit Price (LKR) *</label>
                        <input type="number" step="0.01" name="unit_price" required placeholder="e.g. 2500.00"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Quantity *</label>
                        <input type="number" name="quantity" value="1" min="1" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer mt-1">
                        <input type="checkbox" name="mark_as_paid" value="1" checked class="w-4 h-4 rounded text-emerald-600 border-slate-300">
                        <span class="text-xs font-bold text-emerald-800">Mark as Paid immediately (Generate Receipt & Sinhala WhatsApp Message)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="newOrderModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                        Save Uniform Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Item to Catalog Modal (Principal) -->
    <div x-show="newItemModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;" x-transition>

        <div @click.away="newItemModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">Add Pre-School Item</h3>
                <button @click="newItemModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.uniforms.items.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Full Uniform Set with Cap"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Price (LKR) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="3200.00"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Available Sizes</label>
                    <input type="text" name="size_options" value="XS, S, M, L" placeholder="XS, S, M, L"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="newItemModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md cursor-pointer">
                        Add to Catalog
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

