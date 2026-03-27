<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['serviceName'] }} - {{ $report['reference'] }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #0f172a;
            --muted: #475569;
            --border: #d7e0ea;
            --panel: #ffffff;
            --panel-soft: #f8fafc;
            --accent: #0f766e;
            --accent-soft: #ccfbf1;
            --gold: #9a6700;
            --gold-soft: #fff3c4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
            padding: 20px;
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .toolbar a,
        .toolbar button {
            appearance: none;
            border: 1px solid var(--border);
            background: white;
            color: var(--ink);
            border-radius: 999px;
            padding: 11px 18px;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .primary {
            background: var(--ink);
            border-color: var(--ink);
            color: white;
        }

        .sheet-wrap {
            padding: 0 20px 28px;
        }

        .sheet {
            max-width: 1040px;
            margin: 0 auto;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        }

        .sheet-header {
            padding: 28px 30px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            background: linear-gradient(135deg, #f8fafc, #ecfeff);
            border-bottom: 1px solid var(--border);
        }

        .sheet-header.premium {
            background:
                radial-gradient(circle at top right, rgba(250, 204, 21, 0.22), transparent 28%),
                linear-gradient(135deg, #042f2e, #0f172a 58%, #115e59);
            color: white;
        }

        .kicker {
            font-size: 11px;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            font-weight: 700;
            opacity: 0.7;
        }

        .title {
            margin-top: 12px;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.15;
        }

        .subtitle {
            margin-top: 12px;
            max-width: 680px;
            font-size: 14px;
            line-height: 1.7;
            color: var(--muted);
        }

        .sheet-header.premium .subtitle,
        .sheet-header.premium .kicker {
            color: rgba(255, 255, 255, 0.8);
        }

        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: white;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .sheet-header.premium .badge {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.16);
            color: white;
        }

        .status-badge {
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .status-badge.failed {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-badge.pending,
        .status-badge.processing,
        .status-badge.manual_review {
            background: #fef3c7;
            color: #92400e;
        }

        .sheet-body {
            padding: 28px 30px 34px;
        }

        .hero-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 22px;
            background: var(--panel-soft);
            padding: 20px;
        }

        .identity-card {
            display: grid;
            grid-template-columns: 1fr 180px;
            gap: 18px;
            align-items: start;
        }

        .identity-value {
            margin-top: 8px;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.03em;
        }

        .muted {
            color: var(--muted);
        }

        .avatar-box {
            min-height: 180px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .avatar-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .grid-two {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            border-radius: 18px;
            border: 1px solid var(--border);
            background: white;
            padding: 14px 16px;
        }

        .field-label {
            font-size: 11px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }

        .field-value {
            margin-top: 10px;
            font-size: 14px;
            font-weight: 700;
            word-break: break-word;
        }

        .premium-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            padding: 18px 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--gold-soft), #fff);
            border: 1px solid rgba(154, 103, 0, 0.18);
        }

        .premium-banner .headline {
            font-size: 22px;
            font-weight: 800;
            color: #7c5200;
        }

        .footer-note {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px dashed var(--border);
            font-size: 12px;
            line-height: 1.8;
            color: #64748b;
        }

        .signature-box {
            margin-top: 18px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: white;
            padding: 14px 16px;
        }

        .signature-box img {
            max-width: 100%;
            max-height: 82px;
            object-fit: contain;
        }

        @media (max-width: 900px) {
            .hero-grid,
            .identity-card,
            .grid-two {
                grid-template-columns: 1fr;
            }

            .sheet-header,
            .sheet-body {
                padding: 22px 18px;
            }
        }

        @media print {
            body {
                background: white;
            }

            .toolbar {
                display: none;
            }

            .sheet-wrap {
                padding: 0;
            }

            .sheet {
                max-width: none;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #64748b;">Verification Print View</div>
            <div style="margin-top: 6px; font-size: 15px; color: #475569;">{{ $report['serviceName'] }} · {{ $report['reference'] }}</div>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('verifications.download', ['verificationRequest' => $verification->id, 'mode' => $printMode]) }}">Download</a>
            <button class="primary" type="button" onclick="window.print()">Print Now</button>
        </div>
    </div>

    <div class="sheet-wrap">
        <article class="sheet">
            <header @class(['sheet-header', 'premium' => $printMode === 'premium' && $report['template'] === 'ninSlip'])>
                <div>
                    <div class="kicker">
                        {{ $report['template'] === 'ninSlip' ? 'National Identity Verification Slip' : 'Verification Response Sheet' }}
                    </div>
                    <div class="title">
                        {{ $report['template'] === 'ninSlip' && $printMode === 'premium' ? 'Premium NIN Slip' : ($report['template'] === 'ninSlip' ? 'Standard NIN Slip' : $report['serviceName']) }}
                    </div>
                    <div class="subtitle">{{ $report['message'] }}</div>
                    <div class="badge-row">
                        <span class="badge">{{ $report['reference'] }}</span>
                        <span class="badge">{{ $report['serviceCode'] }}</span>
                        @if ($report['providerVersion'])
                            <span class="badge">{{ $report['providerVersion'] }}</span>
                        @endif
                    </div>
                </div>

                <div class="status-badge {{ $report['status'] }}">
                    {{ $report['statusLabel'] }}
                </div>
            </header>

            <div class="sheet-body">
                @if ($report['template'] === 'ninSlip' && $printMode === 'premium')
                    <section class="premium-banner">
                        <div>
                            <div class="kicker" style="color: #9a6700;">Verification Identity</div>
                            <div class="headline">{{ $report['subjectName'] }}</div>
                        </div>
                        <div class="badge">{{ $report['identityLabel'] }}: {{ $report['identityValue'] ?: 'N/A' }}</div>
                    </section>
                @endif

                <section class="hero-grid">
                    <div class="panel">
                        <div class="identity-card">
                            <div>
                                <div class="field-label">{{ $report['identityLabel'] }}</div>
                                <div class="identity-value">{{ $report['identityValue'] ?: 'Not available' }}</div>
                                <div style="margin-top: 14px; font-size: 14px; font-weight: 700;">{{ $report['subjectName'] }}</div>
                                <div class="muted" style="margin-top: 8px; font-size: 14px; line-height: 1.7;">
                                    This printable sheet is generated from the saved verification response and can be reused for reference, download, or follow-up review later.
                                </div>
                            </div>
                            <div class="avatar-box">
                                @if ($report['photo'])
                                    <img src="{{ $report['photo'] }}" alt="Verification subject photo">
                                @else
                                    <div class="placeholder">No Photo</div>
                                @endif
                            </div>
                        </div>

                        @if ($report['signature'])
                            <div class="signature-box">
                                <div class="field-label">Signature</div>
                                <div style="margin-top: 10px;">
                                    <img src="{{ $report['signature'] }}" alt="Verification subject signature">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="panel">
                        <div class="field-label">Verification Meta</div>
                        <div class="grid-two">
                            @foreach ($report['metaItems'] as $item)
                                <div class="field">
                                    <div class="field-label">{{ $item['label'] }}</div>
                                    <div class="field-value">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="grid-two">
                    <div class="panel">
                        <div class="field-label">Summary Fields</div>
                        <div class="grid-two">
                            @foreach ($report['summaryItems'] as $item)
                                <div class="field">
                                    <div class="field-label">{{ $item['label'] }}</div>
                                    <div class="field-value">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="panel">
                        <div class="field-label">Saved Response Fields</div>
                        <div class="grid-two">
                            @foreach ($report['detailItems'] as $item)
                                <div class="field">
                                    <div class="field-label">{{ $item['label'] }}</div>
                                    <div class="field-value">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <div class="footer-note">
                    <strong>Reference:</strong> {{ $report['reference'] }}<br>
                    <strong>Status:</strong> {{ $report['statusLabel'] }}<br>
                    <strong>Engine:</strong> {{ $report['providerVersion'] ?: 'Pending' }}<br>
                    <strong>Notice:</strong> This document reflects the saved verification response currently stored in the application.
                </div>
            </div>
        </article>
    </div>
</body>
</html>
