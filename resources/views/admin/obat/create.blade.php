@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header Area ── --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.obat.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-[#E0E0E0] text-[#616161] hover:text-[#1B5E20] hover:bg-primary-50 transition-colors"
           title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Tambah Obat Baru</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Lengkapi formulir di bawah untuk mendaftarkan obat baru ke aplikasi mobile.</p>
        </div>
    </div>

    {{-- ── Error Notification ── --}}
    @if($errors->any())
        <div class="mb-5 flex items-start gap-3 bg-danger-50 border border-danger-400/30 text-danger-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold text-xs">Pendaftaran obat gagal</p>
                <p class="text-xs mt-0.5 text-danger-700/80">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    {{-- ── Draft Restored Toast ── --}}
    <div id="draft-toast"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-[#1A1A1A] text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-lg
                opacity-0 translate-y-2 transition-all duration-300 pointer-events-none">
        <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Draft formulir berhasil dipulihkan
    </div>

    {{-- ── Form Card ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <form id="form-tambah-obat" method="POST" action="{{ route('admin.obat.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left/Middle Area: Input Fields --}}
                <div class="lg:col-span-2 space-y-5">
                    {{-- Nama Obat --}}
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-[#424242] mb-1.5">Nama Obat <span class="text-danger-700">*</span></label>
                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            required
                            value="{{ old('nama') }}"
                            placeholder="Contoh: Pestisida Organik Hijau"
                            class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kategori --}}
                        <div>
                            <label for="kategori" class="block text-sm font-semibold text-[#424242] mb-1.5">Kategori <span class="text-danger-700">*</span></label>
                            <select
                                name="kategori"
                                id="kategori"
                                required
                                class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                            >
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Pestisida" {{ old('kategori') == 'Pestisida' ? 'selected' : '' }}>Pestisida</option>
                                <option value="Fungisida" {{ old('kategori') == 'Fungisida' ? 'selected' : '' }}>Fungisida</option>
                                <option value="Insektisida" {{ old('kategori') == 'Insektisida' ? 'selected' : '' }}>Insektisida</option>
                                <option value="Pupuk Cair" {{ old('kategori') == 'Pupuk Cair' ? 'selected' : '' }}>Pupuk Cair</option>
                                <option value="Pupuk Organik" {{ old('kategori') == 'Pupuk Organik' ? 'selected' : '' }}>Pupuk Organik</option>
                                <option value="Nutrisi Tanaman" {{ old('kategori') == 'Nutrisi Tanaman' ? 'selected' : '' }}>Nutrisi Tanaman</option>
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-semibold text-[#424242] mb-1.5">Status <span class="text-danger-700">*</span></label>
                            <select
                                name="status"
                                id="status"
                                required
                                class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                            >
                                <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Tidak Aktif" {{ old('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label for="harga" class="block text-sm font-semibold text-[#424242] mb-1.5">Harga Obat (Rupiah) <span class="text-danger-700">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-sm font-semibold text-[#9E9E9E]">Rp</span>
                            </div>
                            <input
                                type="text"
                                inputmode="numeric"
                                name="harga"
                                id="harga"
                                required
                                value="{{ old('harga') ? number_format((float) old('harga'), 0, ',', '.') : '' }}"
                                placeholder="Contoh: 45.000"
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                            >
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-[#424242] mb-1.5">Deskripsi Obat <span class="text-danger-700">*</span></label>
                        <textarea
                            name="deskripsi"
                            id="deskripsi"
                            required
                            rows="5"
                            placeholder="Tuliskan kegunaan obat, dosis pemakaian, dan cara pemakaian obat secara detail..."
                            class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                        >{{ old('deskripsi') }}</textarea>
                    </div>

                    {{-- ── Penyakit yang Ditangani ── --}}
                    <div>
                        <label class="block text-sm font-semibold text-[#424242] mb-1">Penyakit yang Ditangani</label>
                        <p class="text-xs text-[#9E9E9E] mb-3">Centang penyakit yang cocok ditangani oleh obat ini.</p>

                        @if(! empty($allPenyakit))
                            <div class="space-y-4">
                                @foreach($allPenyakit as $table => $group)
                                    @if(! empty($group['data']))
                                        <div class="border border-[#E0E0E0] rounded-xl overflow-hidden">
                                            {{-- Group Header --}}
                                            <div class="flex items-center justify-between px-4 py-2.5 bg-[#F8F9FA] border-b border-[#E0E0E0]">
                                                <span class="text-sm font-bold text-[#424242]">{{ $group['label'] }}</span>
                                                <button type="button"
                                                        onclick="toggleAll('{{ $table }}')"
                                                        class="text-xs font-semibold text-primary-700 hover:text-primary-600 transition-colors"
                                                        id="toggle-{{ $table }}">
                                                    Pilih Semua
                                                </button>
                                            </div>
                                            {{-- Checkbox List --}}
                                            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($group['data'] as $penyakit)
                                                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#F0F4F0] cursor-pointer transition-colors group">
                                                        <input
                                                            type="checkbox"
                                                            name="penyakit[]"
                                                            value="{{ $table }}:{{ $penyakit['id'] }}"
                                                            class="penyakit-check-{{ $table }} w-4 h-4 rounded border-[#BDBDBD] text-primary-700 focus:ring-primary-600/30 cursor-pointer"
                                                            {{ in_array("{$table}:{$penyakit['id']}", old('penyakit', [])) ? 'checked' : '' }}
                                                        >
                                                        <span class="text-xs text-[#424242] group-hover:text-[#1A1A1A] leading-snug">{{ $penyakit['nama_penyakit'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-[#9E9E9E] italic">Data penyakit belum tersedia.</p>
                        @endif
                    </div>
                </div>


                {{-- Right Area: Image Upload with Preview --}}
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-[#424242]">Gambar Obat</label>

                    <div x-data="imagePreview()" class="space-y-3">
                        {{-- Image Display Area --}}
                        <div class="w-full aspect-[4/3] rounded-2xl bg-[#F8F9FA] border-2 border-dashed border-[#E0E0E0] overflow-hidden flex items-center justify-center relative group">
                            {{-- Placeholder --}}
                            <div x-show="!imageUrl" class="flex flex-col items-center justify-center p-6 text-center">
                                <svg class="w-10 h-10 text-[#BDBDBD] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs text-[#9E9E9E] font-medium">Klik atau tarik file ke sini untuk mengupload gambar</span>
                            </div>

                            {{-- Preview Image --}}
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="w-full h-full object-cover">
                            </template>

                            {{-- Transparent click zone overlay --}}
                            <input
                                type="file"
                                name="gambar"
                                id="gambar"
                                accept="image/*"
                                @change="fileChosen"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            >
                        </div>

                        {{-- Instructions --}}
                        <p class="text-[11px] text-[#9E9E9E] leading-relaxed">
                            Format gambar yang didukung: JPG, JPEG, PNG, WEBP. Maksimal ukuran file 2 MB.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Form Actions --}}
            <div class="border-t border-[#E0E0E0] mt-8 pt-5 flex items-center justify-between gap-3">
                {{-- Left: Clear Draft --}}
                <button type="button"
                        id="btn-clear-draft"
                        class="px-4 py-2.5 text-xs text-[#9E9E9E] hover:text-danger-700 hover:bg-danger-50 border border-transparent hover:border-danger-200 font-semibold rounded-xl transition-colors">
                    Hapus Draft
                </button>

                {{-- Right: Cancel & Submit --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.obat.index') }}"
                       class="px-5 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161] text-sm font-semibold rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-700 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl card-shadow transition-colors">
                        Simpan Obat
                    </button>
                </div>
            </div>

        </form>
    </div>

    {{-- Alpine image choose helper + localStorage auto-save --}}
    <script>
        function imagePreview() {
            return {
                imageUrl: '',
                fileChosen(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.imageUrl = URL.createObjectURL(file);
                    }
                }
            }
        }

        // Toggle all checkboxes in a penyakit group
        function toggleAll(table) {
            const checks = document.querySelectorAll('.penyakit-check-' + table);
            const allChecked = Array.from(checks).every(c => c.checked);
            checks.forEach(c => c.checked = !allChecked);
            const btn = document.getElementById('toggle-' + table);
            if (btn) btn.textContent = allChecked ? 'Pilih Semua' : 'Hapus Semua';
        }

        // ── Rupiah Input Formatter ──
        (function () {
            const hargaInput = document.getElementById('harga');
            if (!hargaInput) return;

            // Format: tambah titik setiap 3 digit dari kanan
            function formatRupiah(val) {
                const digits = val.replace(/\D/g, '');
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            hargaInput.addEventListener('input', function () {
                const cursorPos   = this.selectionStart;
                const beforeLen   = this.value.length;
                this.value        = formatRupiah(this.value);
                const afterLen    = this.value.length;
                // Adjust cursor position after reformatting
                const newPos = cursorPos + (afterLen - beforeLen);
                this.setSelectionRange(Math.max(0, newPos), Math.max(0, newPos));
            });

            // Before submit: strip dots so server gets plain number (e.g. 41500)
            const form = document.getElementById('form-tambah-obat');
            if (form) {
                form.addEventListener('submit', function () {
                    hargaInput.value = hargaInput.value.replace(/\./g, '');
                }, { capture: true }); // run before localStorage clear
            }
        })();

        // ── Auto-save & Restore Form Data (localStorage) ──
        const STORAGE_KEY = 'obat_create_draft';

        function saveDraft() {
            const form   = document.getElementById('form-tambah-obat');
            if (!form) return;

            const draft = {
                nama      : form.querySelector('#nama')?.value      || '',
                kategori  : form.querySelector('#kategori')?.value  || '',
                harga     : form.querySelector('#harga')?.value     || '',
                status    : form.querySelector('#status')?.value    || '',
                deskripsi : form.querySelector('#deskripsi')?.value || '',
                penyakit  : Array.from(form.querySelectorAll('input[name="penyakit[]"]:checked')).map(c => c.value),
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        }

        function restoreDraft() {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;

            try {
                const draft = JSON.parse(raw);
                const form  = document.getElementById('form-tambah-obat');
                if (!form) return;

                if (draft.nama)      { const el = form.querySelector('#nama');      if (el) el.value = draft.nama; }
                if (draft.kategori)  { const el = form.querySelector('#kategori');  if (el) el.value = draft.kategori; }
                if (draft.harga)     { const el = form.querySelector('#harga');     if (el) el.value = draft.harga; }
                if (draft.status)    { const el = form.querySelector('#status');    if (el) el.value = draft.status; }
                if (draft.deskripsi) { const el = form.querySelector('#deskripsi'); if (el) el.value = draft.deskripsi; }

                if (Array.isArray(draft.penyakit) && draft.penyakit.length) {
                    draft.penyakit.forEach(val => {
                        const cb = form.querySelector(`input[name="penyakit[]"][value="${val}"]`);
                        if (cb) cb.checked = true;
                    });
                }

                // Show a small toast that draft was restored
                showDraftToast();
            } catch (e) {
                localStorage.removeItem(STORAGE_KEY);
            }
        }

        function clearDraft() {
            localStorage.removeItem(STORAGE_KEY);
        }

        function showDraftToast() {
            const toast = document.getElementById('draft-toast');
            if (!toast) return;
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2');
            }, 3500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('form-tambah-obat');
            if (!form) return;

            // Restore draft on page load (only if no server-side validation errors showing)
            @if(! $errors->any())
                restoreDraft();
            @endif

            // Save on any input/change
            form.addEventListener('input',  saveDraft);
            form.addEventListener('change', saveDraft);

            // Clear draft when form is successfully submitted
            form.addEventListener('submit', () => {
                // We clear on submit; if server returns error the page reloads with old() values anyway
                clearDraft();
            });

            // Clear draft button
            const clearBtn = document.getElementById('btn-clear-draft');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    clearDraft();
                    form.reset();
                    // Reset Alpine imagePreview
                    const alpineEl = form.querySelector('[x-data]');
                    if (alpineEl && alpineEl._x_dataStack) {
                        alpineEl._x_dataStack[0].imageUrl = '';
                    }
                });
            }
        });
    </script>
@endsection


