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

    {{-- ── Form Card ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <form method="POST" action="{{ route('admin.obat.store') }}" enctype="multipart/form-data" class="p-6">
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
                                type="number"
                                name="harga"
                                id="harga"
                                required
                                min="0"
                                value="{{ old('harga') }}"
                                placeholder="Contoh: 45000"
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
            <div class="border-t border-[#E0E0E0] mt-8 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('admin.obat.index') }}"
                   class="px-5 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161] text-sm font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-700 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl card-shadow transition-colors">
                    Simpan Obat
                </button>
            </div>

        </form>
    </div>

    {{-- Alpine image choose helper script --}}
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
    </script>
@endsection
