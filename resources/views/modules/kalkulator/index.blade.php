@extends('layouts.app')

@php
    use App\Models\Transaksi\Transfer;
    use App\Models\Transaksi\Tunai;
    use App\Models\Transaksi\Limit;
    use App\Models\User;
    use Carbon\Carbon;

    $mulai = request('mulai') ? Carbon::parse(request('mulai')) : Carbon::today();
    $sampai = request('sampai') ? Carbon::parse(request('sampai'))->endOfDay() : Carbon::today()->endOfDay();

    $users = User::role(['admin', 'user'])->get();
    $currentUserId = auth()->id();

    $karyawanData = [];
    $totalKeuntunganAll = 0;

    foreach ($users as $user) {
        $transfers = Transfer::where('user_id', $user->id)
            ->whereBetween('created_at', [$mulai, $sampai])
            ->get();
        $tunais = Tunai::where('user_id', $user->id)
            ->whereBetween('created_at', [$mulai, $sampai])
            ->get();

        $totalTransfer = $transfers->sum('nominal');
        $totalTunai = $tunais->sum('nominal');

        if ($totalTransfer > 0 || $totalTunai > 0) {
            $adminTransfer = $transfers->sum('admin');
            // Admin fee tunai disimpan di kolom keterangan (legacy)
            $adminTunai = $tunais->sum(function($item) {
                return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
            });
            $jumlahTransfer = $transfers->count();
            $jumlahTunai = $tunais->count();
            $kunjungan = $jumlahTransfer + $jumlahTunai;
            $keuntungan = $adminTransfer + $adminTunai;
            $totalKeuntunganAll += $keuntungan;

            // Cabang toko (ambil dari data terakhir)
            $cabang = $transfers->last()?->cabang_toko ?? $tunais->last()?->cabang_toko ?? '-';

            $karyawanData[] = [
                'user' => $user,
                'cabang' => $cabang,
                'total_transfer' => $totalTransfer,
                'total_tunai' => $totalTunai,
                'admin_transfer' => $adminTransfer,
                'admin_tunai' => $adminTunai,
                'jumlah_transfer' => $jumlahTransfer,
                'jumlah_tunai' => $jumlahTunai,
                'kunjungan' => $kunjungan,
                'keuntungan' => $keuntungan,
                'is_current' => $user->id == $currentUserId,
            ];
        }
    }

    // Sort: current user first, then yayang
    usort($karyawanData, function($a, $b) {
        if ($a['user']->username === 'yayang') return -1;
        if ($b['user']->username === 'yayang') return 1;
        return $b['is_current'] <=> $a['is_current'];
    });
@endphp

@section('content')

