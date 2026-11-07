<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Portal Penerimaan Mahasiswa Baru (PMB)') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(!$registration)
                <!-- FORMULIR PENDAFTARAN AWAL -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Formulir Pendaftaran Calon Mahasiswa</h3>
                    <p class="text-sm text-gray-600 mb-6">Silakan pilih Program Studi dan lengkapi data diri Anda untuk memulai proses pendaftaran.</p>

                    <form action="{{ route('pmb.register.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="study_program_id" :value="__('Program Studi Pilihan')" />
                            <select id="study_program_id" name="study_program_id" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($studyPrograms as $sp)
                                    <option value="{{ $sp->id }}">[{{ $sp->degree }}] {{ $sp->name }} ({{ $sp->code }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('study_program_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="full_name" :value="__('Nama Lengkap (sesuai Ijazah)')" />
                                <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', auth()->user()->name)" required />
                                <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="phone_number" :value="__('Nomor WhatsApp Aktif')" />
                                <x-text-input id="phone_number" name="phone_number" type="text" placeholder="08xxxxxxxxxx" class="mt-1 block w-full" :value="old('phone_number')" required />
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="gender" :value="__('Jenis Kelamin')" />
                                <select id="gender" name="gender" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                                <x-text-input id="birth_place" name="birth_place" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                                <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="previous_school" :value="__('Asal Sekolah / Universitas Sebelumnya')" />
                            <x-text-input id="previous_school" name="previous_school" type="text" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('Alamat Lengkap Tempat Tinggal')" />
                            <textarea id="address" name="address" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required></textarea>
                        </div>

                        <div class="flex justify-end pt-4">
                            <x-primary-button>
                                {{ __('Kirim Pendaftaran Awal') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

            @else
                <!-- STATUS & INFORMASI PENDAFTARAN -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-col md:flex-row justify-between md:items-center border-b pb-4 mb-4 gap-2">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gray-500">Nomor Registrasi</span>
                            <div class="text-xl font-black text-indigo-700">{{ $registration->registration_number }}</div>
                            <div class="text-sm text-gray-600 font-medium">Program Studi: [{{ $registration->studyProgram->degree }}] {{ $registration->studyProgram->name }}</div>
                        </div>
                        <div class="text-left md:text-right">
                            <span class="text-xs uppercase tracking-wider text-gray-500 block">Status Pendaftaran</span>
                            @php
                                $statusBadge = match($registration->status) {
                                    'draft' => ['bg' => 'bg-gray-100 text-gray-800', 'label' => 'DRAFT (Upload Berkas)'],
                                    'submitted' => ['bg' => 'bg-blue-100 text-blue-800', 'label' => 'TERKIRIM (Menunggu Asesmen)'],
                                    'under_review', 'assessing' => ['bg' => 'bg-amber-100 text-amber-800', 'label' => 'PROSES ASESMEN & VERIFIKASI'],
                                    'passed' => ['bg' => 'bg-emerald-100 text-emerald-800', 'label' => 'SELAMAT, ANDA LULUS!'],
                                    'rejected' => ['bg' => 'bg-red-100 text-red-800', 'label' => 'TIDAK LULUS SELEKSI'],
                                    'payment_submitted' => ['bg' => 'bg-purple-100 text-purple-800', 'label' => 'VERIFIKASI PEMBAYARAN'],
                                    'accepted' => ['bg' => 'bg-green-600 text-white font-bold', 'label' => 'RESMI DITERIMA'],
                                    default => ['bg' => 'bg-gray-100 text-gray-800', 'label' => $registration->status],
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge['bg'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </div>
                    </div>

                    <!-- KARTU GRUP WHATSAPP RESMI PRODI -->
                    @if($registration->studyProgram->wa_group_url)
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl p-5 shadow-md mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                            <div>
                                <h4 class="font-bold text-lg flex items-center gap-2">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    Tautan Resmi Grup WhatsApp Calon Mahasiswa
                                </h4>
                                <p class="text-sm text-emerald-100 mt-1">Bergabunglah ke grup WhatsApp resmi Program Studi {{ $registration->studyProgram->name }} untuk update informasi seleksi, jadwal asesmen, dan koordinasi dengan admin & dosen.</p>
                            </div>
                            <a href="{{ $registration->studyProgram->wa_group_url }}" target="_blank" rel="noopener noreferrer" class="whitespace-nowrap bg-white text-emerald-700 hover:bg-emerald-50 px-5 py-2.5 rounded-lg font-bold text-sm shadow transition">
                                Gabung Grup WhatsApp &rarr;
                            </a>
                        </div>
                    @endif

                    @if($registration->notes)
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 text-sm text-amber-800">
                            <strong>Catatan dari Tim Verifikator:</strong> {{ $registration->notes }}
                        </div>
                    @endif

                    <!-- LANGKAH 1: UPLOAD BERKAS -->
                    @if(in_array($registration->status, ['draft', 'submitted']))
                        <div class="border rounded-xl p-5 bg-gray-50 mt-6">
                            <h4 class="font-bold text-gray-900 mb-2 text-base">Unggah Berkas Persyaratan Pendaftaran</h4>
                            <p class="text-xs text-gray-600 mb-4">Format berkas: PDF/JPG/PNG, Maksimal 2MB per file.</p>

                            <form action="{{ route('pmb.documents.upload', $registration) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white p-3 rounded border">
                                        <label class="block text-xs font-bold text-gray-700">Scan KTP / Kartu Identitas</label>
                                        <input type="file" name="ktp" class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700" />
                                        @if($registration->hasMedia('ktp'))
                                            <span class="text-xs text-emerald-600 font-semibold mt-1 inline-block">✓ Sudah diunggah ({{ $registration->getFirstMedia('ktp')->file_name }})</span>
                                        @endif
                                    </div>

                                    <div class="bg-white p-3 rounded border">
                                        <label class="block text-xs font-bold text-gray-700">Scan Ijazah Terakhir / SKL</label>
                                        <input type="file" name="ijazah" class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700" />
                                        @if($registration->hasMedia('ijazah'))
                                            <span class="text-xs text-emerald-600 font-semibold mt-1 inline-block">✓ Sudah diunggah ({{ $registration->getFirstMedia('ijazah')->file_name }})</span>
                                        @endif
                                    </div>

                                    <div class="bg-white p-3 rounded border">
                                        <label class="block text-xs font-bold text-gray-700">Transkrip Nilai / Rapor</label>
                                        <input type="file" name="transkrip_nilai" class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700" />
                                        @if($registration->hasMedia('transkrip_nilai'))
                                            <span class="text-xs text-emerald-600 font-semibold mt-1 inline-block">✓ Sudah diunggah ({{ $registration->getFirstMedia('transkrip_nilai')->file_name }})</span>
                                        @endif
                                    </div>

                                    <div class="bg-white p-3 rounded border">
                                        <label class="block text-xs font-bold text-gray-700">Pas Foto Formal Terbaru</label>
                                        <input type="file" name="pas_foto" class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700" />
                                        @if($registration->hasMedia('pas_foto'))
                                            <span class="text-xs text-emerald-600 font-semibold mt-1 inline-block">✓ Sudah diunggah ({{ $registration->getFirstMedia('pas_foto')->file_name }})</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <x-primary-button>
                                        {{ __('Simpan / Perbarui Berkas') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- LANGKAH 2: FORM BUKTI PEMBAYARAN (JIKA LULUS) -->
                    @if(in_array($registration->status, ['passed', 'payment_submitted']))
                        <div class="border rounded-xl p-5 bg-emerald-50/50 border-emerald-200 mt-6">
                            <h4 class="font-bold text-emerald-900 mb-1 text-base">Unggah Bukti Pembayaran Registrasi Ulang</h4>
                            <p class="text-xs text-emerald-700 mb-4">Silakan transfer biaya registrasi ke rekening resmi universitas dan lampirkan bukti pembayaran di bawah ini.</p>

                            <form action="{{ route('pmb.payment.upload', $registration) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="amount" :value="__('Nominal Pembayaran (Rp)')" />
                                        <x-text-input id="amount" name="amount" type="number" placeholder="Contoh: 5000000" class="mt-1 block w-full" :value="old('amount', optional($registration->paymentConfirmation)->amount)" required />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_date" :value="__('Tanggal Transfer')" />
                                        <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" :value="old('payment_date', optional(optional($registration->paymentConfirmation)->payment_date)->format('Y-m-d'))" required />
                                    </div>
                                    <div>
                                        <x-input-label for="bank_name" :value="__('Bank Pengirim / Channel')" />
                                        <x-text-input id="bank_name" name="bank_name" type="text" placeholder="BSI / Mandiri / BCA / dll" class="mt-1 block w-full" :value="old('bank_name', optional($registration->paymentConfirmation)->bank_name)" required />
                                    </div>
                                    <div>
                                        <x-input-label for="account_holder" :value="__('Nama Pemilik Rekening Pengirim')" />
                                        <x-text-input id="account_holder" name="account_holder" type="text" class="mt-1 block w-full" :value="old('account_holder', optional($registration->paymentConfirmation)->account_holder)" required />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="bukti_transfer" :value="__('File Bukti Struk/Slip Transfer')" />
                                    <input type="file" name="bukti_transfer" required class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:bg-emerald-600 file:text-white" />
                                </div>

                                <div class="flex justify-end pt-2">
                                    <x-primary-button class="bg-emerald-600 hover:bg-emerald-700">
                                        {{ __('Kirim Bukti Pembayaran') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- LANGKAH 3: CETAK SURAT PENERIMAAN (JIKA ACCEPTED) -->
                    @if($registration->status === 'accepted')
                        <div class="bg-green-50 border border-green-300 rounded-xl p-6 mt-6 text-center">
                            <h4 class="text-xl font-black text-green-800">SELAMAT! ANDA RESMI DITERIMA</h4>
                            <p class="text-sm text-green-700 mt-1 max-w-2xl mx-auto">Seluruh tahapan administrasi, seleksi berkas, asesmen akademik, dan konfirmasi pembayaran telah diverifikasi dan disetujui oleh Program Studi dan Fakultas.</p>
                            <div class="mt-4">
                                <a href="{{ route('pmb.print.acceptance', $registration) }}" target="_blank" class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white font-bold py-2.5 px-6 rounded-lg shadow transition">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                    Unduh Surat Keterangan Diterima (PDF)
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            @endif

        </div>
    </div>
</x-app-layout>

