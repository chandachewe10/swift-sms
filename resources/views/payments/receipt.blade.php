<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }} — SWIFT-SMS</title>
    <style>
        /* ── Reset & base ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            color: #111827;
            background: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Page wrapper ─────────────────────────────────────────────── */
        .page {
            max-width: 720px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
        }

        /* ── Header ───────────────────────────────────────────────────── */
        .header {
            background: #111827;
            color: #ffffff;
            padding: 32px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .brand-name span { color: #fbbf24; }

        .brand-url {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .receipt-meta { text-align: right; }

        .receipt-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #9ca3af;
        }

        .receipt-number {
            font-size: 28px;
            font-weight: 800;
            color: #fbbf24;
            line-height: 1.1;
        }

        /* ── Body ─────────────────────────────────────────────────────── */
        .body { padding: 40px; }

        /* ── Two-column metadata grid ─────────────────────────────────── */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }

        .meta-block h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .meta-block p {
            font-size: 14px;
            line-height: 1.7;
            color: #111827;
        }

        /* ── Status badge ─────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-successful { background: #d1fae5; color: #065f46; }
        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-failed     { background: #fee2e2; color: #991b1b; }
        .badge-default    { background: #f3f4f6; color: #374151; }

        /* ── Divider ──────────────────────────────────────────────────── */
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }

        /* ── Line-items table ─────────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .items-table th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #6b7280;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table th.right { text-align: right; }

        .items-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .items-table td.right {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }

        .items-table td.muted { color: #6b7280; }

        /* ── Total row ────────────────────────────────────────────────── */
        .total-row { background: #f9fafb; }

        .total-row td {
            padding: 16px 12px;
            font-size: 16px;
            font-weight: 700;
            border-bottom: none;
        }

        /* ── Payment reference block ──────────────────────────────────── */
        .ref-block {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 24px;
        }

        .ref-block p {
            font-size: 13px;
            color: #374151;
            line-height: 1.9;
        }

        .ref-block strong { color: #111827; min-width: 160px; display: inline-block; }

        /* ── Footer ───────────────────────────────────────────────────── */
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 24px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .footer-text {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }

        /* ── Print button (hidden when printing) ─────────────────────── */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #111827;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .print-btn:hover { background: #1f2937; }

        /* ── Print media query ────────────────────────────────────────── */
        @media print {
            body { background: #fff; }

            .page {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                max-width: 100%;
            }

            .no-print { display: none !important; }

            /* Force background colours to print */
            .header           { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-successful { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .total-row        { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ref-block        { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .footer           { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="page">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="header">
        <div>
            <div class="brand-name">⚡ SWIFT<span>-SMS</span></div>
            <div class="brand-url">swiftsms.macroit.org</div>
        </div>
        <div class="receipt-meta">
            <div class="receipt-label">Receipt</div>
            <div class="receipt-number">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    {{-- ── Body ──────────────────────────────────────────────────────────── --}}
    <div class="body">

        {{-- Metadata grid --}}
        <div class="meta-grid">
            <div class="meta-block">
                <h3>Billed To</h3>
                <p>
                    <strong>{{ $customer?->name ?? 'N/A' }}</strong><br>
                    {{ $customer?->email ?? '' }}
                </p>
            </div>
            <div class="meta-block">
                <h3>Receipt Details</h3>
                <p>
                    <strong>Date:</strong>
                    {{ $payment->created_at?->format('F j, Y') ?? 'N/A' }}<br>
                    <strong>Status:</strong>
                    @php
                        $statusClass = match($payment->status) {
                            'successful' => 'badge-successful',
                            'pending'    => 'badge-pending',
                            'failed'     => 'badge-failed',
                            default      => 'badge-default',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ ucfirst($payment->status ?? 'unknown') }}
                    </span>
                </p>
            </div>
        </div>

        <hr>

        {{-- Line items --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->messages ?: 'Subscription Payment' }}</td>
                    <td class="right">K {{ number_format((float) $payment->amount, 2) }}</td>
                </tr>

                @if ($payment->fee_amount && $payment->fee_amount > 0)
                <tr>
                    <td class="muted">Processing Fee</td>
                    <td class="right muted">K {{ number_format((float) $payment->fee_amount, 2) }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total Paid</td>
                    <td class="right">
                        K {{ number_format((float) ($payment->transaction_amount ?: $payment->amount), 2) }}
                        @if ($payment->currency && $payment->currency !== 'ZMW')
                            <span style="font-size:12px;font-weight:400;color:#6b7280;">({{ $payment->currency }})</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Reference block --}}
        <div class="ref-block">
            @if ($payment->reference)
            <p><strong>Payment Reference</strong> {{ $payment->reference }}</p>
            @endif
            @if ($payment->depositId)
            <p><strong>Transaction ID</strong> {{ $payment->depositId }}</p>
            @endif
            @if ($payment->merchant_reference)
            <p><strong>Merchant Reference</strong> {{ $payment->merchant_reference }}</p>
            @endif
            @if ($payment->customer_wallet)
            <p><strong>Mobile Money Number</strong> {{ $payment->customer_wallet }}</p>
            @endif
            <p><strong>Currency</strong> {{ $payment->currency ?? 'ZMW' }}</p>
            <p><strong>Payment Date</strong> {{ $payment->created_at?->format('l, F j, Y \a\t g:i A') ?? 'N/A' }}</p>
        </div>

    </div>

    {{-- ── Footer ─────────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-text">
            <p><strong>Thank you for choosing SWIFT-SMS.</strong></p>
            <p>Questions? Email us at <a href="mailto:swiftsms@macroit.org" style="color:#6b7280;">swiftsms@macroit.org</a></p>
        </div>
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">
                🖨️ Print / Save as PDF
            </button>
        </div>
    </div>

</div>

</body>
</html>
