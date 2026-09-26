@props(['status' => null, 'attendance' => null])
@if($attendance === 'Izin')
    <span class="material-symbols-outlined text-[20px] text-indigo-500" title="Izin">event_busy</span>
@elseif($attendance === 'Sakit')
    <span class="material-symbols-outlined text-[20px] text-rose-500" title="Sakit">sick</span>
@elseif($attendance === 'Tanpa Keterangan')
    <span class="material-symbols-outlined text-[20px] text-slate-500" title="Tanpa Keterangan">help</span>
@elseif(blank($status))
    <span class="material-symbols-outlined text-[20px] text-slate-300" title="Belum Diisi">remove</span>
@elseif($status === 'approved')
    <span class="material-symbols-outlined text-[20px] text-emerald-600" title="Hadir disetujui">check_circle</span>
@elseif($status === 'revision')
    <span class="material-symbols-outlined text-[20px] text-amber-600" title="Perlu Tindakan Anda">warning</span>
@elseif(in_array($status, ['submitted', 'reviewed', 'draft'], true))
    <span class="material-symbols-outlined text-[20px] text-sky-600" title="Menunggu Tindakan Mentor">schedule</span>
@else
    <span class="material-symbols-outlined text-[20px] text-red-600" title="Ditolak">cancel</span>
@endif
