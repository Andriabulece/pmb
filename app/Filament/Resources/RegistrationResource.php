<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Assessment;
use App\Models\PaymentConfirmation;
use App\Models\Registration;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Pendaftaran Mahasiswa';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (!$user) {
            return $query;
        }

        // Scope Tenancy: Staf dengan role terikat prodi hanya bisa melihat data prodi yang ada di user_access
        $accessibleProdiIds = $user->accessibleStudyProgramIds();
        return $query->whereIn('study_program_id', $accessibleProdiIds);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Informasi Pendaftar')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')->disabled()->label('Nomor Registrasi'),
                        Forms\Components\Select::make('study_program_id')
                            ->relationship('studyProgram', 'name')
                            ->disabled()
                            ->label('Program Studi'),
                        Forms\Components\TextInput::make('full_name')->required()->label('Nama Lengkap'),
                        Forms\Components\TextInput::make('phone_number')->required()->label('WhatsApp'),
                        Forms\Components\TextInput::make('previous_school')->label('Asal Sekolah'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted (Menunggu Seleksi)',
                                'assessing' => 'Sedang Dinilai / Diuji',
                                'passed' => 'Lulus Seleksi',
                                'rejected' => 'Tidak Lulus',
                                'payment_submitted' => 'Bukti Pembayaran Masuk',
                                'accepted' => 'Resmi Diterima',
                            ])
                            ->required()
                            ->label('Status Pendaftaran'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull()->label('Catatan Tim Seleksi / Verifikator'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->searchable()->sortable()->badge()->label('No Reg'),
                Tables\Columns\TextColumn::make('full_name')->searchable()->sortable()->label('Nama Calon Mhs'),
                Tables\Columns\TextColumn::make('studyProgram.name')->sortable()->badge()->color('gray')->label('Prodi'),
                Tables\Columns\TextColumn::make('phone_number')->label('WhatsApp'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'assessing' => 'warning',
                        'passed' => 'success',
                        'rejected' => 'danger',
                        'payment_submitted' => 'purple',
                        'accepted' => 'success',
                        default => 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Mendaftar'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('study_program_id')
                    ->relationship('studyProgram', 'name')
                    ->label('Filter Prodi'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'assessing' => 'Assessing',
                        'passed' => 'Passed',
                        'rejected' => 'Rejected',
                        'payment_submitted' => 'Payment Submitted',
                        'accepted' => 'Accepted',
                    ])
                    ->label('Filter Status'),
            ])
            ->actions([
                // 1. Aksi Verifikasi Berkas (Admin Prodi & Ka Prodi)
                Tables\Actions\Action::make('verify_documents')
                    ->label('Verifikasi Berkas')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->form([
                        Forms\Components\Placeholder::make('info_berkas')
                            ->content(function (Registration $record) {
                                $ktp = $record->getFirstMediaUrl('ktp');
                                $ijazah = $record->getFirstMediaUrl('ijazah');
                                $transkrip = $record->getFirstMediaUrl('transkrip_nilai');
                                $foto = $record->getFirstMediaUrl('pas_foto');

                                $html = "<ul class='space-y-1 text-sm'>";
                                $html .= "<li><strong>KTP:</strong> " . ($ktp ? "<a href='{$ktp}' target='_blank' class='text-primary-600 underline'>Lihat Berkas</a>" : "Belum diunggah") . "</li>";
                                $html .= "<li><strong>Ijazah:</strong> " . ($ijazah ? "<a href='{$ijazah}' target='_blank' class='text-primary-600 underline'>Lihat Berkas</a>" : "Belum diunggah") . "</li>";
                                $html .= "<li><strong>Transkrip:</strong> " . ($transkrip ? "<a href='{$transkrip}' target='_blank' class='text-primary-600 underline'>Lihat Berkas</a>" : "Belum diunggah") . "</li>";
                                $html .= "<li><strong>Pas Foto:</strong> " . ($foto ? "<a href='{$foto}' target='_blank' class='text-primary-600 underline'>Lihat Foto</a>" : "Belum diunggah") . "</li>";
                                $html .= "</ul>";
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                        Forms\Components\Textarea::make('notes')->label('Catatan untuk Calon Mahasiswa'),
                        Forms\Components\Select::make('decision')
                            ->options([
                                'assessing' => 'Berkas Lengkap & Valid (Lanjut ke Tahap Asesmen)',
                                'draft' => 'Berkas Kurang Lengkap (Minta Mahasiswa Upload Ulang)',
                            ])
                            ->required()
                            ->label('Keputusan Verifikasi'),
                    ])
                    ->action(function (Registration $record, array $data) {
                        $record->update([
                            'status' => $data['decision'],
                            'notes' => $data['notes'] ?? $record->notes,
                            'verified_at' => now(),
                        ]);
                        Notification::make()->title('Status berkas berhasil diperbarui')->success()->send();
                    }),

                // 2. Aksi Asesmen & Penilaian (Dosen & Ka Prodi)
                Tables\Actions\Action::make('give_assessment')
                    ->label('Input Asesmen')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('score')->numeric()->minValue(0)->maxValue(100)->required()->label('Skor Ujian / Wawancara (0-100)'),
                        Forms\Components\Select::make('recommendation')
                            ->options([
                                'passed' => 'Direkomendasikan LULUS',
                                'rejected' => 'TIDAK LULUS',
                                'backup' => 'Cadangan',
                            ])
                            ->required()
                            ->label('Rekomendasi Seleksi'),
                        Forms\Components\Textarea::make('evaluation_notes')->required()->label('Catatan Evaluasi Asesor'),
                    ])
                    ->action(function (Registration $record, array $data) {
                        Assessment::create([
                            'registration_id' => $record->id,
                            'assessor_id' => Auth::id(),
                            'score' => $data['score'],
                            'recommendation' => $data['recommendation'],
                            'evaluation_notes' => $data['evaluation_notes'],
                        ]);

                        if ($data['recommendation'] === 'passed') {
                            $record->update(['status' => 'passed', 'passed_at' => now()]);
                        } elseif ($data['recommendation'] === 'rejected') {
                            $record->update(['status' => 'rejected']);
                        }

                        Notification::make()->title('Hasil asesmen berhasil disimpan')->success()->send();
                    }),

                // 3. Aksi Approval Pembayaran (Keuangan, Kabag TU, Ka Prodi, Admin Fakultas)
                Tables\Actions\Action::make('verify_payment')
                    ->label('Approval Bayar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn (Registration $record) => in_array($record->status, ['payment_submitted', 'passed']))
                    ->form([
                        Forms\Components\Placeholder::make('payment_info')
                            ->content(function (Registration $record) {
                                $payment = $record->paymentConfirmation;
                                if (!$payment) {
                                    return 'Belum ada data konfirmasi pembayaran yang diunggah.';
                                }
                                $buktiUrl = $payment->getFirstMediaUrl('bukti_transfer');
                                $html = "<div class='text-sm space-y-1'>";
                                $html .= "<p><strong>Nominal:</strong> Rp " . number_format($payment->amount, 0, ',', '.') . "</p>";
                                $html .= "<p><strong>Bank:</strong> {$payment->bank_name} (a.n {$payment->account_holder})</p>";
                                $html .= "<p><strong>Tanggal Bayar:</strong> {$payment->payment_date->format('d/m/Y')}</p>";
                                $html .= "<p><strong>Bukti Transfer:</strong> " . ($buktiUrl ? "<a href='{$buktiUrl}' target='_blank' class='text-primary-600 underline font-bold'>Lihat Bukti Transfer</a>" : "Tidak ada berkas") . "</p>";
                                $html .= "</div>";
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                        Forms\Components\Select::make('approval_decision')
                            ->options([
                                'accepted' => 'Setujui Pembayaran & RESMI TERIMA MAHASISWA',
                                'rejected' => 'Tolak Bukti Pembayaran (Minta Upload Ulang)',
                            ])
                            ->required()
                            ->label('Keputusan Approval'),
                        Forms\Components\Textarea::make('verification_notes')->label('Catatan Keuangan / Kabag TU'),
                    ])
                    ->action(function (Registration $record, array $data) {
                        $payment = $record->paymentConfirmation;
                        if ($payment) {
                            $payment->update([
                                'verification_status' => $data['approval_decision'] === 'accepted' ? 'approved' : 'rejected',
                                'verified_by' => Auth::id(),
                                'verification_notes' => $data['verification_notes'] ?? null,
                                'verified_at' => now(),
                            ]);
                        }

                        if ($data['approval_decision'] === 'accepted') {
                            $record->update([
                                'status' => 'accepted',
                                'accepted_at' => now(),
                            ]);
                            Notification::make()->title('Mahasiswa RESMI DITERIMA! Surat tanda penerimaan kini dapat dicetak.')->success()->send();
                        } else {
                            $record->update(['status' => 'passed', 'notes' => 'Pembayaran ditolak: ' . ($data['verification_notes'] ?? '')]);
                            Notification::make()->title('Pembayaran ditolak. Pendaftar diminta upload ulang.')->warning()->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRegistrations::route('/'),
        ];
    }
}
