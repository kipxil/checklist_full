<!DOCTYPE html>
<html>

<head>
    <title>Daily Report PDF</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px;
        }

        .font-bold {
            font-weight: bold;
        }

        .section-title {
            background-color: #eee;
            padding: 5px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        .data-table th {
            background-color: #f9f9f9;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badges {
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 9px;
            margin-right: 2px;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 9px;
            color: #888;
        }

        .signature-box {
            margin-top: 30px;
            width: 100%;
        }

        .sig-col {
            width: 33%;
            float: left;
            text-align: center;
        }

        .sig-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="header">
        {{-- Ganti dengan path logo Anda yang benar (harus path absolut public_path) --}}
        <img src="{{ public_path('images/VasaHotel.png') }}" height="40">
        <h1>{{ $dailyReport->restaurant->name }}</h1>
        <p>Daily Operations Report</p>
    </div>

    {{-- INFO UMUM --}}
    <table class="info-table">
        <tr>
            <td width="15%" class="font-bold">Date</td>
            <td width="35%">: {{ $dailyReport->date->format('d F Y') }}</td>
            <td width="15%" class="font-bold">Status</td>
            <td width="35%">: <span style="color: green; font-weight:bold">APPROVED</span></td>
        </tr>
        <tr>
            <td class="font-bold">Created By</td>
            <td>: {{ $dailyReport->user->name }} ({{ $dailyReport->user->nik }})</td>
            <td class="font-bold">Approved By</td>
            <td>: {{ $dailyReport->approver->name ?? '-' }}</td>
        </tr>
    </table>

    {{-- LOOPING SESI (Breakfast, Lunch, Dinner) --}}
    @foreach ($dailyReport->details as $detail)
        <div class="section-title">
            {{ strtoupper($detail->session_type) }} SESSION
            @if ($detail->thematic)
                <span style="font-weight: normal">| Theme: {{ $detail->thematic }}</span>
            @endif
        </div>

        {{-- 1. REVENUE SUMMARY --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th>Revenue Category</th>
                    <th class="text-end">Amount (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Food Revenue</td>
                    <td class="text-end">{{ number_format($detail->revenue_food, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Beverage Revenue</td>
                    <td class="text-end">{{ number_format($detail->revenue_beverage, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Others Revenue</td>
                    <td class="text-end">{{ number_format($detail->revenue_others, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Event Revenue</td>
                    <td class="text-end">{{ number_format($detail->revenue_event, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color: #f0f0f0; font-weight:bold;">
                    <td>TOTAL REVENUE</td>
                    <td class="text-end">
                        {{ number_format($detail->revenue_food + $detail->revenue_beverage + $detail->revenue_others + $detail->revenue_event, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- 2. COVER & COMPETITOR (Grid Layout sederhana dgn Table) --}}
        <table style="width: 100%; margin-top: 10px;">
            <tr>
                {{-- Kolom Kiri: Cover Data --}}
                <td width="50%" valign="top">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="2">Cover Statistics</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($detail->cover_data)
                                @foreach ($detail->cover_data as $k => $v)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $k)) }}</td>
                                        <td class="text-end font-bold">{{ $v }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </td>
                {{-- Kolom Kanan: Competitor --}}
                <td width="50%" valign="top">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="2">Competitor Comparison</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($detail->competitor_data)
                                @foreach ($detail->competitor_data as $k => $v)
                                    <tr>
                                        <td>{{ ucwords(str_replace(['_cover', 'cover'], '', $k)) }}</td>
                                        <td class="text-end font-bold">{{ $v }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 3. REMARKS & STAFF --}}
        <div style="margin-top: 10px; font-size: 11px;">
            <strong>General Remarks:</strong> {{ $detail->remarks ?? '-' }} <br>
            <strong>Staff On Duty:</strong>
            @php
                $staff = $detail->staff_on_duty;
                if (is_string($staff)) {
                    $staff = json_decode($staff, true);
                }
            @endphp
            @if (is_array($staff))
                {{ implode(', ', $staff) }}
            @else
                -
            @endif
        </div>
    @endforeach

    {{-- FOOTER / TANDA TANGAN --}}
    <div class="signature-box">
        <div class="sig-col">
            <br><br>
            <div class="sig-line">{{ $dailyReport->user->name }}</div>
            Created By
        </div>
        <div class="sig-col">
            <br><br>
            <div class="sig-line">Manager On Duty</div>
            Checked By
        </div>
        <div class="sig-col">
            <br><br>
            <div class="sig-line">{{ $dailyReport->approver->name ?? 'Manager' }}</div>
            Approved By
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Generated by System on {{ now()->format('d M Y H:i') }}
    </div>

</body>

</html>