{{-- Filter Tanggal --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.2rem 1.5rem;">
        <form method="GET" action="{{ url('/kalkulator') }}" style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            <div>
                <label class="form-label">Mulai Tanggal</label>
                <input type="date" name="mulai" class="form-control" value="{{ $mulai->format('Y-m-d') }}">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="{{ $sampai->format('Y-m-d') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Submit
                </button>
            </div>
            {{-- Total Keuntungan --}}
            <div style="margin-left: auto;">
                <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(139, 92, 246, 0.15)); border: 1.5px solid rgba(99, 102, 241, 0.4); border-radius: 12px; padding: 0.6rem 1.5rem; text-align: center;">
                    <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(165, 180, 252, 0.8); margin-bottom: 0.2rem;">Total Keuntungan</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #fff;">Rp {{ number_format($totalKeuntunganAll, 0, ',', ',') }}</div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Chart Keuntungan --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h5 style="font-weight: 700; margin: 0; color: #fff;">Grafik Keuntungan</h5>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                {{-- User Dropdown --}}
                <div class="chart-user-dropdown" style="position: relative;">
                    <button type="button" id="userDropdownBtn" class="chart-btn" style="display: flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.8rem;">
                        <i class="bi bi-people-fill" style="font-size: 0.85rem;"></i>
                        <span id="userDropdownLabel">Semua User</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.65rem;"></i>
                    </button>
                    <div id="userDropdownMenu" class="user-dropdown-menu" style="display: none;">
                        <label class="user-check-item">
                            <input type="checkbox" id="checkAll" checked>
                            <span>Semua</span>
                        </label>
                        <div style="border-bottom: 1px solid rgba(255,255,255,0.06); margin: 0.3rem 0;"></div>
                        @foreach($users as $u)
                        <label class="user-check-item">
                            <input type="checkbox" class="user-check" value="{{ $u->id }}" checked>
                            <span>{{ $u->username }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                {{-- Period Toggle --}}
                <div class="chart-toggle">
                    <button type="button" class="chart-btn active" data-period="daily">Harian</button>
                    <button type="button" class="chart-btn" data-period="weekly">Mingguan</button>
                    <button type="button" class="chart-btn" data-period="monthly">Bulanan</button>
                </div>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="profitChart"></canvas>
        </div>
    </div>
</div>

{{-- Title --}}
<h4 style="font-weight: 800; margin-bottom: 1.5rem;">Kalkulator</h4>

{{-- Cards Karyawan --}}
<div class="calc-grid">
    @foreach($karyawanData as $data)
    @php
        $isYayang = $data['user']->username === 'yayang';
        $isCurrent = $data['is_current'];
        $cardClass = $isYayang ? 'glass-card-yayang' : ($isCurrent ? 'glass-card-current' : 'glass-card');
    @endphp
    <div class="{{ $cardClass }}">
        {{-- Header --}}
        <div style="padding: 1.3rem 1.5rem; position: relative; z-index: 1;">
            <div style="font-weight: 700; font-size: 1.05rem; color: #fff;">
                {{ $data['user']->username }}
            </div>
            <div style="font-size: 0.82rem; color: rgba(255,255,255,0.6);">
                - {{ $data['cabang'] ?: '-' }}
            </div>
            <div style="font-size: 0.78rem; font-weight: 600; margin-top: 0.6rem; color: rgba(255,255,255,0.8); letter-spacing: 0.3px;">
                Detail Transaksi
            </div>
        </div>

        {{-- Detail Table --}}
        <div style="padding: 0 1.5rem 1.5rem; position: relative; z-index: 1;">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Total Transfer</th>
                        <th>Total Tarik Tunai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 700; font-size: 0.95rem; color: #fff;">{{ number_format($data['total_transfer'], 0, ',', ',') }}</td>
                        <td style="font-weight: 700; font-size: 0.95rem; color: #fff;">{{ number_format($data['total_tunai'], 0, ',', ',') }}</td>
                    </tr>
                    <tr>
                        <td style="color: rgba(255,255,255,0.75);">{{ number_format($data['admin_transfer'], 0, ',', ',') }} ({{ $data['jumlah_transfer'] }})</td>
                        <td style="color: rgba(255,255,255,0.75);">{{ number_format($data['admin_tunai'], 0, ',', ',') }} ({{ $data['jumlah_tunai'] }})</td>
                    </tr>
                    <tr>
                        <td style="color: rgba(255,255,255,0.75);">kunjungan ({{ $data['kunjungan'] }})</td>
                        <td style="font-weight: 600; color: #fff;">keuntung ({{ number_format($data['keuntungan'], 0, ',', ',') }})</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

{{-- Keuntungan Yayang - Saham 50% --}}
@php
    $yayangData = collect($karyawanData)->firstWhere(fn($d) => $d['user']->username === 'yayang');
@endphp
@if($yayangData && $yayangData['keuntungan'] > 0)
<div style="margin-top: 2rem;">
    <div class="saham-box">
        <div style="padding: 1.8rem 2rem; position: relative; z-index: 1;">
            <div style="font-size: 2.4rem; font-weight: 800; color: #fff; letter-spacing: -1px;">{{ number_format($yayangData['keuntungan'], 0, ',', ',') }}</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin: 0.5rem 0;">/ 50%</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #a5b4fc;">{{ number_format($yayangData['keuntungan'] / 2, 0, ',', ',') }}</div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    /* Chart Toggle */
    .chart-toggle {
        display: flex;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 3px;
        gap: 2px;
    }
    .chart-btn {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .chart-btn.active {
        background: rgba(99, 102, 241, 0.3);
        color: #a5b4fc;
        border: 1px solid rgba(99, 102, 241, 0.4);
    }
    .chart-btn:hover:not(.active) {
        color: rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.05);
    }

    /* User Dropdown */
    .chart-user-dropdown {
        position: relative;
    }
    .user-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 0.4rem;
        background: rgba(30, 41, 59, 0.98);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 0.5rem;
        min-width: 180px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 100;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    .user-check-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.8);
        transition: background 0.15s;
    }
    .user-check-item:hover {
        background: rgba(255, 255, 255, 0.06);
    }
    .user-check-item input[type="checkbox"] {
        accent-color: #6366f1;
        width: 15px;
        height: 15px;
        cursor: pointer;
    }

    .calc-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.2rem;
        align-items: start;
    }
    @media (max-width: 1200px) {
        .calc-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .calc-grid { grid-template-columns: 1fr; }
    }

    /* Liquid Glass Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    }
    .glass-card:hover {
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow:
            0 16px 48px rgba(0, 0, 0, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    /* Yayang - Blue Liquid Glass */
    .glass-card-yayang {
        background: linear-gradient(135deg, rgba(59, 59, 239, 0.3), rgba(99, 102, 241, 0.15));
        backdrop-filter: blur(40px) saturate(200%);
        -webkit-backdrop-filter: blur(40px) saturate(200%);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(59, 59, 239, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card-yayang::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(165, 180, 252, 0.4), transparent);
    }
    .glass-card-yayang::after {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 100%; height: 100%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1), transparent);
        border-radius: 50%;
    }
    .glass-card-yayang:hover {
        transform: translateY(-3px);
        border-color: rgba(139, 92, 246, 0.4);
        box-shadow:
            0 16px 48px rgba(59, 59, 239, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    /* Current user - Teal Glass */
    .glass-card-current {
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.25), rgba(20, 184, 166, 0.1));
        backdrop-filter: blur(40px) saturate(200%);
        -webkit-backdrop-filter: blur(40px) saturate(200%);
        border: 1px solid rgba(20, 184, 166, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(13, 148, 136, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card-current::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 234, 212, 0.3), transparent);
    }
    .glass-card-current:hover {
        transform: translateY(-3px);
        border-color: rgba(20, 184, 166, 0.4);
        box-shadow:
            0 16px 48px rgba(13, 148, 136, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

    /* Glass Table inside cards */
    .glass-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
    }
    .glass-table th {
        padding: 0.6rem 0.8rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255, 255, 255, 0.5);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: rgba(0, 0, 0, 0.15);
    }
    .glass-table td {
        padding: 0.6rem 0.8rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.85rem;
    }
    .glass-table tr:last-child td { border-bottom: none; }

    /* Saham Box */
    .saham-box {
        background: linear-gradient(135deg, rgba(59, 59, 239, 0.25), rgba(139, 92, 246, 0.15));
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(59, 59, 239, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
        max-width: 380px;
        position: relative;
        overflow: hidden;
    }
    .saham-box::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(165, 180, 252, 0.4), transparent);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('profitChart').getContext('2d');
    let profitChart = null;
    let currentPeriod = 'daily';

    function createGradient(ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
        return gradient;
    }

    function getSelectedUsers() {
        const checked = document.querySelectorAll('.user-check:checked');
        return Array.from(checked).map(cb => cb.value);
    }

    function updateDropdownLabel() {
        const total = document.querySelectorAll('.user-check').length;
        const selected = document.querySelectorAll('.user-check:checked').length;
        const label = document.getElementById('userDropdownLabel');

        if (selected === 0) {
            label.textContent = 'Pilih User';
        } else if (selected === total) {
            label.textContent = 'Semua User';
        } else {
            label.textContent = selected + ' User';
        }
    }

    function loadChart(period) {
        currentPeriod = period;
        const users = getSelectedUsers();
        const params = new URLSearchParams();
        params.set('period', period);
        users.forEach(id => params.append('users[]', id));

        // Pass date filter if present in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('mulai')) params.set('mulai', urlParams.get('mulai'));
        if (urlParams.has('sampai')) params.set('sampai', urlParams.get('sampai'));

        fetch(`{{ url('/kalkulator/chart-data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(result => {
                if (profitChart) {
                    profitChart.destroy();
                }

                profitChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: result.labels,
                        datasets: [{
                            label: 'Keuntungan',
                            data: result.data,
                            borderColor: 'rgba(99, 102, 241, 1)',
                            backgroundColor: createGradient(ctx),
                            borderWidth: 2.5,
                            pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleColor: '#f1f5f9',
                                bodyColor: '#a5b4fc',
                                borderColor: 'rgba(99, 102, 241, 0.3)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                titleFont: { weight: '600' },
                                bodyFont: { size: 14, weight: '700' },
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.5)',
                                    font: { size: 11 },
                                    maxRotation: 45,
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.5)',
                                    font: { size: 11 },
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'jt';
                                        if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
                                        return value;
                                    }
                                },
                                beginAtZero: true,
                            }
                        }
                    }
                });
            });
    }

    // Period toggle buttons
    document.querySelectorAll('.chart-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-btn[data-period]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadChart(this.dataset.period);
        });
    });

    // User dropdown toggle
    const dropdownBtn = document.getElementById('userDropdownBtn');
    const dropdownMenu = document.getElementById('userDropdownMenu');

    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', function(e) {
        if (!dropdownMenu.contains(e.target) && e.target !== dropdownBtn) {
            dropdownMenu.style.display = 'none';
        }
    });

    // Check All
    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.user-check').forEach(cb => {
            cb.checked = this.checked;
        });
        updateDropdownLabel();
        loadChart(currentPeriod);
    });

    // Individual checkbox
    document.querySelectorAll('.user-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const total = document.querySelectorAll('.user-check').length;
            const checked = document.querySelectorAll('.user-check:checked').length;
            document.getElementById('checkAll').checked = (checked === total);
            updateDropdownLabel();
            loadChart(currentPeriod);
        });
    });

    // Load default (daily)
    loadChart('daily');
</script>
@endpush